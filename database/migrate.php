<?php

namespace SunCoastConnection\ClaimsToOEMR;

use \Illuminate\Database\Capsule\Manager as DatabaseManager;

// SQL Schema was extracted from the database.sql file in the OpenEMR project.

DatabaseManager::schema()->create('addresses', function($table) {
	// CREATE TABLE `addresses` (
	//   `id` int(11) NOT NULL default '0',
	//   `line1` varchar(255) default NULL,
	//   `line2` varchar(255) default NULL,
	//   `city` varchar(255) default NULL,
	//   `state` varchar(35) default NULL,
	//   `zip` varchar(10) default NULL,
	//   `plus_four` varchar(4) default NULL,
	//   `country` varchar(255) default NULL,
	//   `foreign_id` int(11) default NULL,
	//   PRIMARY KEY  (`id`),
	//   KEY `foreign_id` (`foreign_id`)
	// ) ENGINE=MyISAM;

	$table->increments('id');
	$table->string('line1', 255)->nullable();
	$table->string('line2', 255)->nullable();
	$table->string('city', 255)->nullable();
	$table->string('state', 35)->nullable();
	$table->string('zip', 10)->nullable();
	$table->string('plus_four', 4)->nullable();
	$table->string('country', 255)->nullable();
	$table->integer('foreign_id')->length(11)->nullable();
});

DatabaseManager::schema()->create('billing', function($table) {
	// CREATE TABLE `billing` (
	//   `id` int(11) NOT NULL auto_increment,
	//   `date` datetime default NULL,
	//   `code_type` varchar(15) default NULL,
	//   `code` varchar(20) default NULL,
	//   `pid` int(11) default NULL,
	//   `provider_id` int(11) default NULL,
	//   `user` int(11) default NULL,
	//   `groupname` varchar(255) default NULL,
	//   `authorized` tinyint(1) default NULL,
	//   `encounter` int(11) default NULL,
	//   `code_text` longtext,
	//   `billed` tinyint(1) default NULL,
	//   `activity` tinyint(1) default NULL,
	//   `payer_id` int(11) default NULL,
	//   `bill_process` tinyint(2) NOT NULL default '0',
	//   `bill_date` datetime default NULL,
	//   `process_date` datetime default NULL,
	//   `process_file` varchar(255) default NULL,
	//   `modifier` varchar(12) default NULL,
	//   `units` tinyint(3) default NULL,
	//   `fee` decimal(12,2) default NULL,
	//   `justify` varchar(255) default NULL,
	//   `target` varchar(30) default NULL,
	//   `x12_partner_id` int(11) default NULL,
	//   `ndc_info` varchar(255) default NULL,
	//   `notecodes` varchar(25) NOT NULL default '',
	//   PRIMARY KEY  (`id`),
	//   KEY `pid` (`pid`)
	// ) ENGINE=MyISAM AUTO_INCREMENT=1 ;

	$table->increments('id');
	$table->dateTime('date')->nullable();
	$table->string('code_type', 15)->nullable();
	$table->string('code', 20)->nullable();
	$table->integer('pid')->length(11)->nullable();
	$table->integer('provider_id')->length(11)->nullable();
	$table->integer('user')->length(11)->nullable();
	$table->string('groupname', 255)->nullable();
	$table->integer('authorized')->length(1)->nullable();
	$table->integer('encounter')->length(11)->nullable();
	$table->longText('code_text')->nullable();
	$table->integer('billed')->length(1)->nullable();
	$table->integer('activity')->length(1)->nullable();
	$table->integer('payer_id')->length(11)->nullable();
	$table->integer('bill_process')->length(2)->default(0);
	$table->dateTime('bill_date')->nullable();
	$table->dateTime('process_date')->nullable();
	$table->string('process_file', 255)->nullable();
	$table->string('modifier', 12)->nullable();
	$table->integer('units')->length(3)->nullable();
	$table->decimal('fee', 12, 2)->nullable();
	$table->string('justify', 255)->nullable();
});

