MK LOGGER
=========


## Installation

Install TYPO3 via composer.
Maybe you can use our [TYPO3-Composer-Webroot Project](https://github.com/DMKEBUSINESSGMBH/typo3-composer-webroot)

From project root you need to run
```bash
composer require dmk/mklog
```

Now add the following to your AdditionalConfiguration.php if you want to use the log 
writers during error handling:
```php
    if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['mklog']['enable_devlog'])) {
        $minLogLevel = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['mklog']['min_log_level']
            ?: \DMK\Mklog\Utility\SeverityUtility::DEBUG;
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'][$minLogLevel][\DMK\Mklog\Logger\DevlogLogger::class]
            = [];
    }
    if (!empty($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['mklog']['gelf_enable'])) {
        $minLogLevel = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['mklog']['gelf_min_log_level']
            ?: \DMK\Mklog\Utility\SeverityUtility::ALERT;
        $GLOBALS['TYPO3_CONF_VARS']['LOG']['writerConfiguration'][$minLogLevel][\DMK\Mklog\Logger\GelfLogger::class]
            = [];
    }
```
Otherwise the log writers won't be available during error handling of TYPO3 as the 
registration in the ext_localconf.php happends too late for the error handler.

## Developer Log

TYPO3 provides a functionality for logging information for the purposes of development and debugging.
However this functionality doesn't do anything by itself.
MK LOG provides a log writer to handle the informations from this logging.
The informations are stored in a database table and can be browsed using the build in backend module.

![](Images/DevLogEntryBeModule.png)

### Extension Configuration

 * **enable_devlog**  
   Enables the logger. This is required, otherwise the Developer log will nort work.
 * ***min_log_level***  
   Only messages with minimum these log level should be logged. 
   The allowed levels are described below.
 * **exclude_ext_keys**  
   A comma seperated list of extension keys that should not be logged
 * **max_logs**  
   The absolute limit of log messages stored in the database. 
   If that limit is reached all older log entries will be deleted by scheduler task (see next section). 
   If you don't want any limit, set this parameter to zero.

#### Cleanup log table
To clean up the log entry table you can add the "Cleanup mklog entries" scheduler task.
It will delete all older entries above the **max_log** limit.

It's recommended to execute this task regularly, to limit the growth of the log table. 

## Gelf Logger

This logger is designed to transport emergency messages
to an gelf compatible backend like [Graylog2](http://graylog2.org/).  
A log message with a severity of ALERT or EMERGENCY can usualy not be written into the database.
These messages has to transport directly. Otherwise they will be lost.  
Just that this was created.

### Extension Configuration

 * **gelf_enable**  
   Enables the gelf logger.
 * ***gelf_credentials***  
   The gelf logger comunicates with a udp ransport. fill the host and port here like `127.0.0.1:12201`
 * **gelf_min_log_level**  
   Only messages with minimum these log level should be transportet. 
   We recommed to use here ALERT for performance reasons. 
   All other log messages schould logged with the devlog into the database.


## How to log

We recommend to use the static utility `Tx_Rnbase_Utility_Logger` as warpper.

The recommend way  with the wrapper
```php
    Tx_Rnbase_Utility_Logger::debug('tx_myext', 'only a debug info');
```
The direct way
```php
    $logManager = t3lib_div::makeInstance('TYPO3\\CMS\\Core\\Log\\LogManager');
    $logger = $logManager->getLogger($'tx_myext');
    $logger->debug('only a debug info');
```
The old way with the wrapper over the classic logging hook 
```php
    tx_rnbase_util_Logger::debug('only a debug info', 'tx_myext');
```
The old direct way over the classic logging hook 
```php
    t3lib_div::devLog('only a debug info', 'tx_myext', -1);
```


## Log Levels

 * **EMERGENCY**  
   system is unusable. You'd likely not be able to reach the system. You better have an SLA in place when this happens.
 * **ALERT**  
   action must be taken immediately. Entire website down, database unavailable, ...
 * **CRITICAL**  
   critical conditions like unexpected exception.
 * **ERROR**  
   error conditions like runtime errors.
 * **WARNING**  
   warning conditions. Use of deprecated APIs, undesirable things that are not necessarily wrong.
 * **NOTICE**  
   normal but significant condition. Things you should have a look at, nothing to worry about though.
 * **INFO**  
   informational messages like User logs in or SQL logs.
 * **DEBUG**  
   debug-level messages with detailed status informations.

## Scheduler

### DevLog WatchDog

The devlog scheduler checks the devlog entries for new messages and sends
these messages throu a transport.

Currently there are a mail and some gelf transports.

![](Images/SchedulerWatchDog.png)

The credentials are Transport specific:

 * **Mail Message**  
   Only the email: `john@dohe.org`
 * **Gelf UDP**  
   The host and port of the server: `127.0.0.1:12201`
 * **Gelf HTTP**  
   The complete url of the server: `https://admin:admin@127.0.0.1:2345/gelf`
 * **Message limit per run**  
   Max message count to send throu the transport for each scheduler run. Set to `0` to disable the limit.


Additional features
-------------------

Normally when a page is copied the devlog entries are copied along (depending on the permissions).
This is annoying and can lead to confusion as those entries are recognized as new even though they aren't.
That's why this extensions hooks into the copy process and removes the devlog table from the list of tables which can be copied.
This happens for all admins as well.

When a page is deleted by a non admin TYPO3 checks if the user has permissions to delete all tables on that page.
This is fine except for the devlog table as this can be safely deleted and users shouldn't have permissions for those.
That's why this extensions hooks into the deletion process and deletes all devlog entries on a page before the page is deleted ignoring permissions.
