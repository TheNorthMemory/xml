# A wrapper of the XML parser and builder

Split from the wechatpay-php project for general usage.

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