DatabaseManager::schema()->create('facility', function($table) {
	// CREATE TABLE `facility` (
	//   `id` int(11) NOT NULL auto_increment,
	//   `name` varchar(255) default NULL,
	//   `phone` varchar(30) default NULL,
	//   `fax` varchar(30) default NULL,
	//   `street` varchar(255) default NULL,
	//   `city` varchar(255) default NULL,
	//   `state` varchar(50) default NULL,
	//   `postal_code` varchar(11) default NULL,
	//   `country_code` varchar(10) default NULL,
	//   `federal_ein` varchar(15) default NULL,
	//   `website` varchar(255) default NULL,
	//   `email` varchar(255) default NULL,
	//   `service_location` tinyint(1) NOT NULL default '1',
	//   `billing_location` tinyint(1) NOT NULL default '0',
	//   `accepts_assignment` tinyint(1) NOT NULL default '0',
	//   `pos_code` tinyint(4) default NULL,
	//   `x12_sender_id` varchar(25) default NULL,
	//   `attn` varchar(65) default NULL,
	//   `domain_identifier` varchar(60) default NULL,
	//   `facility_npi` varchar(15) default NULL,
	//   `tax_id_type` VARCHAR(31) NOT NULL DEFAULT '',
	//   `color` VARCHAR(7) NOT NULL DEFAULT '',
	//   `primary_business_entity` INT(10) NOT NULL DEFAULT '0' COMMENT '0-Not Set as business entity 1-Set as business entity',
	//   PRIMARY KEY  (`id`)
	// ) ENGINE=MyISAM AUTO_INCREMENT=4 ;

	$table->increments('id');
	$table->string('name', 255)->nullable();
	$table->string('street', 255)->nullable();
	$table->string('city', 255)->nullable();
	$table->string('state', 50)->nullable();
	$table->string('postal_code', 11)->nullable();
	$table->string('country_code', 10)->nullable();
	$table->string('federal_ein', 15)->nullable();
	$table->integer('service_location')->length(1)->default(1);
	$table->integer('billing_location')->length(1)->default(0);
	$table->integer('accepts_assignment')->length(1)->default(0);
	$table->integer('pos_code')->length(4)->nullable();
	$table->string('attn', 65)->nullable();
	$table->string('domain_identifier', 60)->nullable();
	$table->string('facility_npi', 15)->nullable();
	$table->string('tax_id_type', 31)->default('');
	$table->string('color', 7)->default('');
	$table->integer('primary_business_entity')->length(10)->default(0)->comment = '0-Not Set as business entity 1-Set as business entity';
});

