<?php

namespace WeatherPHP\Tests\Integration;

use Exception;
use PHPUnit\Framework\TestCase;
use Safe\DateTimeImmutable;
use Symfony\Component\HttpClient\HttpClient;
use WeatherPHP\DTOs\Point;
use WeatherPHP\WeatherClient;

class WeatherClientTest extends TestCase
{
    private WeatherClient $client;
    public function setUp(): void
    {
        $this->client = new WeatherClient(HttpClient::create(), "http://weather-nginx/");
    }

    public function testGetId(): void
    {
        $id = "weather_0007feda3f7b1fd528fab9e5e9dfe42b";
        $result = $this->client->getSavedFromId($id);
        $this->assertEquals($id, $result->weatherInfoId);
    }

    public function testGetDatePoint(): void
    {
        $point = new Point(48.878, 2.398);
        $date = DateTimeImmutable::createFromFormat("Y-m-d H:i", "2024-01-02 11:08");
        $result = $this->client->getSavedFromDateAndPoint($point, $date, true);
        $this->assertEquals($point, $result->point);
    }


    public function testFetch(): void
    {
    }
}
