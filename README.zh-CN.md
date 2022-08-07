# Config

[![License](https://img.shields.io/packagist/l/phppkg/config.svg?style=flat-square)](LICENSE)
[![Php Version](https://img.shields.io/badge/php-%3E=8.0-brightgreen.svg?maxAge=2592000)](https://packagist.org/packages/phppkg/config)
[![Latest Stable Version](http://img.shields.io/packagist/v/phppkg/config.svg)](https://packagist.org/packages/phppkg/config)
[![Actions Status](https://github.com/phppkg/easytpl/workflows/Unit-Tests/badge.svg)](https://github.com/phppkg/easytpl/actions)

PHP的配置数据加载,管理,获取,支持多种数据格式.

- 配置数据加载,管理,获取
- 支持加载多个配置数据，会自动合并
- 支持 INI,JSON,YAML,TOML,NEON,PHP 等格式的文件内容
- 支持导出整个配置数据到文件
- 简单的多语言配置数据管理

> **[EN README](README.md)**

## 安装

**composer**

- Required PHP 8.0+

```bash
composer require phppkg/config
```

## 快速开始

先创建一个配置实例,就可以加载指定的配置数据了.

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

### 其他方式创建

### 更多加载方法

- `loadFromFiles(array $filePaths, string $format = '')`
- `loadFromStrings(string $format, string ...$strings)`
- `loadFromSteam(string $format, resource $stream)`
- `loadIniFile(string $filepath)`
- `loadJsonFile(string $filepath)`
- `loadJson5File(string $filepath)`
- `loadYamlFile(string $filepath)`
- `loadPhpFile(string $filepath)`

### 查看加载的数据

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

## 获取值

可以获取指定类型的返回值,同时支持链式key方式获取值

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

## 设置值

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

## 导出到文件

支持导出整个配置数据到文件.

```php
use PhpPkg\Config\ConfigBox;

/** @var ConfigBox $config */
$config->exportTo('/path/to/file.json');
$config->exportTo('/path/to/my.conf', ConfigBox::FORMAT_YAML);
```

## License

[MIT](LICENSE)
