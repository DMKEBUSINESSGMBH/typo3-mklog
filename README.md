MK Log
=========

![TYPO3 compatibility](https://img.shields.io/badge/TYPO3-10.4%20%7C%2011.5-orange?maxAge=3600&style=flat-square&logo=typo3)
[![Latest Stable Version](https://img.shields.io/packagist/v/dmk/mklog.svg?maxAge=3600&style=flat-square&logo=composer)](https://packagist.org/packages/dmk/mklog)
[![Total Downloads](https://img.shields.io/packagist/dt/dmk/mklog.svg?maxAge=3600&style=flat-square)](https://packagist.org/packages/dmk/mklog)
[![Build Status](https://img.shields.io/github/workflow/status/DMKEBUSINESSGMBH/typo3-mklog/PHP%20Checks.svg?maxAge=3600&style=flat-square&logo=github-actions)](https://github.com/DMKEBUSINESSGMBH/typo3-mklog/actions?query=workflow%3A%22PHP+Checks%22)
[![License](https://img.shields.io/packagist/l/dmk/mklog.svg?maxAge=3600&style=flat-square&logo=gnu)](https://packagist.org/packages/dmk/mklog)


This extension offers a developer log. 
Ther is a scheduler task too, the watch dog, which aggregates devlog entries and sends them via a transport.
So it is possible to send a mail with fatal errors the minute they occur but warnings only every 6 hours. 
Or send a mail with infos from an import every night.

Or transport all the logs to a graylog server

Of course the devlog has to be used by the core and extensions.
To have exceptions and errors logged to the devlog the error handling of mktools can be used.

### Installation
Install TYPO3 via composer.
Maybe you can use our [TYPO3-Composer-Webroot Project](https://github.com/DMKEBUSINESSGMBH/typo3-composer-webroot)

From project root you need to run
```bash
composer require dmk/mklog
```

### Documentation

For usage please have a look in our **_[Documentation](Documentation/Index.md)_**

[CHANGELOG](Documentation/CHANGELOG.md)
