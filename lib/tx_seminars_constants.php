<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2009 Oliver Klee (typo3-coding@oliverklee.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software); you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation); either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY); without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(t3lib_extMgm::extPath('oelib').'tx_oelib_commonConstants.php');

define('SEMINARS_TABLE_SEMINARS', 'tx_seminars_seminars');
define('SEMINARS_TABLE_SPEAKERS', 'tx_seminars_speakers');
define('SEMINARS_TABLE_SITES', 'tx_seminars_sites');
define('SEMINARS_TABLE_ORGANIZERS', 'tx_seminars_organizers');
define('SEMINARS_TABLE_ATTENDANCES', 'tx_seminars_attendances');
define('SEMINARS_TABLE_PAYMENT_METHODS', 'tx_seminars_payment_methods');
define('SEMINARS_TABLE_EVENT_TYPES', 'tx_seminars_event_types');
define('SEMINARS_TABLE_CHECKBOXES', 'tx_seminars_checkboxes');
define('SEMINARS_TABLE_LODGINGS', 'tx_seminars_lodgings');
define('SEMINARS_TABLE_FOODS', 'tx_seminars_foods');
define('SEMINARS_TABLE_TIME_SLOTS', 'tx_seminars_timeslots');
define('SEMINARS_TABLE_TARGET_GROUPS', 'tx_seminars_target_groups');
define('SEMINARS_TABLE_CATEGORIES', 'tx_seminars_categories');
define('SEMINARS_TABLE_SKILLS', 'tx_seminars_skills');
define('SEMINARS_TABLE_TEST', 'tx_seminars_test');
define('SEMINARS_TABLE_PARTNER_MAIN', 'tx_partner_main');
define('SEMINARS_TABLE_PARTNER_CONTACT_INFO', 'tx_partner_contact_info');
define('SEMINARS_TABLE_PRICES', 'tx_seminars_prices');

define('SEMINARS_TABLE_SEMINARS_MANAGERS_MM', 'tx_seminars_seminars_feusers_mm');
define('SEMINARS_TABLE_SEMINARS_SPEAKERS_MM', 'tx_seminars_seminars_speakers_mm');
define('SEMINARS_TABLE_SEMINARS_PARTNERS_MM', 'tx_seminars_seminars_speakers_mm_partners');
define('SEMINARS_TABLE_SEMINARS_TUTORS_MM', 'tx_seminars_seminars_speakers_mm_tutors');
define('SEMINARS_TABLE_SEMINARS_LEADERS_MM', 'tx_seminars_seminars_speakers_mm_leaders');
define('SEMINARS_TABLE_SEMINARS_SITES_MM', 'tx_seminars_seminars_place_mm');
define('SEMINARS_TABLE_SEMINARS_CHECKBOXES_MM', 'tx_seminars_seminars_checkboxes_mm');
define('SEMINARS_TABLE_ATTENDANCES_CHECKBOXES_MM', 'tx_seminars_attendances_checkboxes_mm');
define('SEMINARS_TABLE_SEMINARS_LODGINGS_MM', 'tx_seminars_seminars_lodgings_mm');
define('SEMINARS_TABLE_ATTENDANCES_LODGINGS_MM', 'tx_seminars_attendances_lodgings_mm');
define('SEMINARS_TABLE_SEMINARS_FOODS_MM', 'tx_seminars_seminars_foods_mm');
define('SEMINARS_TABLE_ATTENDANCES_FOODS_MM', 'tx_seminars_attendances_foods_mm');
define('SEMINARS_TABLE_TIME_SLOTS_SPEAKERS_MM', 'tx_seminars_timeslots_speakers_mm');
define('SEMINARS_TABLE_SEMINARS_TARGET_GROUPS_MM', 'tx_seminars_seminars_target_groups_mm');
define('SEMINARS_TABLE_SEMINARS_CATEGORIES_MM', 'tx_seminars_seminars_categories_mm');
define('SEMINARS_TABLE_SPEAKERS_SKILLS_MM', 'tx_seminars_speakers_skills_mm');
define('SEMINARS_TABLE_SEMINARS_ORGANIZING_PARTNERS_MM', 'tx_seminars_seminars_organizing_partners_mm');
define('SEMINARS_TABLE_SEMINARS_REQUIREMENTS_MM', 'tx_seminars_seminars_requirements_mm');

define('SEMINARS_RECORD_TYPE_COMPLETE', 0);
define('SEMINARS_RECORD_TYPE_TOPIC', 1);
define('SEMINARS_RECORD_TYPE_DATE', 2);

define('SEMINARS_PARTNER_MAIN_TYPE_PERSON', 0);
define('SEMINARS_PARTNER_MAIN_TYPE_ORGANIZATION', 1);

define('SEMINARS_PARTNER_CONTACT_INFORMATION_TYPE_PHONE', 0);
define('SEMINARS_PARTNER_CONTACT_INFORMATION_TYPE_MOBILE', 1);
define('SEMINARS_PARTNER_CONTACT_INFORMATION_TYPE_FAX', 2);
define('SEMINARS_PARTNER_CONTACT_INFORMATION_TYPE_EMAIL', 3);
define('SEMINARS_PARTNER_CONTACT_INFORMATION_TYPE_URL', 4);

define('SEMINARS_PRICE_CURRENCY_LEFT', 0);
define('SEMINARS_PRICE_CURRENCY_RIGHT', 1);

define('SEMINARS_PRICE_INCLUDING_TAX', 0);
define('SEMINARS_PRICE_EXCLUDING_TAX', 1);

define('SEMINARS_UPLOAD_PATH', 'uploads/tx_seminars/');

?>