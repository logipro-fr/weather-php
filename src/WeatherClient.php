<?php

namespace WeatherPHP;

use InvalidArgumentException;
use Safe\DateTimeImmutable;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use WeatherPHP\DTOs\Point;
use WeatherPHP\DTOs\Source;
use WeatherPHP\DTOs\WeatherAPIReturn;
use WeatherPHP\DTOs\WeatherInfo;

use function Safe\json_decode;
use function Safe\preg_match;

class WeatherClient
{
    private const BASE_API_URI = "api/v1/";
    public const GET_FROM_ID_URI = self::BASE_API_URI . "data/by-id";
    public const GET_FROM_DATE_POINT_URI = self::BASE_API_URI . "data/by-date-point";
    public const GET_VIA_API_URI = self::BASE_API_URI . "fetch";
    public const DEFAULT_DOMAIN = "https://weather.logipro.fr/";
    private const URL_VALIDATOR = '/^(https?:\/\/)([\w]+(?!\.\/)\.?)+(\/([\w+&@#\/%?=~_|!:,.;]+(?!\.\/)\.?)+)*\/?$/';

    private HttpClientInterface $http;

    public function __construct(
        HttpClientInterface $http = null, 
        private string $domain = self::DEFAULT_DOMAIN
    )
    {
        if ($http == null) {
            $this->http = HttpClient::create();
        } else {
            $this->http = $http;
        }
        if(!preg_match(self::URL_VALIDATOR, $this->domain)){
            throw new InvalidArgumentException("domain is not a valid URI");
        }
        if(!str_ends_with($this->domain, "/")){
            $this->domain .= "/";
        }
    }

    public function getSavedFromId(string $weatherInfoId): WeatherInfo
    {
        $query = $this->constructIdRequest($weatherInfoId);
        $response = $this->http->request("GET", $query);
        $responseData = $response->getContent();

        /** @var object{"success": bool, "data": \stdClass, "errorCode": string} */
        $dataObj = json_decode($responseData);
        return $this->objectToWeatherInfo($dataObj->data);
    }

    private function constructIdRequest(string $id): string
    {
        return $this->domain . self::GET_FROM_ID_URI . "?id=" . $id;
    }

    public function getSavedFromDateAndPoint(
        Point $point,
        DateTimeImmutable $date,
        bool $exact,
        ?bool $precise = null
    ): WeatherInfo {
        $query = $this->constructDatePointRequest($point, $date, $precise, $exact);
        $response = $this->http->request("GET", $query);
        $responseData = $response->getContent();

        /** @var object{"success": bool, "data": \stdClass, "errorCode": string} */
        $dataObj = json_decode($responseData);
        return $this->objectToWeatherInfo($dataObj->data);
    }

    private function constructDatePointRequest(
        Point $point,
        DateTimeImmutable $date,
        ?bool $precise,
        bool $exact
    ): string {
        return $this->domain . self::GET_FROM_DATE_POINT_URI . "?date=" . $date->format("Y-m-d H:i:s.u") .
        "&point=" . $point->latitude . "," . $point->longitude .
        ($precise !== null ? "&historicalOnly=" . ($precise ? "false" : "true") : "") .
            "&exact=" . ($exact ? "true" : "false");
    }

    /**
     * @param array<Point> $pointArray
     * @return array<WeatherInfo>
     */
    public function getFromApi(array $pointArray, DateTimeImmutable $date): array
    {
        $query = $this->constructFetchRequest($pointArray, $date);
        $response = $this->http->request("GET", $query);
        $responseData = $response->getContent();

        /** @var object{"success": bool, "data": array<\stdClass>, "errorCode": string} */
        $dataObj = json_decode($responseData);
        $data = $dataObj->data;
        $res = [];
        foreach ($data as $info) {
            array_push($res, $this->objectToWeatherInfo($info));
        }
        return $res;
    }

    /**
     * @param array<Point> $pointArray
     */
    private function constructFetchRequest(
        array $pointArray,
        DateTimeImmutable $date
    ): string {
        $points = "";
        foreach ($pointArray as $point) {
            $points .= ";" . $point->latitude . "," . $point->longitude;
        }
        return $this->domain . self::GET_VIA_API_URI . "?date=" . $date->format("Y-m-d H:i:s.u") .
            "&points=" . substr($points, 1);
    }

    private function objectToWeatherInfo(\stdClass $data): WeatherInfo
    {
        return new WeatherInfo(
            $data->id,
            DateTimeImmutable::createFromFormat("Y-m-d H:i:s.u", $data->date),
            new WeatherAPIReturn($data->results, new Source($data->source->name), $data->historical),
            new Point($data->latitude, $data->longitude)
        );
    }

    public function getDomain(): string {
        return $this->domain;
    }
}
