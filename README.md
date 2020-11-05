MK LOGGER
=========

![TYPO3 compatibility](https://img.shields.io/badge/TYPO3-6.2%20%7C%207.6%20%7C%208.7%20%7C%209.5-orange?maxAge=3600&style=flat-square&logo=typo3)
[![Latest Stable Version](https://img.shields.io/packagist/v/dmk/mklog.svg?maxAge=3600&style=flat-square&logo=composer)](https://packagist.org/packages/dmk/mklog)
[![Total Downloads](https://img.shields.io/packagist/dt/dmk/mklog.svg?maxAge=3600&style=flat-square)](https://packagist.org/packages/dmk/mklog)
[![Build Status](https://img.shields.io/travis/DMKEBUSINESSGMBH/typo3-mklog.svg?maxAge=3600&style=flat-square&logo=travis)](https://travis-ci.com/DMKEBUSINESSGMBH/typo3-mklog)
[![Scrutinizer code quality](https://img.shields.io/scrutinizer/quality/g/DMKEBUSINESSGMBH/typo3-mklog/master?maxAge=3600&style=flat-square&logo=scrutinizerci)](https://scrutinizer-ci.com/g/DMKEBUSINESSGMBH/typo3-mklog/?branch=master)
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