DatabaseManager::schema()->create('form_encounter', function($table) {
	// CREATE TABLE `form_encounter` (
	//   `id` bigint(20) NOT NULL auto_increment,
	//   `date` datetime default NULL,
	//   `reason` longtext,
	//   `facility` longtext,
	//   `facility_id` int(11) NOT NULL default '0',
	//   `pid` bigint(20) default NULL,
	//   `encounter` bigint(20) default NULL,
	//   `onset_date` datetime default NULL,
	//   `sensitivity` varchar(30) default NULL,
	//   `billing_note` text,
	//   `pc_catid` int(11) NOT NULL default '5' COMMENT 'event category from openemr_postcalendar_categories',
	//   `last_level_billed` int  NOT NULL DEFAULT 0 COMMENT '0=none, 1=ins1, 2=ins2, etc',
	//   `last_level_closed` int  NOT NULL DEFAULT 0 COMMENT '0=none, 1=ins1, 2=ins2, etc',
	//   `last_stmt_date`    date DEFAULT NULL,
	//   `stmt_count`        int  NOT NULL DEFAULT 0,
	//   `provider_id` INT(11) DEFAULT '0' COMMENT 'default and main provider for this visit',
	//   `supervisor_id` INT(11) DEFAULT '0' COMMENT 'supervising provider, if any, for this visit',
	//   `invoice_refno` varchar(31) NOT NULL DEFAULT '',
	//   `referral_source` varchar(31) NOT NULL DEFAULT '',
	//   `billing_facility` INT(11) NOT NULL DEFAULT 0,
	//   PRIMARY KEY  (`id`),
	//   KEY `pid_encounter` (`pid`, `encounter`)
	// ) ENGINE=MyISAM AUTO_INCREMENT=1 ;

	$table->bigIncrements('id');
	$table->dateTime('date')->nullable();
	$table->longText('reason');
	$table->longText('facility');
	$table->integer('facility_id')->length(11)->default(0);
	$table->integer('pid')->length(20)->nullable();
	$table->integer('encounter')->length(20)->nullable();
	$table->dateTime('onset_date')->nullable();
	$table->string('sensitivity', 30)->nullable();
	$table->text('billing_note');
	$table->integer('pc_catid')->length(11)->default(5)->comment = 'event category from openemr_postcalendar_categories';
	$table->integer('last_level_billed')->default(0)->comment = '0=none, 1=ins1, 2=ins2, etc';
	$table->integer('last_level_closed')->default(0)->comment = '0=none, 1=ins1, 2=ins2, etc';
	$table->date('last_stmt_date')->nullable();
	$table->integer('stmt_count')->default(0);
	$table->integer('provider_id')->length(11)->default(0)->comment = 'default and main provider for this visit';
	$table->integer('supervisor_id')->length(11)->default(0)->comment = 'supervising provider, if any, for this visit';
	$table->string('invoice_refno', 31)->default('');
	$table->string('referral_source', 31)->default('');
	$table->integer('billing_facility')->length(11)->default(0);
});

DatabaseManager::schema()->create('forms', function($table) {
	// CREATE TABLE `forms` (
	//   `id` bigint(20) NOT NULL auto_increment,
	//   `date` datetime default NULL,
	//   `encounter` bigint(20) default NULL,
	//   `form_name` longtext,
	//   `form_id` bigint(20) default NULL,
	//   `pid` bigint(20) default NULL,
	//   `user` varchar(255) default NULL,
	//   `groupname` varchar(255) default NULL,
	//   `authorized` tinyint(4) default NULL,
	//   `deleted` tinyint(4) DEFAULT '0' NOT NULL COMMENT 'flag indicates form has been deleted',
	//   `formdir` longtext,
	//   PRIMARY KEY  (`id`),
	//   KEY `pid_encounter` (`pid`, `encounter`),
	//   KEY `form_id` (`form_id`)
	// ) ENGINE=MyISAM AUTO_INCREMENT=1 ;

	$table->bigIncrements('id');
	$table->dateTime('date')->nullable();
	$table->integer('encounter')->length(20)->nullable();
	$table->longText('form_name');
	$table->integer('form_id')->length(20)->nullable();
	$table->integer('pid')->length(20)->nullable();
	$table->string('user', 255)->nullable();
	$table->string('groupname', 255)->nullable();
	$table->integer('authorized')->length(4)->nullable();
	$table->integer('deleted')->length(4)->default('0')->comment = 'flag indicates form has been deleted';
	$table->longText('formdir');
});

DatabaseManager::schema()->create('groups', function($table) {
	// CREATE TABLE `groups` (
	//   `id` bigint(20) NOT NULL auto_increment,
	//   `name` longtext,
	//   `user` longtext,
	//   PRIMARY KEY  (`id`)
	// ) ENGINE=MyISAM AUTO_INCREMENT=1 ;

	$table->bigIncrements('id');
	$table->longText('name');
	$table->longText('user');
});

