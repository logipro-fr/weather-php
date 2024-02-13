<?php

namespace WeatherPHP\DTOs;

class WeatherAPIReturn
{
    public function __construct(
        public readonly \stdClass $data,
        public readonly Source $source,
        public readonly bool $isPrediction
    ) {
    }
}
