#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
    tx_seminars_registration int(11) unsigned DEFAULT '0' NOT NULL
);


#
# Table structure for table 'fe_groups'
#
CREATE TABLE fe_groups (
    # @deprecated #1543 will be removed in seminars 5.0
    tx_seminars_publish_events tinyint(1) DEFAULT '0' NOT NULL,
    tx_seminars_events_pid int(11) unsigned DEFAULT '0' NOT NULL,
    tx_seminars_auxiliary_records_pid int(11) unsigned DEFAULT '0' NOT NULL,
    tx_seminars_reviewer int(11) unsigned DEFAULT '0' NOT NULL,
    tx_seminars_default_categories int(11) unsigned DEFAULT '0' NOT NULL,
    tx_seminars_default_organizer int(11) unsigned DEFAULT '0' NOT NULL
);


#
# Table structure for table 'be_groups'
#
CREATE TABLE be_groups (
    tx_seminars_events_folder int(11) unsigned DEFAULT '0' NOT NULL,
    tx_seminars_registrations_folder int(11) unsigned DEFAULT '0' NOT NULL,
    tx_seminars_auxiliaries_folder int(11) unsigned DEFAULT '0' NOT NULL
);


#
# Table structure for table 'tx_seminars_test'
#
CREATE TABLE tx_seminars_test (
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    title tinytext,

    KEY dummy (is_dummy_record)
);


#
# Table structure for table 'tx_seminars_test_mm'
#
CREATE TABLE tx_seminars_test_test_mm (
    uid_local int(11) unsigned DEFAULT '0' NOT NULL,
    uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    tablenames varchar(30) DEFAULT '' NOT NULL,
    sorting int(11) unsigned DEFAULT '0' NOT NULL,
    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
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
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
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
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
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
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
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
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
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
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
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
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
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
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
);


#
# Table structure for table 'tx_seminars_seminars_organizers_mm'
#
CREATE TABLE tx_seminars_seminars_organizers_mm (
    uid_local int(11) unsigned DEFAULT '0' NOT NULL,
    uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    tablenames varchar(30) DEFAULT '' NOT NULL,
    sorting int(11) unsigned DEFAULT '0' NOT NULL,
    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
);


#
# Table structure for table 'tx_seminars_seminars_organizing_partners_mm'
#
CREATE TABLE tx_seminars_seminars_organizing_partners_mm (
    uid_local int(11) unsigned DEFAULT '0' NOT NULL,
    uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    tablenames varchar(30) DEFAULT '' NOT NULL,
    sorting int(11) unsigned DEFAULT '0' NOT NULL,
    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
);


#
# Table structure for table 'tx_seminars_seminars_payment_methods_mm'
#
CREATE TABLE tx_seminars_seminars_payment_methods_mm (
    uid_local int(11) unsigned DEFAULT '0' NOT NULL,
    uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    tablenames varchar(30) DEFAULT '' NOT NULL,
    sorting int(11) unsigned DEFAULT '0' NOT NULL,
    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
);


