# Config

[![License](https://img.shields.io/packagist/l/phppkg/config.svg?style=flat-square)](LICENSE)
[![Php Version](https://img.shields.io/badge/php-%3E=8.0-brightgreen.svg?maxAge=2592000)](https://packagist.org/packages/phppkg/config)
[![Latest Stable Version](http://img.shields.io/packagist/v/phppkg/config.svg)](https://packagist.org/packages/phppkg/config)
[![Actions Status](https://github.com/phppkg/easytpl/workflows/Unit-Tests/badge.svg)](https://github.com/phppkg/easytpl/actions)

Config manage, load, get. Supports INI,JSON,YAML,NEON,PHP format file

- Config data manage
- Language manage

## Install

**composer**

```bash
composer require phppkg/config
```

## Usage

**Config**

```php
use PhpPkg\Config\ConfigBox;

$config = ConfigBox::new();

$config->getString('name'); // 'inhere'
```

## License

MIT