DatabaseManager::schema()->create('insurance_companies', function($table) {
	// CREATE TABLE `insurance_companies` (
	//   `id` int(11) NOT NULL default '0',
	//   `name` varchar(255) default NULL,
	//   `attn` varchar(255) default NULL,
	//   `cms_id` varchar(15) default NULL,
	//   `freeb_type` tinyint(2) default NULL,
	//   `x12_receiver_id` varchar(25) default NULL,
	//   `x12_default_partner_id` int(11) default NULL,
	//   `alt_cms_id` varchar(15) NOT NULL DEFAULT '',
	//   PRIMARY KEY  (`id`)
	// ) ENGINE=MyISAM;

	$table->increments('id');
	$table->string('name', 255)->nullable();
	$table->string('attn', 255)->nullable();
	$table->string('cms_id', 15)->nullable();
	$table->string('x12_receiver_id', 25)->nullable();
	$table->integer('x12_default_partner_id')->length(11)->nullable();
});

DatabaseManager::schema()->create('insurance_data', function($table) {
	// CREATE TABLE `insurance_data` (
	//   `id` bigint(20) NOT NULL auto_increment,
	//   `type` enum('primary','secondary','tertiary') default NULL,
	//   `provider` varchar(255) default NULL,
	//   `plan_name` varchar(255) default NULL,
	//   `policy_number` varchar(255) default NULL,
	//   `group_number` varchar(255) default NULL,
	//   `subscriber_lname` varchar(255) default NULL,
	//   `subscriber_mname` varchar(255) default NULL,
	//   `subscriber_fname` varchar(255) default NULL,
	//   `subscriber_relationship` varchar(255) default NULL,
	//   `subscriber_ss` varchar(255) default NULL,
	//   `subscriber_DOB` date default NULL,
	//   `subscriber_street` varchar(255) default NULL,
	//   `subscriber_postal_code` varchar(255) default NULL,
	//   `subscriber_city` varchar(255) default NULL,
	//   `subscriber_state` varchar(255) default NULL,
	//   `subscriber_country` varchar(255) default NULL,
	//   `subscriber_phone` varchar(255) default NULL,
	//   `subscriber_employer` varchar(255) default NULL,
	//   `subscriber_employer_street` varchar(255) default NULL,
	//   `subscriber_employer_postal_code` varchar(255) default NULL,
	//   `subscriber_employer_state` varchar(255) default NULL,
	//   `subscriber_employer_country` varchar(255) default NULL,
	//   `subscriber_employer_city` varchar(255) default NULL,
	//   `copay` varchar(255) default NULL,
	//   `date` date NOT NULL default '0000-00-00',
	//   `pid` bigint(20) NOT NULL default '0',
	//   `subscriber_sex` varchar(25) default NULL,
	//   `accept_assignment` varchar(5) NOT NULL DEFAULT 'TRUE',
	//   `policy_type` varchar(25) NOT NULL default '',
	//   PRIMARY KEY  (`id`),
	//   UNIQUE KEY `pid_type_date` (`pid`,`type`,`date`)
	// ) ENGINE=MyISAM AUTO_INCREMENT=1 ;

	$table->bigIncrements('id');
	$table->enum('type', ['primary', 'secondary', 'tertiary'])->nullable();
	$table->string('provider', 255)->nullable();
	$table->string('plan_name', 255)->nullable();
	$table->string('policy_number', 255)->nullable();
	$table->string('group_number', 255)->nullable();
	$table->string('subscriber_lname', 255)->nullable();
	$table->string('subscriber_mname', 255)->nullable();
	$table->string('subscriber_fname', 255)->nullable();
	$table->string('subscriber_relationship', 255)->nullable();
	$table->date('subscriber_DOB')->nullable();
	$table->string('subscriber_street', 255)->nullable();
	$table->string('subscriber_postal_code', 255)->nullable();
	$table->string('subscriber_city', 255)->nullable();
	$table->string('subscriber_state', 255)->nullable();
	$table->string('subscriber_country', 255)->nullable();
	$table->string('subscriber_sex', 25)->nullable();
	$table->string('accept_assignment', 5)->default('TRUE');
	$table->integer('pid')->length(20)->default('0');
	$table->date('date')->default('0000-00-00');
});

