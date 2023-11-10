# Config

[![License](https://img.shields.io/packagist/l/phppkg/config.svg?style=flat-square)](LICENSE)
[![Php Version](https://img.shields.io/badge/php-%3E=8.0-brightgreen.svg?maxAge=2592000)](https://packagist.org/packages/phppkg/config)
[![Latest Stable Version](http://img.shields.io/packagist/v/phppkg/config.svg)](https://packagist.org/packages/phppkg/config)
[![Actions Status](https://github.com/phppkg/config/workflows/Unit-Tests/badge.svg)](https://github.com/phppkg/config/actions)

🗂 Config load, management, merge, get, set and more.

- Config data load, management
- Support load multi config data, will auto merge
- Supports INI,JSON,YAML,TOML,NEON,PHP format file
- Support for exporting configuration data to file
- Language data management

> **[中文说明](README.zh-CN.md)**

## Install

**composer**

- Required PHP 8.0+

```bash
composer require phppkg/config
```

## Usage

create and load config data. load multi file, will auto merge data.

```php
use PhpPkg\Config\ConfigBox;

$config = ConfigBox::new();
$config->loadFromFiles([
    __DIR__ . '/test/testdata/config.ini',
    __DIR__ . '/test/testdata/config.neon',
    __DIR__ . '/test/testdata/config.yml',
    __DIR__ . '/test/testdata/config.toml',
]);
```

### Created in other ways

```php
use PhpPkg\Config\ConfigBox;

$config = ConfigBox::newFromFiles([
    // ... config file list
]);

$config->loadIniFile('path/to/my.ini')
```

### More load methods

- `loadFromFiles(array $filePaths, string $format = '')`
- `loadFromStrings(string $format, string ...$strings)`
- `loadFromSteam(string $format, resource $stream)`
- `loadIniFile(string $filepath)`
- `loadJsonFile(string $filepath)`
- `loadJson5File(string $filepath)`
- `loadYamlFile(string $filepath)`
- `loadPhpFile(string $filepath)`

### Dump data

```php
// dump config
vdump($config->getData());
```

**Output**:

```php
CALL ON PhpPkg\ConfigTest\ConfigBoxTest(24):
array(7) {
  ["name"]=> string(6) "inhere"
  ["age"]=> int(89)
  ["atIni"]=> string(6) "value0"
  ["arr0"]=> array(3) {
    [0]=> string(2) "ab"
    [1]=> int(23)
    [2]=> string(2) "de"
  }
  ["map0"]=> array(2) {
    ["key0"]=> string(4) "val0"
    ["key1"]=> string(4) "val1"
  }
  ["atNeon"]=> string(6) "value1"
  ["atYaml"]=> string(6) "value2"
  ["atToml"]=> string(6) "val at toml"
}
```

## Get value

```php
/** @var PhpPkg\Config\ConfigBox $config */
$config->getInt('age'); // int(89)
$config->getString('name'); // string('inhere')
$config->get('arr0');
$config->get('map0');

// get value by key-path.
$config->getInt('arr0.1'); // int(23)
$config->getString('map0.key0'); // string('val0')
```

## Set value

```php
/** @var PhpPkg\Config\ConfigBox $config */
$config->set('name', 'INHERE');
$config->set('map0.key0', 'new value');

// set multi at once
$config->sets([
    'key1' => 'value1',
    'key2' => 'value2',
    // ...
]);
```

## Export to file

Export config data to file.

```php
use PhpPkg\Config\ConfigBox;

/** @var ConfigBox $config */
$config->exportTo('/path/to/file.json');
$config->exportTo('/path/to/my.conf', ConfigBox::FORMAT_YAML);
```

## PHPPkg Projects

- [phppkg/config](https://github.com/phppkg/config) - 🗂 Config load, management, merge, get, set and more.
- [phppkg/easytpl](https:://github.com/phppkg/easytpl) - ⚡️ Simple and fastly template engine for PHP
- [phppkg/http-client](https:://github.com/phppkg/http-client) - An easy-to-use HTTP client library for PHP
- [phppkg/ini](https:://github.com/phppkg/ini) - 💪 An enhanced `INI` format parser written in PHP.
- [phppkg/jenkins-client](https:://github.com/phppkg/jenkins-client) - Designed to interact with Jenkins CI using its API.
- [phppkg/phpgit](https:://github.com/phppkg/phpgit) - A Git wrapper library for PHP

## License

[MIT](LICENSE)
