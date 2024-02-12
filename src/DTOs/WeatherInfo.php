<?php

namespace WeatherPHP\DTOs;

use Safe\DateTimeImmutable;

class WeatherInfo {

    public function __construct(
        public readonly string $weatherInfoId,
        public readonly DateTimeImmutable $date,
        public readonly WeatherAPIReturn $data,
        public readonly Point $point
    )
    {
        
    }
}