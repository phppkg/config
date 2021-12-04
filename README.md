# Config

[![License](https://img.shields.io/packagist/l/phppkg/config.svg?style=flat-square)](LICENSE)
[![Php Version](https://img.shields.io/badge/php-%3E=8.0-brightgreen.svg?maxAge=2592000)](https://packagist.org/packages/phppkg/config)
[![Latest Stable Version](http://img.shields.io/packagist/v/phppkg/config.svg)](https://packagist.org/packages/phppkg/config)
[![Actions Status](https://github.com/phppkg/easytpl/workflows/Unit-Tests/badge.svg)](https://github.com/phppkg/easytpl/actions)

Config load, management, get, set and more.

- Config data load, management
- Supports INI,JSON,YAML,NEON,PHP format file
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
]);

// dump config
vdump($config->getData());
```

Output:

```php
CALL ON PhpPkg\ConfigTest\ConfigBoxTest(24):
object(PhpPkg\Config\ConfigBox)#367 (4) {
  ["data":protected]=> array(5) {
    ["name"]=> string(6) "inhere"
    ["age"]=> int(89)
    ["atIni"]=> string(6) "value0"
    ["atNeon"]=> string(6) "value1"
    ["atYaml"]=> string(6) "value2"
  }
  ["mergeDepth"]=> int(3)
  ["keyPathSep"]=> string(1) "."
  ["name":protected]=> string(6) "config"
}
```

### Get value

```php
$config->getInt('age'); // int(89)
$config->getString('name'); // string('inhere')
```

## License

MIT
