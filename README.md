# A wrapper of the XML parser and builder

Split from the wechatpay-php project for general usages.

[![GitHub actions](https://github.com/TheNorthMemory/xml/workflows/CI/badge.svg)](https://github.com/TheNorthMemory/xml/actions)
[![Packagist Stars](https://img.shields.io/packagist/stars/thenorthmemory/xml)](https://packagist.org/packages/thenorthmemory/xml)
[![Packagist Downloads](https://img.shields.io/packagist/dm/thenorthmemory/xml)](https://packagist.org/packages/thenorthmemory/xml)
[![Packagist Version](https://img.shields.io/packagist/v/thenorthmemory/xml)](https://packagist.org/packages/thenorthmemory/xml)
[![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/thenorthmemory/xml)](https://packagist.org/packages/thenorthmemory/xml)
[![Packagist License](https://img.shields.io/packagist/l/thenorthmemory/xml)](https://packagist.org/packages/thenorthmemory/xml)

## Install

```shell
composer require thenorthmemory/xml
```

## Usage

```php
use TheNorthMemory\Xml\Transformer;
$array = Transformer::toArray('<xml><hello>world</hello></xml>');
// print_r($array);
// Array
// (
//     [hello] => world
// )
$xml = Transformer::toXml($array);
// print_r($xml);
// <xml><hello>world</hello></xml>
```

## License

[Apache-2.0 License](LICENSE)
