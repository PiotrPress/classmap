# Classmap

This library generates classmap for autoload.

**NOTE:** This code is based on [ClassMapGenerator](https://github.com/symfony/class-loader/blob/3.4/ClassMapGenerator.php) from [symfony/class-loader](https://github.com/symfony/class-loader) component.  

## Installation

```console
composer require piotrpress/classmap
```

## Usage

```php
require __DIR__ . '/vendor/autoload.php';

use PiotrPress\Classmap;

$classmap = new Classmap( __DIR__ . '/vendor' );

// return array
$classmap->get();

// save to file
$classmap->dump( __DIR__ . '/vendor/classmap.php' );
```

## License

[GPL3.0](license.txt)