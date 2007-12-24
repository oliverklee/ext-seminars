#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	tx_seminars_phone_mobile tinytext,
	tx_seminars_matriculation_number int(11) unsigned DEFAULT '0' NOT NULL,
	tx_seminars_planned_degree tinytext,
	tx_seminars_semester tinyint(4) unsigned DEFAULT '0' NOT NULL,
	tx_seminars_subject tinytext
);


#
# Table structure for table 'tx_seminars_seminars_place_mm'
#
CREATE TABLE tx_seminars_seminars_place_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_seminars_seminars_speakers_mm'
#
CREATE TABLE tx_seminars_seminars_speakers_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_seminars_seminars_speakers_mm_partners'
#
CREATE TABLE tx_seminars_seminars_speakers_mm_partners (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_seminars_seminars_speakers_mm_tutors'
#
CREATE TABLE tx_seminars_seminars_speakers_mm_tutors (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_seminars_seminars_speakers_mm_leaders'
#
CREATE TABLE tx_seminars_seminars_speakers_mm_leaders (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_seminars_seminars_target_groups_mm'
#
CREATE TABLE tx_seminars_seminars_target_groups_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_seminars_seminars_categories_mm'
#
CREATE TABLE tx_seminars_seminars_categories_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_seminars_seminars'
#
CREATE TABLE tx_seminars_seminars (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	object_type int(11) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	title tinytext,
	topic int(11) unsigned DEFAULT '0' NOT NULL,
	subtitle tinytext,
	categories int(11) unsigned DEFAULT '0' NOT NULL,
	teaser text,
	description text,
	event_type int(11) unsigned DEFAULT '0' NOT NULL,
	accreditation_number tinytext,
	credit_points int(11) unsigned DEFAULT '0' NOT NULL,
	begin_date int(11) unsigned DEFAULT '0' NOT NULL,
	end_date int(11) unsigned DEFAULT '0' NOT NULL,
	timeslots text,
	deadline_registration int(11) unsigned DEFAULT '0' NOT NULL,
	deadline_early_bird int(11) unsigned DEFAULT '0' NOT NULL,
	deadline_unregistration int(11) unsigned DEFAULT '0' NOT NULL,
	place int(11) unsigned DEFAULT '0' NOT NULL,
	room text,
	lodgings int(11) unsigned DEFAULT '0' NOT NULL,
	foods int(11) unsigned DEFAULT '0' NOT NULL,
	additional_times_places text,
	speakers int(11) unsigned DEFAULT '0' NOT NULL,
	partners int(11) unsigned DEFAULT '0' NOT NULL,
	tutors int(11) unsigned DEFAULT '0' NOT NULL,
	leaders int(11) unsigned DEFAULT '0' NOT NULL,
	language varchar(2) DEFAULT '',
	price_regular decimal(7,2) DEFAULT '0.00' NOT NULL,
	price_regular_early decimal(7,2) DEFAULT '0.00' NOT NULL,
	price_regular_board decimal(7,2) DEFAULT '0.00' NOT NULL,
	price_special decimal(7,2) DEFAULT '0.00' NOT NULL,
	price_special_early decimal(7,2) DEFAULT '0.00' NOT NULL,
	price_special_board decimal(7,2) DEFAULT '0.00' NOT NULL,
	additional_information text,
	payment_methods tinytext,
	organizers tinytext,
	allows_multiple_registrations tinyint(3) unsigned DEFAULT '0' NOT NULL,
	attendees_min int(11) unsigned DEFAULT '0' NOT NULL,
	attendees_max int(11) unsigned DEFAULT '0' NOT NULL,
	queue_size int(11) unsigned DEFAULT '0' NOT NULL,
	target_groups int(11) unsigned DEFAULT '0' NOT NULL,
	cancelled tinyint(3) unsigned DEFAULT '0' NOT NULL,
	owner_feuser int(11) unsigned DEFAULT '0' NOT NULL,
	vips int(11) unsigned DEFAULT '0' NOT NULL,
	checkboxes int(11) unsigned DEFAULT '0' NOT NULL,
	uses_terms_2 tinyint(3) unsigned DEFAULT '0' NOT NULL,
	notes text,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_seminars_seminars_feusers_mm'
#
CREATE TABLE tx_seminars_seminars_feusers_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_seminars_speakers'
#
CREATE TABLE tx_seminars_speakers (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	title tinytext,
	organization tinytext,
	homepage tinytext,
	description text,
	picture blob,
	notes text,
	address text,
	phone_work tinytext,
	phone_home tinytext,
	phone_mobile tinytext,
	fax tinytext,
	email tinytext,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_seminars_attendances'
#
CREATE TABLE tx_seminars_attendances (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	title tinytext,
	user int(11) unsigned DEFAULT '0' NOT NULL,
	seminar int(11) unsigned DEFAULT '0' NOT NULL,
	registration_queue tinyint(4) unsigned DEFAULT '0' NOT NULL,
	price text,
	seats int(11) unsigned DEFAULT '0' NOT NULL,
	total_price decimal(7,2) DEFAULT '0.00' NOT NULL,
	attendees_names text,
	paid tinyint(3) unsigned DEFAULT '0' NOT NULL,
	datepaid int(11) unsigned DEFAULT '0' NOT NULL,
	method_of_payment int(11) unsigned DEFAULT '0' NOT NULL,
	account_number tinytext,
	bank_code tinytext,
	bank_name tinytext,
	account_owner tinytext,
	name varchar(80) DEFAULT '' NOT NULL,
	gender tinyint(1) unsigned DEFAULT '0' NOT NULL,
	address tinytext,
	zip varchar(20) DEFAULT '' NOT NULL,
	city varchar(50) DEFAULT '' NOT NULL,
	country varchar(60) DEFAULT '' NOT NULL,
	telephone varchar(20) DEFAULT '' NOT NULL,
	email varchar(80) DEFAULT '' NOT NULL,
	been_there tinyint(3) unsigned DEFAULT '0' NOT NULL,
	interests text,
	expectations text,
	background_knowledge text,
	accommodation text,
	lodgings int(11) unsigned DEFAULT '0' NOT NULL,
	food text,
	foods int(11) unsigned DEFAULT '0' NOT NULL,
	known_from text,
	notes text,
	kids int(11) unsigned DEFAULT '0' NOT NULL,
	checkboxes int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_seminars_sites'
#
CREATE TABLE tx_seminars_sites (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	title tinytext,
	address text,
	city tinytext,
	country varchar(2) DEFAULT '',
	homepage tinytext,
	directions text,
	notes text,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_seminars_organizers'
#
CREATE TABLE tx_seminars_organizers (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	title tinytext,
	homepage tinytext,
	email tinytext,
	email_footer text,
	attendances_pid int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_seminars_payment_methods'
#
CREATE TABLE tx_seminars_payment_methods (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	title tinytext,
	description text,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_seminars_event_types'
#
CREATE TABLE tx_seminars_event_types (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	title tinytext,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_seminars_checkboxes'
#
CREATE TABLE tx_seminars_checkboxes (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	title tinytext,
	description text,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_seminars_seminars_checkboxes_mm'
#
CREATE TABLE tx_seminars_seminars_checkboxes_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_seminars_attendances_checkboxes_mm'
#
CREATE TABLE tx_seminars_attendances_checkboxes_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_seminars_lodgings'
#
CREATE TABLE tx_seminars_lodgings (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	title tinytext,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_seminars_seminars_lodgings_mm'
#
CREATE TABLE tx_seminars_seminars_lodgings_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_seminars_attendances_lodgings_mm'
#
CREATE TABLE tx_seminars_attendances_lodgings_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_seminars_foods'
#
CREATE TABLE tx_seminars_foods (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	title tinytext,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_seminars_seminars_foods_mm'
#
CREATE TABLE tx_seminars_seminars_foods_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_seminars_attendances_foods_mm'
#
CREATE TABLE tx_seminars_attendances_foods_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_seminars_timeslots'
#
CREATE TABLE tx_seminars_timeslots (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	seminar int(11) unsigned DEFAULT '0' NOT NULL,
	title tinytext,
	begin_date int(11) unsigned DEFAULT '0' NOT NULL,
	end_date int(11) unsigned DEFAULT '0' NOT NULL,
	entry_date int(11) unsigned DEFAULT '0' NOT NULL,
	speakers int(11) unsigned DEFAULT '0' NOT NULL,
	place int(11) unsigned DEFAULT '0' NOT NULL,
	room text,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_seminars_timeslots_speakers_mm'
#
CREATE TABLE tx_seminars_timeslots_speakers_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Table structure for table 'tx_seminars_target_groups'
#
CREATE TABLE tx_seminars_target_groups (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	title tinytext,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_seminars_categories'
#
CREATE TABLE tx_seminars_categories (
	uid int(11) unsigned DEFAULT '0' NOT NULL auto_increment,
	pid int(11) unsigned DEFAULT '0' NOT NULL,
	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
	title tinytext,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