DatabaseManager::schema()->create('patient_data', function($table) {
	// CREATE TABLE `patient_data` (
	//   `id` bigint(20) NOT NULL auto_increment,
	//   `title` varchar(255) NOT NULL default '',
	//   `language` varchar(255) NOT NULL default '',
	//   `financial` varchar(255) NOT NULL default '',
	//   `fname` varchar(255) NOT NULL default '',
	//   `lname` varchar(255) NOT NULL default '',
	//   `mname` varchar(255) NOT NULL default '',
	//   `DOB` date default NULL,
	//   `street` varchar(255) NOT NULL default '',
	//   `postal_code` varchar(255) NOT NULL default '',
	//   `city` varchar(255) NOT NULL default '',
	//   `state` varchar(255) NOT NULL default '',
	//   `country_code` varchar(255) NOT NULL default '',
	//   `drivers_license` varchar(255) NOT NULL default '',
	//   `ss` varchar(255) NOT NULL default '',
	//   `occupation` longtext,
	//   `phone_home` varchar(255) NOT NULL default '',
	//   `phone_biz` varchar(255) NOT NULL default '',
	//   `phone_contact` varchar(255) NOT NULL default '',
	//   `phone_cell` varchar(255) NOT NULL default '',
	//   `pharmacy_id` int(11) NOT NULL default '0',
	//   `status` varchar(255) NOT NULL default '',
	//   `contact_relationship` varchar(255) NOT NULL default '',
	//   `date` datetime default NULL,
	//   `sex` varchar(255) NOT NULL default '',
	//   `referrer` varchar(255) NOT NULL default '',
	//   `referrerID` varchar(255) NOT NULL default '',
	//   `providerID` int(11) default NULL,
	//   `ref_providerID` int(11) default NULL,
	//   `email` varchar(255) NOT NULL default '',
	//   `ethnoracial` varchar(255) NOT NULL default '',
	//   `race` varchar(255) NOT NULL default '',
	//   `ethnicity` varchar(255) NOT NULL default '',
	//   `interpretter` varchar(255) NOT NULL default '',
	//   `migrantseasonal` varchar(255) NOT NULL default '',
	//   `family_size` varchar(255) NOT NULL default '',
	//   `monthly_income` varchar(255) NOT NULL default '',
	//   `homeless` varchar(255) NOT NULL default '',
	//   `financial_review` datetime default NULL,
	//   `pubpid` varchar(255) NOT NULL default '',
	//   `pid` bigint(20) NOT NULL default '0',
	//   `genericname1` varchar(255) NOT NULL default '',
	//   `genericval1` varchar(255) NOT NULL default '',
	//   `genericname2` varchar(255) NOT NULL default '',
	//   `genericval2` varchar(255) NOT NULL default '',
	//   `hipaa_mail` varchar(3) NOT NULL default '',
	//   `hipaa_voice` varchar(3) NOT NULL default '',
	//   `hipaa_notice` varchar(3) NOT NULL default '',
	//   `hipaa_message` varchar(20) NOT NULL default '',
	//   `hipaa_allowsms` VARCHAR(3) NOT NULL DEFAULT 'NO',
	//   `hipaa_allowemail` VARCHAR(3) NOT NULL DEFAULT 'NO',
	//   `squad` varchar(32) NOT NULL default '',
	//   `fitness` int(11) NOT NULL default '0',
	//   `referral_source` varchar(30) NOT NULL default '',
	//   `usertext1` varchar(255) NOT NULL DEFAULT '',
	//   `usertext2` varchar(255) NOT NULL DEFAULT '',
	//   `usertext3` varchar(255) NOT NULL DEFAULT '',
	//   `usertext4` varchar(255) NOT NULL DEFAULT '',
	//   `usertext5` varchar(255) NOT NULL DEFAULT '',
	//   `usertext6` varchar(255) NOT NULL DEFAULT '',
	//   `usertext7` varchar(255) NOT NULL DEFAULT '',
	//   `usertext8` varchar(255) NOT NULL DEFAULT '',
	//   `userlist1` varchar(255) NOT NULL DEFAULT '',
	//   `userlist2` varchar(255) NOT NULL DEFAULT '',
	//   `userlist3` varchar(255) NOT NULL DEFAULT '',
	//   `userlist4` varchar(255) NOT NULL DEFAULT '',
	//   `userlist5` varchar(255) NOT NULL DEFAULT '',
	//   `userlist6` varchar(255) NOT NULL DEFAULT '',
	//   `userlist7` varchar(255) NOT NULL DEFAULT '',
	//   `pricelevel` varchar(255) NOT NULL default 'standard',
	//   `regdate`     date DEFAULT NULL COMMENT 'Registration Date',
	//   `contrastart` date DEFAULT NULL COMMENT 'Date contraceptives initially used',
	//   `completed_ad` VARCHAR(3) NOT NULL DEFAULT 'NO',
	//   `ad_reviewed` date DEFAULT NULL,
	//   `vfc` varchar(255) NOT NULL DEFAULT '',
	//   `mothersname` varchar(255) NOT NULL DEFAULT '',
	//   `guardiansname` varchar(255) NOT NULL DEFAULT '',
	//   `allow_imm_reg_use` varchar(255) NOT NULL DEFAULT '',
	//   `allow_imm_info_share` varchar(255) NOT NULL DEFAULT '',
	//   `allow_health_info_ex` varchar(255) NOT NULL DEFAULT '',
	//   `allow_patient_portal` varchar(31) NOT NULL DEFAULT '',
	//   `deceased_date` datetime default NULL,
	//   `deceased_reason` varchar(255) NOT NULL default '',
	//   `soap_import_status` TINYINT(4) DEFAULT NULL COMMENT '1-Prescription Press 2-Prescription Import 3-Allergy Press 4-Allergy Import',
	//   UNIQUE KEY `pid` (`pid`),
	//   KEY `id` (`id`)
	// ) ENGINE=MyISAM AUTO_INCREMENT=1 ;

	$table->bigIncrements('id');
	$table->string('language', 255)->default('');
	$table->string('fname', 255)->default('');
	$table->string('lname', 255)->default('');
	$table->string('mname', 255)->default('');
	$table->date('DOB')->nullable();
	$table->string('street', 255)->default('');
	$table->string('postal_code', 255)->default('');
	$table->string('city', 255)->default('');
	$table->string('state', 255)->default('');
	$table->datetime('date')->nullable();
	$table->string('sex', 255)->default('');
	$table->integer('providerID')->length(11)->nullable();
	$table->string('pubpid', 255)->default('');
	$table->integer('pid')->length(20)->default('0');
});

