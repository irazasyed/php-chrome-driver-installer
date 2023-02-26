# PHP Chrome Driver Installer

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-packagist]
[![Software License][ico-license]](LICENSE.md)

This is a simple script to install the latest version of the Chrome Driver for Selenium / WebDriver.

## Installation

```bash
composer require irazasyed/php-chrome-driver-installer
```

## Usage

```php
use Irazasyed\PHP\ChromeDriverInstaller;

$installer = new ChromeDriverInstaller();
$chromeBinary = $installer->getChromeDriverBinary();
```

## Contributing

Contributions are always welcome!

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/irazasyed/php-chrome-driver-installer.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/irazasyed/php-chrome-driver-installer.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/irazasyed/php-chrome-driver-installer