#
# Table structure for table 'tx_seminars_seminars'
#
CREATE TABLE tx_seminars_seminars (
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    object_type int(11) unsigned DEFAULT '0' NOT NULL,
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
    timeslots int(3) unsigned DEFAULT '0' NOT NULL,
    begin_date_registration int(11) unsigned DEFAULT '0' NOT NULL,
    deadline_registration int(11) unsigned DEFAULT '0' NOT NULL,
    deadline_early_bird int(11) unsigned DEFAULT '0' NOT NULL,
    deadline_unregistration int(11) unsigned DEFAULT '0' NOT NULL,
    expiry int(11) unsigned DEFAULT '0' NOT NULL,
    details_page tinytext,
    place int(11) unsigned DEFAULT '0' NOT NULL,
    room text,
    lodgings int(11) unsigned DEFAULT '0' NOT NULL,
    foods int(11) unsigned DEFAULT '0' NOT NULL,
    speakers int(11) unsigned DEFAULT '0' NOT NULL,
    partners int(11) unsigned DEFAULT '0' NOT NULL,
    tutors int(11) unsigned DEFAULT '0' NOT NULL,
    leaders int(11) unsigned DEFAULT '0' NOT NULL,
    language char(2) DEFAULT '' NOT NULL,
    price_regular decimal(10,2) DEFAULT '0.00' NOT NULL,
    price_regular_early decimal(10,2) DEFAULT '0.00' NOT NULL,
    price_special decimal(10,2) DEFAULT '0.00' NOT NULL,
    price_special_early decimal(10,2) DEFAULT '0.00' NOT NULL,
    additional_information text,
    payment_methods int(11) unsigned DEFAULT '0' NOT NULL,
    organizers int(11) unsigned DEFAULT '0' NOT NULL,
    organizing_partners int(11) unsigned DEFAULT '0' NOT NULL,
    event_takes_place_reminder_sent int(1) unsigned DEFAULT '0' NOT NULL,
    cancelation_deadline_reminder_sent int(1) unsigned DEFAULT '0' NOT NULL,
    needs_registration tinyint(1) unsigned DEFAULT '0' NOT NULL,
    allows_multiple_registrations tinyint(3) unsigned DEFAULT '0' NOT NULL,
    attendees_min int(11) unsigned DEFAULT '0' NOT NULL,
    attendees_max int(11) unsigned DEFAULT '0' NOT NULL,
    queue_size int(1) unsigned DEFAULT '0' NOT NULL,
    offline_attendees int(11) unsigned DEFAULT '0' NOT NULL,
    target_groups int(11) unsigned DEFAULT '0' NOT NULL,
    # @deprecated #1763 will be removed in seminars 5.0
    skip_collision_check tinyint(1) unsigned DEFAULT '0' NOT NULL,
    # @deprecated #1324 will be removed in seminars 5.0
    registrations int(11) unsigned DEFAULT '0' NOT NULL,
    cancelled tinyint(1) unsigned DEFAULT '0' NOT NULL,
    owner_feuser int(11) unsigned DEFAULT '0' NOT NULL,
    vips int(11) unsigned DEFAULT '0' NOT NULL,
    checkboxes int(11) unsigned DEFAULT '0' NOT NULL,
    uses_terms_2 tinyint(1) unsigned DEFAULT '0' NOT NULL,
    notes text,
    attached_files int(11) unsigned DEFAULT '0' NOT NULL,
    image int(11) unsigned DEFAULT '0' NOT NULL,
    requirements int(11) unsigned DEFAULT '0' NOT NULL,
    dependencies int(11) unsigned DEFAULT '0' NOT NULL,
    # @deprecated #1543 will be removed in seminars 5.0
    publication_hash text,
    organizers_notified_about_minimum_reached tinyint(1) unsigned DEFAULT '0' NOT NULL,
    mute_notification_emails tinyint(1) unsigned DEFAULT '0' NOT NULL,
    automatic_confirmation_cancelation tinyint(1) unsigned DEFAULT '0' NOT NULL,
    price_on_request tinyint(1) unsigned DEFAULT '0' NOT NULL,
    date_of_last_registration_digest int(11) unsigned DEFAULT '0' NOT NULL,

    KEY dummy (is_dummy_record),
    KEY object_type (object_type),
    KEY topic (topic),
    KEY event_takes_place_reminder_sent (event_takes_place_reminder_sent),
    KEY cancelation_deadline_reminder_sent (cancelation_deadline_reminder_sent),
    FULLTEXT index_event_searchfields (accreditation_number),
    FULLTEXT index_topic_searchfields (title,subtitle,description)
) ENGINE = MyISAM;


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
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
);


#
# Table structure for table 'tx_seminars_speakers'
#
CREATE TABLE tx_seminars_speakers (
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    owner int(11) unsigned DEFAULT '0' NOT NULL,
    title tinytext,
    organization tinytext,
    homepage tinytext,
    description text,
    image int(11) unsigned DEFAULT '0' NOT NULL,
    skills int(11) unsigned DEFAULT '0' NOT NULL,
    notes text,
    address text,
    phone_work tinytext,
    phone_home tinytext,
    phone_mobile tinytext,
    email tinytext,
    gender tinyint(1) unsigned DEFAULT '0' NOT NULL,
    cancelation_period int(11) unsigned DEFAULT '0' NOT NULL,

    KEY dummy (is_dummy_record),
    FULLTEXT index_searchfields (title)
) ENGINE = MyISAM;


#
# Table structure for table 'tx_seminars_speakers_skills_mm'
#
CREATE TABLE tx_seminars_speakers_skills_mm (
    uid_local int(11) unsigned DEFAULT '0' NOT NULL,
    uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    tablenames varchar(30) DEFAULT '' NOT NULL,
    sorting int(11) unsigned DEFAULT '0' NOT NULL,
    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
);


#
# Table structure for table 'tx_seminars_attendances'
#
CREATE TABLE tx_seminars_attendances (
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    title tinytext,
    user int(11) unsigned DEFAULT '0' NOT NULL,
    seminar int(11) unsigned DEFAULT '0' NOT NULL,
    registration_queue tinyint(1) unsigned DEFAULT '0' NOT NULL,
    price tinytext,
    price_code tinytext,
    seats int(3) unsigned DEFAULT '0' NOT NULL,
    registered_themselves tinyint(1) unsigned DEFAULT '0' NOT NULL,
    total_price decimal(10,2) DEFAULT '0.00' NOT NULL,
    # @deprecated #1990 will be removed in seminars 5.0
    currency int(11) unsigned DEFAULT '0' NOT NULL,
    # @deprecated #1990 will be removed in seminars 5.0
    including_tax tinyint(1) unsigned DEFAULT '0' NOT NULL,
    attendees_names text,
    additional_persons int(11) unsigned DEFAULT '0' NOT NULL,
    datepaid int(11) unsigned DEFAULT '0' NOT NULL,
    method_of_payment int(11) unsigned DEFAULT '0' NOT NULL,
    separate_billing_address tinyint(1) unsigned DEFAULT '0' NOT NULL,
    company tinytext,
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

    KEY dummy (is_dummy_record),
    KEY seminar (seminar),
    KEY user (user)
);


