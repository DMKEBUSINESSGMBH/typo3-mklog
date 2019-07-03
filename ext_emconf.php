<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "mklog".
 *
 * Auto generated 11-09-2014 12:29
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/
$EM_CONF[$_EXTKEY] = array(
    'title' => 'MK Logging',
    'description' => 'Keep track of developer logs. Provides automatic email notification about important errors.',
    'category' => 'be',
    'author' => 'René Nitzsche, Michael Wagner, Hannes Bochmann',
    'author_company' => 'DMK E-BUSINESS',
    'author_email' => 'dev@dmk-ebusiness.de',
    'shy' => '',
    'dependencies' => 'rn_base,devlog,scheduler',
    'conflicts' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => 0,
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'version' => '9.5.1',
    'constraints' => array(
        'depends' => array(
            'rn_base' => '1.10.0-',
            'typo3' => '6.2.14-9.5.99',
            'scheduler' => '',
        ),
        'conflicts' => array(),
        'suggests' => array(),
    ),
    'suggests' => array(),
    '_md5_values_when_last_written' => 'a:15:{s:16:"ext_autoload.php";s:4:"ff56";s:17:"ext_localconf.php";s:4:"d951";s:14:"ext_tables.sql";s:4:"7ac6";s:16:"locallang_db.xml";s:4:"fcaa";s:10:"README.txt";s:4:"c13b";s:26:"Documentation/Includes.txt";s:4:"ef74";s:23:"Documentation/Index.rst";s:4:"74aa";s:26:"Documentation/Settings.yml";s:4:"a775";s:33:"Documentation/ChangeLog/Index.rst";s:4:"2259";s:38:"Documentation/Images/SchedulerTask.png";s:4:"d16c";s:36:"Documentation/Introduction/Index.rst";s:4:"3ad1";s:35:"Documentation/UsersManual/Index.rst";s:4:"1353";s:47:"scheduler/class.tx_mklog_scheduler_WatchDog.php";s:4:"e76f";s:63:"scheduler/class.tx_mklog_scheduler_WatchDogAddFieldProvider.php";s:4:"3dad";s:35:"srv/class.tx_mklog_srv_WatchDog.php";s:4:"8769";}',
);
