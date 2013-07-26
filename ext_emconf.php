<?php
/**
 * Extension Manager/Repository config file for ext "mklog".
 */

$EM_CONF[$_EXTKEY] = array(
	'title' => 'MK Logging',
  	'description' => 'Keep track of developer logs. Provides automatic email notify about important errors. New BE module to view logging table.',
  	'category' => 'be',
  	'author' => 'René Nitzsche',
  	'author_company' => 'das MedienKombinat',
  	'author_email' => 'nitzsche@das-medienkombinat.de',
  	'shy' => '',
  	'dependencies' => '',
  	'conflicts' => '',
  	'priority' => '',
  	'module' => '',
  	'state' => 'alpha',
  	'internal' => '',
  	'uploadfolder' => 0,
	//'createDirs' => 'uploads/tx_mklogs',
  	'modify_tables' => '',
  	'clearCacheOnLoad' => 0,
  	'lockType' => '',
  	'version' => '1.0.8',
  	'constraints' => array(
    	'depends' => array(
      		'rn_base' => '0.10.3-0.0.0',
      		'devlog' => '',
      		'scheduler' => '',
		),
    	'conflicts' => array(
    	),
    	'suggests' => array(
    	),
  	),
);