#
# Table structure for table 'tx_seminars_sites'
#
CREATE TABLE tx_seminars_sites (
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    owner int(11) unsigned DEFAULT '0' NOT NULL,
    title tinytext,
    address text,
    zip tinytext,
    city tinytext,
    country char(2) DEFAULT '' NOT NULL,
    homepage tinytext,
    directions text,
    notes text,

    KEY dummy (is_dummy_record),
    FULLTEXT index_searchfields (title,city)
) ENGINE = MyISAM;


#
# Table structure for table 'tx_seminars_organizers'
#
CREATE TABLE tx_seminars_organizers (
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    title tinytext,
    homepage tinytext,
    email tinytext,
    email_footer text,
    # @deprecated #1922 will be removed in seminars 5.0
    attendances_pid int(11) unsigned DEFAULT '0' NOT NULL,
    description text,

    KEY dummy (is_dummy_record)
);


#
# Table structure for table 'tx_seminars_payment_methods'
#
CREATE TABLE tx_seminars_payment_methods (
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    title tinytext,
    description text,

    KEY dummy (is_dummy_record)
);


#
# Table structure for table 'tx_seminars_event_types'
#
CREATE TABLE tx_seminars_event_types (
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    title tinytext,
    single_view_page int(11) unsigned DEFAULT '0' NOT NULL,

    KEY dummy (is_dummy_record),
    FULLTEXT index_searchfields (title)
) ENGINE = MyISAM;


#
# Table structure for table 'tx_seminars_checkboxes'
#
CREATE TABLE tx_seminars_checkboxes (
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    owner int(11) unsigned DEFAULT '0' NOT NULL,
    title tinytext,
    description text,

    KEY dummy (is_dummy_record)
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
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
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
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
);


#
# Table structure for table 'tx_seminars_lodgings'
#
CREATE TABLE tx_seminars_lodgings (
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    title tinytext,

    KEY dummy (is_dummy_record)
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
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
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
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
);


#
# Table structure for table 'tx_seminars_foods'
#
CREATE TABLE tx_seminars_foods (
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    title tinytext,

    KEY dummy (is_dummy_record)
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
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
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
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
);


#
# Table structure for table 'tx_seminars_timeslots'
#
CREATE TABLE tx_seminars_timeslots (
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    seminar int(11) unsigned DEFAULT '0' NOT NULL,
    begin_date int(11) unsigned DEFAULT '0' NOT NULL,
    end_date int(11) unsigned DEFAULT '0' NOT NULL,
    entry_date int(11) unsigned DEFAULT '0' NOT NULL,
    speakers int(11) unsigned DEFAULT '0' NOT NULL,
    place int(11) unsigned DEFAULT '0' NOT NULL,
    room text,

    KEY dummy (is_dummy_record),
    KEY seminar (seminar)
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
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
);


#
# Table structure for table 'tx_seminars_target_groups'
#
CREATE TABLE tx_seminars_target_groups (
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    owner int(11) unsigned DEFAULT '0' NOT NULL,
    title tinytext,
    minimum_age tinyint(3) unsigned DEFAULT '0' NOT NULL,
    maximum_age tinyint(3) unsigned DEFAULT '0' NOT NULL,

    KEY dummy (is_dummy_record),
    FULLTEXT index_searchfields (title)
) ENGINE = MyISAM;


#
# Table structure for table 'tx_seminars_categories'
#
CREATE TABLE tx_seminars_categories (
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    title tinytext,
    icon int(11) unsigned DEFAULT '0' NOT NULL,
    single_view_page int(11) unsigned DEFAULT '0' NOT NULL,

    KEY dummy (is_dummy_record),
    FULLTEXT index_searchfields (title)
) ENGINE = MyISAM;


#
# Table structure for table 'tx_seminars_skills'
#
CREATE TABLE tx_seminars_skills (
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    title tinytext,

    KEY dummy (is_dummy_record)
);


#
# Table structure for table 'tx_seminars_seminars_requirements_mm'
#
CREATE TABLE tx_seminars_seminars_requirements_mm (
    uid_local int(11) unsigned DEFAULT '0' NOT NULL,
    uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    tablenames varchar(30) DEFAULT '' NOT NULL,
    sorting int(11) unsigned DEFAULT '0' NOT NULL,
    sorting_foreign int(11) unsigned DEFAULT '0' NOT NULL,

    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
);


#
# Table structure for table 'tx_seminars_usergroups_categories_mm'
#
CREATE TABLE tx_seminars_usergroups_categories_mm (
    uid_local int(11) unsigned DEFAULT '0' NOT NULL,
    uid_foreign int(11) unsigned DEFAULT '0' NOT NULL,
    is_dummy_record tinyint(1) unsigned DEFAULT '0' NOT NULL,
    tablenames varchar(30) DEFAULT '' NOT NULL,
    sorting int(11) unsigned DEFAULT '0' NOT NULL,
    KEY uid_local (uid_local),
    KEY uid_foreign (uid_foreign),
    KEY dummy (is_dummy_record)
);
