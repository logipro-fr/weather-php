# Weather PHP

A PHP component to use Weather' API within your PHP project.

**WeatherClient** allows you to use Weather
* *get* a weather from an external API
* *fetch* a weather stored by the application 

## Usage

### In source files
**WeatherClient** contains 3 public methods :
* `getSavedFromId(string $weatherInfoId)` to retrieve a specific weatherInfo of id `weatherInfoId`. Returns a `WeatherInfo`.
* `getSavedFromDateAndPoint(Point $point, DateTimeImmutable $date, bool $exact, ?bool $precise = null)` to retrieve a weather with a date of `date` at a point `point`. `exact` should be true if the match needs to be an exact match, and `precise` shouldbe true if we want accurate data, false if we want prediction data, or null for either. Returns a `WeatherInfo`.
* `getFromApi(array<Point> $pointArray, DateTimeImmutable $date)` to fetch new weathers from tan external API, based of a list of points and a date, and persist it. Returns an array of `WeatherInfo`s.

## Install

```shell
composer require logipro/weather-php
```

## To contribute to Datamaps PHP
### Requirements:
* docker
* git
* a bash shell

### Unit tests
```shell
bin/phpunit
```

### Integration tests
```shell
bin/phpunit-integration
```
**integration tests can only be run if you have a running [weather](https://github.com/logipro-fr/weather) instance**

### Quality
#### Some indicators:
* phpcs PSR12
* phpstan level 9
* coverage >= 100%
* infection MSI >= 100%


#### Quick check with:
```shell
./codecheck
```


#### Check coverage with:
```shell
bin/phpunit --coverage-html var
```
and view 'var/index.html' in your browser


#### Check infection with:
```shell
bin/infection
```
and view 'var/infection.html' in your browser