DatabaseManager::schema()->create('phone_numbers', function($table) {
	// CREATE TABLE `phone_numbers` (
	//   `id` int(11) NOT NULL default '0',
	//   `country_code` varchar(5) default NULL,
	//   `area_code` char(3) default NULL,
	//   `prefix` char(3) default NULL,
	//   `number` varchar(4) default NULL,
	//   `type` int(11) default NULL,
	//   `foreign_id` int(11) default NULL,
	//   PRIMARY KEY  (`id`),
	//   KEY `foreign_id` (`foreign_id`)
	// ) ENGINE=MyISAM;

	$table->increments('id');
	$table->string('country_code', 5)->nullable();
	$table->char('area_code', 3)->nullable();
	$table->char('prefix', 3)->nullable();
	$table->string('number', 4)->nullable();
	$table->integer('type')->length(11)->nullable();
	$table->integer('foreign_id')->length(11)->nullable();
});

DatabaseManager::schema()->create('users', function($table) {
	// CREATE TABLE `users` (
	//   `id` bigint(20) NOT NULL auto_increment,
	//   `username` varchar(255) default NULL,
	//   `password` longtext,
	//   `authorized` tinyint(4) default NULL,
	//   `info` longtext,
	//   `source` tinyint(4) default NULL,
	//   `fname` varchar(255) default NULL,
	//   `mname` varchar(255) default NULL,
	//   `lname` varchar(255) default NULL,
	//   `federaltaxid` varchar(255) default NULL,
	//   `federaldrugid` varchar(255) default NULL,
	//   `upin` varchar(255) default NULL,
	//   `facility` varchar(255) default NULL,
	//   `facility_id` int(11) NOT NULL default '0',
	//   `see_auth` int(11) NOT NULL default '1',
	//   `active` tinyint(1) NOT NULL default '1',
	//   `npi` varchar(15) default NULL,
	//   `title` varchar(30) default NULL,
	//   `specialty` varchar(255) default NULL,
	//   `billname` varchar(255) default NULL,
	//   `email` varchar(255) default NULL,
	//   `url` varchar(255) default NULL,
	//   `assistant` varchar(255) default NULL,
	//   `organization` varchar(255) default NULL,
	//   `valedictory` varchar(255) default NULL,
	//   `street` varchar(60) default NULL,
	//   `streetb` varchar(60) default NULL,
	//   `city` varchar(30) default NULL,
	//   `state` varchar(30) default NULL,
	//   `zip` varchar(20) default NULL,
	//   `street2` varchar(60) default NULL,
	//   `streetb2` varchar(60) default NULL,
	//   `city2` varchar(30) default NULL,
	//   `state2` varchar(30) default NULL,
	//   `zip2` varchar(20) default NULL,
	//   `phone` varchar(30) default NULL,
	//   `fax` varchar(30) default NULL,
	//   `phonew1` varchar(30) default NULL,
	//   `phonew2` varchar(30) default NULL,
	//   `phonecell` varchar(30) default NULL,
	//   `notes` text,
	//   `cal_ui` tinyint(4) NOT NULL default '1',
	//   `taxonomy` varchar(30) NOT NULL DEFAULT '207Q00000X',
	//   `ssi_relayhealth` varchar(64) NULL,
	//   `calendar` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = appears in calendar',
	//   `abook_type` varchar(31) NOT NULL DEFAULT '',
	//   `pwd_expiration_date` date default NULL,
	//   `pwd_history1` longtext default NULL,
	//   `pwd_history2` longtext default NULL,
	//   `default_warehouse` varchar(31) NOT NULL DEFAULT '',
	//   `irnpool` varchar(31) NOT NULL DEFAULT '',
	//   `state_license_number` VARCHAR(25) DEFAULT NULL,
	//   `newcrop_user_role` VARCHAR(30) DEFAULT NULL,
	//   PRIMARY KEY  (`id`)
	// ) ENGINE=MyISAM AUTO_INCREMENT=1 ;

	$table->bigIncrements('id');
	$table->string('username', 255)->nullable();
	$table->longText('password');
	$table->integer('authorized')->length(4)->nullable();
	$table->string('fname', 255)->nullable();
	$table->string('mname', 255)->nullable();
	$table->string('lname', 255)->nullable();
	$table->string('federaltaxid', 255)->nullable();
	$table->string('federaldrugid', 255)->nullable();
	$table->string('facility', 255)->nullable();
	$table->integer('facility_id')->length(11)->default(0);
	$table->integer('see_auth')->length(11)->default(1);
	$table->integer('active')->length(1)->default(1);
	$table->string('npi', 15)->nullable();
	$table->integer('cal_ui')->length(4)->default(1);
	$table->string('taxonomy', 30)->default('207Q00000X');
	$table->integer('calendar')->length(1)->default(0)->comment = '1 = appears in calendar';
	$table->string('abook_type', 31)->default('');
	$table->string('state_license_number', 25)->nullable();
});

