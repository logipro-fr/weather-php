<?php

namespace WeatherPHP\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionObject;
use Safe\DateTimeImmutable;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use WeatherPHP\DTOs\Point;
use WeatherPHP\DTOs\Source;
use WeatherPHP\DTOs\WeatherAPIReturn;
use WeatherPHP\DTOs\WeatherInfo;
use WeatherPHP\WeatherClient;

use function Safe\json_encode;

class WeatherClientTest extends TestCase
{
    /** @var array<string,mixed> */
    private array $SUCCESSFUL_DATA;
    private WeatherInfo $SUCCESSFUL_RESPONSE_TARGET;
    private string $SUCCESSFUL_RESPONSE;
    private string $SUCCESSFUL_MULTI_RESPONSE;
    private string $SUCCESSFUL_TRUE_MULTI_RESPONSE;

    public function setUp(): void
    {
        $this->SUCCESSFUL_DATA = [
            "id" => "debug001",
            "latitude" => 2.1,
            "longitude" => 40.531,
            "date" => "2024-01-01 12:30:00.000000",
            "historical" => false,
            "source" => [
                "name" => "DEBUG",
                "url" => "https://example.com/"
            ],
            "result" => ["foo" => "bar", "sussus" => "ඞ"]
        ];
        $this->SUCCESSFUL_RESPONSE_TARGET = new WeatherInfo(
            "debug001",
            DateTimeImmutable::createFromFormat("Y-m-d H:i", "2024-01-01 12:30"),
            new WeatherAPIReturn((object)($this->SUCCESSFUL_DATA["result"]), new Source("DEBUG"), false),
            new Point(2.1, 40.531)
        );

        $this->SUCCESSFUL_RESPONSE = '{"success":true,"data":' .
            json_encode($this->SUCCESSFUL_DATA) . ',"errorCode":""}';

        $this->SUCCESSFUL_MULTI_RESPONSE = '{"success":true,"data":[' .
            json_encode($this->SUCCESSFUL_DATA) . '],"errorCode":""}';
        $this->SUCCESSFUL_TRUE_MULTI_RESPONSE = '{"success":true,"data":[' .
            json_encode($this->SUCCESSFUL_DATA) . ',' . json_encode($this->SUCCESSFUL_DATA) .
            '],"errorCode":""}';
    }

    public function testCreate(): void
    {
        $serviceA = new WeatherClient();
        $httpcli = $this->createMock(HttpClientInterface::class);
        $serviceB = new WeatherClient($httpcli, "example.com");

        $this->assertInstanceOf(WeatherClient::class, $serviceA);
        $this->assertInstanceOf(WeatherClient::class, $serviceB);
        $this->assertNotEquals($serviceA, $serviceB);
        $this->assertEquals("example.com/", $serviceB->getDomain());
    }

    public function testCreateBadDomain(){
        $this->expectException(InvalidArgumentException::class);
        $service = new WeatherClient(null, "@");
    }

    public function testGetSavedFromId(): void
    {
        $target = $this->SUCCESSFUL_RESPONSE_TARGET;
        $httpcli = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method("getContent")->willReturn($this->SUCCESSFUL_RESPONSE);
        $httpcli->method("request")->willReturn($mockResponse);

        $service = new WeatherClient($httpcli);
        $result = $service->getSavedFromId($target->weatherInfoId);

        $this->assertEquals($target, $result);
    }

    public function testGetSavedFromDatePoint(): void
    {
        $target = $this->SUCCESSFUL_RESPONSE_TARGET;
        $httpcli = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method("getContent")->willReturn($this->SUCCESSFUL_RESPONSE);
        $httpcli->method("request")->willReturn($mockResponse);

        $service = new WeatherClient($httpcli);
        $result = $service->getSavedFromDateAndPoint(
            $this->SUCCESSFUL_RESPONSE_TARGET->point,
            $this->SUCCESSFUL_RESPONSE_TARGET->date,
            false
        );

        $this->assertEquals($target, $result);
    }

    public function testGetOneFromApi(): void
    {
        $target = $this->SUCCESSFUL_RESPONSE_TARGET;
        $httpcli = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method("getContent")->willReturn($this->SUCCESSFUL_MULTI_RESPONSE);
        $httpcli->method("request")->willReturn($mockResponse);

        $service = new WeatherClient($httpcli);
        $result = $service->getFromApi(
            [$this->SUCCESSFUL_RESPONSE_TARGET->point],
            $this->SUCCESSFUL_RESPONSE_TARGET->date
        );

        $this->assertEquals($target, $result[0]);
    }

