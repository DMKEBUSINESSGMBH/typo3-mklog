<?php
/**
 * Extension Manager/Repository config file for ext "mklog".
 */

$EM_CONF[$_EXTKEY] = array(
	'title' => 'MK Logging',
  	'description' => 'Keep track of developer logs. Provides automatic email notify about important errors.',
  	'category' => 'be',
  	'author' => 'René Nitzsche, Michael Wagner, Hannes Bochmann',
  	'author_company' => 'DMK E-BUSINESS',
  	'author_email' => 'dev@dmk-ebusiness.de',
  	'shy' => '',
  	'dependencies' => '',
  	'conflicts' => '',
  	'priority' => '',
  	'module' => '',
  	'state' => 'alpha',
  	'internal' => '',
  	'uploadfolder' => 0,
  	'modify_tables' => '',
  	'clearCacheOnLoad' => 0,
  	'lockType' => '',
  	'version' => '1.1.0',
  	'constraints' => array(
    	'depends' => array(
      		'rn_base' => '0.10.3-0.0.0',
      		'devlog' => '',
      		'scheduler' => '',
    		'typo3' => '4.3.0-',
		),
    	'conflicts' => array(
    	),
    	'suggests' => array(
    	),
  	),
);