DatabaseManager::schema()->create('x12_partners', function($table) {
	// CREATE TABLE `x12_partners` (
	//   `id` int(11) NOT NULL default '0',
	//   `name` varchar(255) default NULL,
	//   `id_number` varchar(255) default NULL,
	//   `x12_sender_id` varchar(255) default NULL,
	//   `x12_receiver_id` varchar(255) default NULL,
	//   `x12_version` varchar(255) default NULL,
	//   `processing_format` enum('standard','medi-cal','cms','proxymed') default NULL,
	//   `x12_isa01` VARCHAR( 2 ) NOT NULL DEFAULT '00' COMMENT 'User logon Required Indicator',
	//   `x12_isa02` VARCHAR( 10 ) NOT NULL DEFAULT '          ' COMMENT 'User Logon',
	//   `x12_isa03` VARCHAR( 2 ) NOT NULL DEFAULT '00' COMMENT 'User password required Indicator',
	//   `x12_isa04` VARCHAR( 10 ) NOT NULL DEFAULT '          ' COMMENT 'User Password',
	//   `x12_isa05` char(2)     NOT NULL DEFAULT 'ZZ',
	//   `x12_isa07` char(2)     NOT NULL DEFAULT 'ZZ',
	//   `x12_isa14` char(1)     NOT NULL DEFAULT '0',
	//   `x12_isa15` char(1)     NOT NULL DEFAULT 'P',
	//   `x12_gs02`  varchar(15) NOT NULL DEFAULT '',
	//   `x12_per06` varchar(80) NOT NULL DEFAULT '',
	//   `x12_gs03`  varchar(15) NOT NULL DEFAULT '',
	//   PRIMARY KEY  (`id`)
	// ) ENGINE=MyISAM;

	$table->increments('id');
	$table->string('name', 255)->nullable();
	$table->string('id_number', 255)->nullable();
	$table->string('x12_version', 255)->nullable();
	$table->string('x12_isa01', 2)->default('00')->comment = 'User logon Required Indicator';
	$table->string('x12_isa02', 10)->default('          ')->comment = 'User Logon';
	$table->string('x12_isa03', 2)->default('00')->comment = 'User password required Indicator';
	$table->string('x12_isa04', 10)->default('          ')->comment = 'User Password';
	$table->char('x12_isa05', 2)->default('ZZ');
	$table->string('x12_sender_id', 255)->nullable();
	$table->char('x12_isa07', 2)->default('ZZ');
	$table->string('x12_receiver_id', 255)->nullable();
	$table->char('x12_isa14', 1)->default('0');
	$table->char('x12_isa15', 1)->default('P');
	$table->string('x12_gs02', 15)->default('');
	$table->string('x12_gs03', 15)->default('');
});

