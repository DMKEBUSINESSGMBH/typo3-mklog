#
# Table structure for table 'tx_scheduler_task'
#
CREATE TABLE tx_scheduler_task (
	tx_mklog_lastrun datetime DEFAULT '0000-00-00 00:00:00',
);

#
# Table structure for table 'tx_mklog_devlog'
#
CREATE TABLE tx_mklog_devlog_entry (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	run_id varchar(50) DEFAULT '' NOT NULL,
	severity int(11) DEFAULT '0' NOT NULL,
	ext_key varchar(255) DEFAULT '' NOT NULL,
	message text NOT NULL,
	extra_data blob,

	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	transport_ids varchar(60) DEFAULT '' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)

);
