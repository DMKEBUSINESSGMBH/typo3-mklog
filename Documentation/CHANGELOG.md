ChangeLog
=========

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

