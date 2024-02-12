<?php

namespace WeatherPHP\DTOs;

class Point{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude
    )
    {
        
    }
}