<?php

namespace WeatherPHP\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use WeatherPHP\WeatherClient;

class WeatherClientTest extends TestCase
{
    private WeatherClient $client;
    public function setUp(): void
    {
        $this->client = new WeatherClient(HttpClient::create(), "http://weather-nginx:10280/");
    }

    public function testGet(): void
    {
        $id = "weather_0007feda3f7b1fd528fab9e5e9dfe42b";
        $result = $this->client->getSavedFromId($id);
        $this->assertEquals($id, $result->weatherInfoId);
    }
}