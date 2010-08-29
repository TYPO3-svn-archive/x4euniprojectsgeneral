#
# Table structure for table 'tx_x4euniprojectsgeneral_list'
#
CREATE TABLE tx_x4euniprojectsgeneral_list (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	projecttitle tinytext NOT NULL,
	projectmanagement blob NOT NULL,
	externalprojectmanagement text NOT NULL,
	personsinvolved blob NOT NULL,
	externalpersonsinvolved text NOT NULL,
	start int(11) DEFAULT '0' NOT NULL,
	end int(11) DEFAULT '0' NOT NULL,
	financing tinytext NOT NULL,
	collaboration text NOT NULL,
	volume tinytext NOT NULL,
	link1 tinytext NOT NULL,
	link2 tinytext NOT NULL,
	link3 tinytext NOT NULL,
	description text NOT NULL,
	comment text NOT NULL,
	methodology text NOT NULL,
	picture blob NOT NULL,
	finished tinyint(3) DEFAULT '0' NOT NULL,
	category blob NOT NULL,
	contact int(11) DEFAULT '0' NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_x4euniprojectsgeneral_category'
#
CREATE TABLE tx_x4euniprojectsgeneral_category (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	starttime int(11) DEFAULT '0' NOT NULL,
	endtime int(11) DEFAULT '0' NOT NULL,
	cattitle tinytext NOT NULL,
	
	PRIMARY KEY (uid),
	KEY parent (pid)
);