    public function testGetTwoFromApi(): void
    {
        $target = $this->SUCCESSFUL_RESPONSE_TARGET;
        $httpcli = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method("getContent")->willReturn($this->SUCCESSFUL_TRUE_MULTI_RESPONSE);
        $httpcli->method("request")->willReturn($mockResponse);

        $service = new WeatherClient($httpcli);
        $result = $service->getFromApi(
            [$this->SUCCESSFUL_RESPONSE_TARGET->point, $this->SUCCESSFUL_RESPONSE_TARGET->point],
            $this->SUCCESSFUL_RESPONSE_TARGET->date
        );

        $this->assertEquals($target, $result[0]);
        $this->assertEquals($target, $result[1]);
    }

    public function testConstructIdUri(): void
    {
        $service = new WeatherClient();
        $reflector = new ReflectionObject($service);
        $id = "testerid";
        $target = $service->getDomain() . WeatherClient::GET_FROM_ID_URI . "?id=" . $id;
        $result = $reflector->getMethod("constructIdRequest")->invoke($service, $id);
        $this->assertEquals($target, $result);
    }

    public function testConstructDatePointUriNoPrecise(): void
    {
        $service = new WeatherClient();
        $reflector = new ReflectionObject($service);
        $point = new Point(5, 6);
        $pointString = $point->latitude . "," . $point->longitude;
        $date = DateTimeImmutable::createFromFormat("Y-m-d H:i:s", "2024-01-01 12:30:00");
        $target = $service->getDomain() . WeatherClient::GET_FROM_DATE_POINT_URI . "?date=" .
            $date->format("Y-m-d H:i:s.u") . "&point=" . $pointString . "&exact=false";
        $result = $reflector->getMethod("constructDatePointRequest")->invoke($service, $point, $date, null, false);
        $this->assertEquals($target, $result);
    }

    public function testConstructDatePointUriYesPrecise(): void
    {
        $service = new WeatherClient();
        $reflector = new ReflectionObject($service);
        $point = new Point(5, 6);
        $pointString = $point->latitude . "," . $point->longitude;
        $date = DateTimeImmutable::createFromFormat("Y-m-d H:i:s", "2024-01-01 12:30:00");
        $target = $service->getDomain() . WeatherClient::GET_FROM_DATE_POINT_URI . "?date=" .
            $date->format("Y-m-d H:i:s.u") . "&point=" . $pointString . "&historicalOnly=true&exact=false";
        $result = $reflector->getMethod("constructDatePointRequest")->invoke($service, $point, $date, false, false);
        $this->assertEquals($target, $result);
    }

    public function testConstructFetchSingle(): void
    {
        $service = new WeatherClient();
        $reflector = new ReflectionObject($service);
        $point = new Point(5, 6);
        $pointString = $point->latitude . "," . $point->longitude;
        $date = DateTimeImmutable::createFromFormat("Y-m-d H:i:s", "2024-01-01 12:30:00");
        $target = $service->getDomain() . WeatherClient::GET_VIA_API_URI . "?date=" .
            $date->format("Y-m-d H:i:s.u") . "&points=" . $pointString;
        $result = $reflector->getMethod("constructFetchRequest")->invoke($service, [$point], $date, false, false);
        $this->assertEquals($target, $result);
    }

    public function testConstructFetchDouble(): void
    {
        $service = new WeatherClient();
        $reflector = new ReflectionObject($service);
        $pointA = new Point(5, 6);
        $pointB = new Point(7, 8);
        $pointString = $pointA->latitude . "," . $pointA->longitude . ";" .
            $pointB->latitude . "," . $pointB->longitude;
        $date = DateTimeImmutable::createFromFormat("Y-m-d H:i:s", "2024-01-01 12:30:00");
        $target = $service->getDomain() . WeatherClient::GET_VIA_API_URI . "?date=" .
            $date->format("Y-m-d H:i:s.u") . "&points=" . $pointString;
        $result = $reflector->getMethod("constructFetchRequest")
            ->invoke($service, [$pointA, $pointB], $date, false, false);
        $this->assertEquals($target, $result);
    }
}
