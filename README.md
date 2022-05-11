# Config

[![License](https://img.shields.io/packagist/l/phppkg/config.svg?style=flat-square)](LICENSE)
[![Php Version](https://img.shields.io/badge/php-%3E=8.0-brightgreen.svg?maxAge=2592000)](https://packagist.org/packages/phppkg/config)
[![Latest Stable Version](http://img.shields.io/packagist/v/phppkg/config.svg)](https://packagist.org/packages/phppkg/config)
[![Actions Status](https://github.com/phppkg/easytpl/workflows/Unit-Tests/badge.svg)](https://github.com/phppkg/easytpl/actions)

Config load, management, get, set and more.

- Config data load, management
- Supports INI,JSON,YAML,TOML,NEON,PHP format file
- Language data management

## Install

**composer**

- Required PHP 8.0+

```bash
composer require phppkg/config
```

## Usage

**Config**

```php
use PhpPkg\Config\ConfigBox;

$config = ConfigBox::new();
$config->loadFromFiles([
    __DIR__ . '/test/testdata/config.ini',
    __DIR__ . '/test/testdata/config.neon',
    __DIR__ . '/test/testdata/config.yml',
    __DIR__ . '/test/testdata/config.toml',
]);

// dump config
vdump($config->getData());
```

Output:

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

### Get value

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

### More load methods

- `loadFromFiles(array $filePaths, string $format = '')`
- `loadFromStrings(string $format, string ...$strings)`
- `loadFromSteam(string $format, resource $stream)`

## License

[MIT](LICENSE)
