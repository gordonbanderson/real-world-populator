# SilverStripe Real World Data Populator

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Source text and image data from real world sites to populate your development SilverStripe site.


## Install

Via Composer, SilverStripe 4+

``` bash
$ composer require suilven/real-world-populator
```

## Usage

### Description
```
sake dev/tasks/gutenberg  book=<gutenberg book url> title='<title>'
```

###Example
```bash
sake dev/tasks/gutenberg  book=http://www.gutenberg.org/cache/epub/103/pg103.txt title='Around the World in 80 Days'
```

###Example, Creating a New Blog
By default, the blog created and appended to is called Gutenberg.  Pass a blog parameter to override this.
```bash
sake dev/tasks/gutenberg  book=http://www.gutenberg.org/cache/epub/36/pg36.txt title='War of the World' --blog='HG Wells'
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email gordon.b.anderson@gmail.com instead of using the issue tracker.

## Credits

- [Gordon Anderson][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/suilven/real-world-populator.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/suilven/real-world-populator/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/suilven/real-world-populator.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/suilven/real-world-populator.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/suilven/real-world-populator.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/suilven/real-world-populator
[link-travis]: https://travis-ci.org/suilven/real-world-populator
[link-scrutinizer]: https://scrutinizer-ci.com/g/suilven/real-world-populator/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/suilven/real-world-populator
[link-downloads]: https://packagist.org/packages/suilven/real-world-populator
[link-author]: https://github.com/gordonbanderson
[link-contributors]: ../../contributors
