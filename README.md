# Weather PHP

A PHP component to use Weather' API within your PHP project.

**WeatherClient** allows you to use Weather
* *get* a weather from an external API
* *fetch* a weather stored by the application 

## Usage

### In source files
**WeatherClient** contains 3 public methods :
* `get(string $mapId)` to retrieve a specific map of id `mapId`. Returns a `Map`.
* `search(int $amount)` to retrieve the `amount` last created maps. Returns an array of `Map`.
* `create(Map $map)` to create and save a new map. Only `bounds` and `layers` will be persisted, `mapId` and `createdAt` will be generated on creation by Datamaps. Returns a `Map`.

### In test files

**SucceedingDatamapsClientMockFactory** allows to test **WeatherClient** if everything goes **right**.
* `make()` creates a mock of **WeatherClient** that will **never** respond with failures.
* `getExpectedResponseFromGet(string $mapId)` returns the map the mocked version of the `get($mapId) `method will return.
* `getExpectedResponseFromSearch(int $amount)` returns the map the mocked version of the `search($amount) `method will return.
* `getExpectedResponseFromCreate(Map $map)` returns the map the mocked version of the `create($map) `method will return.

**FailingDatamapsClientMockFactory** allows to test **DatamapsClient** if everything goes **wrong**.
* `make()` creates a mock of **DatamapsClient** that will **always** respond with failures.
* `get($mapId)` will always result on `Error on request to Datamaps. Map with mapId $mapId not found`.
* `search($amount)` will always result on `Error on request to Datamaps. Can't retrieve data from an empty repository`.
* `create($map)` will always result on `Error on request to Datamaps. /bounds: Array should have at least 2 items, 1 found`.



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