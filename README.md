MK LOGGER
=========

[![Latest Stable Version](https://poser.pugx.org/dmk/mklog/v/stable?format=flat-square)](https://packagist.org/packages/dmk/mklog)
[![Latest Unstable Version](https://poser.pugx.org/dmk/mklog/v/unstable?format=flat-square)](https://packagist.org/packages/dmk/mklog)
[![Total Downloads](https://poser.pugx.org/dmk/mklog/downloads?format=flat-square)](https://packagist.org/packages/dmk/mklog)
[![License](https://poser.pugx.org/dmk/mklog/license?format=flat-square)](https://packagist.org/packages/dmk/mklog)


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