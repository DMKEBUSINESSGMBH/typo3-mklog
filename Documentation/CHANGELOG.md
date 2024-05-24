ChangeLog
=========

12.0.3
------
* Remove stack trace arguments from error mail when an exception during logging occured.

12.0.2
------
* Minor bugfixes

12.0.0
------
* TYPO3 12.4 compatibility

11.0.12
------
* bugfix for BE module since rn_base 1.16.7

11.0.11
------
* remove obsolete database field

11.0.10
------
* fix uninitialized string offset

11.0.9
------
* require rn_base at least 1.16.0

11.0.1 - 11.0.8
------
* bugfixes

11.0.0
------
* support for TYPO3 10 and 11

10.1.0
------
* require rn_base at least in version 1.15.0

10.0.3
------
* Add doctrine dbal 2.13 support

10.0.2
------

  * cleaned up and streamlined code
  * drop support for TYPO3 8.7 
  * drop support for PHP 7.1 and 7.2
  * require doctrine/dbal at least in version ~2.11.2
  * bugfixes

10.0.1
------

  * fix none-composer installations
  * bugfixes


10.0.0
------

  * compatibility to TYPO3 10.4
  * cleaned up and streamlined code
  * drop TYPO3 7 support
  * remove some dependencies to rn_base 

9.6.0
------

  * extract cleanup step to new scheduler task

9.5.11
------

  * added documentation how to register log writer for error handling
  
9.5.10
------

  * Add configuration for max extradata size for watchdog transports
  * Remove class constant visibility in test
  * Remove class constant visibility for backward compatibility to php 7.0 and php 5.6
  * Add usage of entry data parser on persist into db
  * Cleanup cs fo exception in factory
  * Disable phpdoc align and superfluousphpdoc rules
  * Add tests for shortened internal data
  * Cleanup short array syntax
  * Add tests generated var/log/ folder and composer.lock file to gitignore
  * Fix factory namespace in decorator
  * Cleanup php doc
  * Add usage of entry data parser for gelf transports
  * Add usage of entry data parser in be module
  * Add method to short internal data if required
  * Add new entry data parser utility class
  * Fix extension key in composer json
  * Add scrutinizer badge
  
9.5.6
------

  * support PHP 5.6 again
  * fix transport factory exception
  
9.5.5
------

  * devlog entry table optimization
  * use current time for log entries
  
9.5.4
------

  * fixed test case class name
  
9.5.3
------

  * added option to configure extension white- and blacklist and custom mail subject in WatchDog scheduler
  
9.5.2
------

  * require rn_base >= v1.10.2 so logging works again
  * use rn_base utility to add flash message in schedulers
  
9.5.1
------

  * fixed compatibility issues for TYPO3 8.7 and 9.5. Please check that your schedulers have the correct configuration still set. If not you need to reconfigure them.
  
9.5.0
------

  * added support to TYPO3 9.5
  
3.1.6
------

  * cleanup send entries ordered by crdate
  * some optimizations and fixes
  
3.1.3
------

  * gelf logger path in syslog config fixed
  
3.1.2
------

  * Bugfix in data converter utility, decode returns always an array now
  
3.1.1
------

  * Request url added to extra data

3.1.0
------

  * new syslog hook for direct gelf logging
  * transport for gelf logger are configurable in extension configuration now
  * extra data size increased
  * timezone issues fpr log messages fixed
  * extra data output in be module fixed

3.0.0
------

  * TYPO3 8.7 LTS Support

2.1.0
------

  * new devlog logger added
  * new be module for browsing devlog entries added
  * new scheduler with transport support
  * new gelf and mail transports added
  * new gelf logger for emergency alerts

2.0.5
------

  * Delete devlog entries automatically when deleting pages and prevent copying of devlog entries along when copying a page

2.0.4
------

  * added extension icon

2.0.3
------

  * converted documentation from reSt to markdown

2.0.2
------

  * added support for TYPO3 7.6
  * added support for devlog 3.x

1.1.7
------

  * fixed sending mail without found entries if no grouping configured

1.1.0
------

  * Initial Release

