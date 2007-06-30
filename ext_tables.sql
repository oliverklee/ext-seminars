#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
	tx_seminars_phone_mobile tinytext NOT NULL,
	tx_seminars_matriculation_number int(11) unsigned DEFAULT '0' NOT NULL,
	tx_seminars_planned_degree tinytext NOT NULL,
	tx_seminars_semester tinyint(4) unsigned DEFAULT '0' NOT NULL,
	tx_seminars_subject tinytext NOT NULL
);


#
# Table structure for table 'tx_seminars_seminars_place_mm'
#
CREATE TABLE tx_seminars_seminars_place_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
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
	object_type int(11) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	topic int(11) unsigned DEFAULT '0' NOT NULL,
	subtitle tinytext NOT NULL,
	teaser text NOT NULL,
	description text NOT NULL,
	event_type int(11) unsigned DEFAULT '0' NOT NULL,
	accreditation_number tinytext NOT NULL,
	credit_points int(11) unsigned DEFAULT '0' NOT NULL,
	begin_date int(11) unsigned DEFAULT '0' NOT NULL,
	end_date int(11) unsigned DEFAULT '0' NOT NULL,
	timeslots text NOT NULL,
	deadline_registration int(11) unsigned DEFAULT '0' NOT NULL,
	deadline_early_bird int(11) unsigned DEFAULT '0' NOT NULL,
	place int(11) unsigned DEFAULT '0' NOT NULL,
	room text NOT NULL,
	lodgings int(11) unsigned DEFAULT '0' NOT NULL,
	foods int(11) unsigned DEFAULT '0' NOT NULL,
	additional_times_places text NOT NULL,
	speakers int(11) unsigned DEFAULT '0' NOT NULL,
	partners int(11) unsigned DEFAULT '0' NOT NULL,
	tutors int(11) unsigned DEFAULT '0' NOT NULL,
	leaders int(11) unsigned DEFAULT '0' NOT NULL,
	price_regular decimal(7,2) DEFAULT '0.00' NOT NULL,
	price_regular_early decimal(7,2) DEFAULT '0.00' NOT NULL,
	price_regular_board decimal(7,2) DEFAULT '0.00' NOT NULL,
	price_special decimal(7,2) DEFAULT '0.00' NOT NULL,
	price_special_early decimal(7,2) DEFAULT '0.00' NOT NULL,
	price_special_board decimal(7,2) DEFAULT '0.00' NOT NULL,
	additional_information text NOT NULL,
	payment_methods tinytext NOT NULL,
	organizers tinytext NOT NULL,
	needs_registration tinyint(3) unsigned DEFAULT '0' NOT NULL,
	allows_multiple_registrations tinyint(3) unsigned DEFAULT '0' NOT NULL,
	attendees_min int(11) unsigned DEFAULT '0' NOT NULL,
	attendees_max int(11) unsigned DEFAULT '0' NOT NULL,
	cancelled tinyint(3) unsigned DEFAULT '0' NOT NULL,
	attendees int(11) unsigned DEFAULT '0' NOT NULL,
	enough_attendees tinyint(3) unsigned DEFAULT '0' NOT NULL,
	is_full tinyint(3) unsigned DEFAULT '0' NOT NULL,
	owner_feuser int(11) unsigned DEFAULT '0' NOT NULL,
	vips int(11) unsigned DEFAULT '0' NOT NULL,
	checkboxes int(11) unsigned DEFAULT '0' NOT NULL,
	uses_terms_2 tinyint(3) unsigned DEFAULT '0' NOT NULL,
	notes text NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_seminars_seminars_feusers_mm'
#
CREATE TABLE tx_seminars_seminars_feusers_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
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
	title tinytext NOT NULL,
	organization tinytext NOT NULL,
	homepage tinytext NOT NULL,
	description text NOT NULL,
	picture blob NOT NULL,
	notes text NOT NULL,
	address text NOT NULL,
	phone_work tinytext NOT NULL,
	phone_home tinytext NOT NULL,
	phone_mobile tinytext NOT NULL,
	fax tinytext NOT NULL,
	email tinytext NOT NULL,

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
	title tinytext NOT NULL,
	user int(11) unsigned DEFAULT '0' NOT NULL,
	seminar int(11) unsigned DEFAULT '0' NOT NULL,
	price text NOT NULL,
	seats int(11) unsigned DEFAULT '0' NOT NULL,
	total_price decimal(7,2) DEFAULT '0.00' NOT NULL,
	attendees_names text NOT NULL,
	paid tinyint(3) unsigned DEFAULT '0' NOT NULL,
	datepaid int(11) unsigned DEFAULT '0' NOT NULL,
	method_of_payment int(11) unsigned DEFAULT '0' NOT NULL,
	account_number tinytext NOT NULL,
	bank_code tinytext NOT NULL,
	bank_name tinytext NOT NULL,
	account_owner tinytext NOT NULL,
	name varchar(80) DEFAULT '' NOT NULL,
	gender tinyint(1) unsigned DEFAULT '0' NOT NULL,
	address tinytext NOT NULL,
	zip varchar(20) DEFAULT '' NOT NULL,
	city varchar(50) DEFAULT '' NOT NULL,
	country varchar(60) DEFAULT '' NOT NULL,
	telephone varchar(20) DEFAULT '' NOT NULL,
	email varchar(80) DEFAULT '' NOT NULL,
	been_there tinyint(3) unsigned DEFAULT '0' NOT NULL,
	interests text NOT NULL,
	expectations text NOT NULL,
	background_knowledge text NOT NULL,
	accommodation text NOT NULL,
	lodgings int(11) unsigned DEFAULT '0' NOT NULL,
	food text NOT NULL,
	foods int(11) unsigned DEFAULT '0' NOT NULL,
	known_from text NOT NULL,
	notes text NOT NULL,
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
	title tinytext NOT NULL,
	address text NOT NULL,
	homepage tinytext NOT NULL,
	directions text NOT NULL,
	notes text NOT NULL,

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
	title tinytext NOT NULL,
	homepage tinytext NOT NULL,
	email tinytext NOT NULL,
	email_footer text NOT NULL,
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
	title tinytext NOT NULL,
	description text NOT NULL,

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
	title tinytext NOT NULL,

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
	title tinytext NOT NULL,
	description text NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_seminars_seminars_checkboxes_mm'
#
CREATE TABLE tx_seminars_seminars_checkboxes_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
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
	title tinytext NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_seminars_seminars_lodgings_mm'
#
CREATE TABLE tx_seminars_seminars_lodgings_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
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
	title tinytext NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_seminars_seminars_foods_mm'
#
CREATE TABLE tx_seminars_seminars_foods_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
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
	seminar int(11) unsigned DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	begin_date int(11) unsigned DEFAULT '0' NOT NULL,
	end_date int(11) unsigned DEFAULT '0' NOT NULL,
	entry_date int(11) unsigned DEFAULT '0' NOT NULL,
	speakers int(11) unsigned DEFAULT '0' NOT NULL,
	place int(11) unsigned DEFAULT '0' NOT NULL,
	room text NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_seminars_timeslots_speakers_mm'
#
CREATE TABLE tx_seminars_timeslots_speakers_mm (
	uid_local int(11) unsigned DEFAULT '0' NOT NULL,
	uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) unsigned DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);
