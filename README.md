MK LOGGER
=========

[![Latest Stable Version](https://img.shields.io/packagist/v/dmk/mklog.svg?maxAge=2592000&style=flat-square)](https://packagist.org/packages/dmk/mklog)
[![Total Downloads](https://img.shields.io/packagist/dt/dmk/mklog.svg?maxAge=2592000&style=flat-square)](https://packagist.org/packages/dmk/mklog)
[![Build Status](https://img.shields.io/travis/DMKEBUSINESSGMBH/typo3-mklog.svg?maxAge=2592000&style=flat-square)](https://travis-ci.org/DMKEBUSINESSGMBH/typo3-mklog)
[![License](https://img.shields.io/packagist/l/dmk/mklog.svg?maxAge=2592000&style=flat-square)](https://packagist.org/packages/dmk/mklog)


This extension offers a developer log. 
Ther is a scheduler task too, the watch dog, which aggregates devlog entries and sends them via a transport.
So it is possible to send a mail with fatal errors the minute they occur but warnings only every 6 hours. 
Or send a mail with infos from an import every night.

Or transport all the logs to a graylog server

Of course the devlog has to be used by the core and extensions.
To have exceptions and errors logged to the devlog the error handling of mktools can be used.

[UsersManual](Documentation/UsersManual/Index.md)

### Installation
Install TYPO3 via composer.
Maybe you can use our [TYPO3-Composer-Webroot Project](https://github.com/DMKEBUSINESSGMBH/typo3-composer-webroot)

From project root you need to run
```bash
composer require dmk/mklog
```

### Documentation

For usage please have a look in our [Documentation](Documentation/Index.md)

[CHANGELOG](Documentation/CHANGELOG.md)