DatabaseManager::schema()->create('pqrs_import_files', function($table) {
	// CREATE TABLE `pqrs_import_files` (
	//   `id` bigint(20) NOT NULL AUTO_INCREMENT,
	//   `status` enum('Staged', 'Queued', 'Processing', 'Failed', 'Completed') DEFAULT 'Staged',
	//   `relative_path` varchar(255) NOT NULL,
	//   `size` int(11) NOT NULL,
	//   `md5` varchar(32) NOT NULL,
	//   `staged_datetime` datetime NOT NULL,
	//   `queued_datetime` datetime DEFAULT NULL,
	//   `processing_datetime` datetime DEFAULT NULL,
	//   `processing_id` int(11) DEFAULT NULL,
	//   `failed_datetime` datetime DEFAULT NULL,
	//   `failed_reason` text DEFAULT NULL,
	//   `completed_datetime` datetime DEFAULT NULL,
	//   PRIMARY KEY  (`id`),
	//   KEY `relative_path` (`relative_path`),
	//   KEY `processing_id` (`processing_id`),
	//   KEY `md5` (`md5`)
	// ) ENGINE=InnoDB;

	$table->increments('id');
	$table->enum('status', ['Staged', 'Queued', 'Processing', 'Failed', 'Completed'])->default('Staged');
	$table->string('relative_path', 255);
	$table->integer('size')->length(11);
	$table->string('md5', 32);
	$table->dateTime('staged_datetime');
	$table->dateTime('queued_datetime')->nullable();
	$table->dateTime('processing_datetime')->nullable();
	$table->integer('processing_id')->length(11)->nullable();
	$table->dateTime('failed_datetime')->nullable();
	$table->text('failed_reason')->nullable();
	$table->dateTime('completed_datetime')->nullable();
	$table->index('relative_path');
	$table->index('processing_id');
	$table->index('md5');
});
