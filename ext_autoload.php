<?php
/*
 * Register necessary class names with autoloader
 *
 */

$extensionPath = PATH_typo3conf . 'ext/mklog/';
return array(
	'tx_mklog_scheduler_watchdog'					=> $extensionPath . 'scheduler/class.tx_mklog_scheduler_WatchDog.php',
	'tx_mklog_scheduler_watchdogaddfieldprovider'	=> $extensionPath . 'scheduler/class.tx_mklog_scheduler_WatchDogAddFieldProvider.php',
);

