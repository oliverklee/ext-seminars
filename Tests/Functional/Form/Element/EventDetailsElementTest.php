<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Form\Element;

use OliverKlee\Seminars\Form\Element\EventDetailsElement;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\Element\GroupElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Recordlist\LinkHandler\FileLinkHandler;
use TYPO3\CMS\Recordlist\LinkHandler\FolderLinkHandler;
use TYPO3\CMS\Recordlist\LinkHandler\MailLinkHandler;
use TYPO3\CMS\Recordlist\LinkHandler\PageLinkHandler;
use TYPO3\CMS\Recordlist\LinkHandler\TelephoneLinkHandler;
use TYPO3\CMS\Recordlist\LinkHandler\UrlLinkHandler;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Form\Element\EventDetailsElement
 */
final class EventDetailsElementTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/seminars',
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/AdminBackEndUser.csv');
        $backendUser = $this->setUpBackendUser(1);
        $GLOBALS['LANG'] = $this->get(LanguageServiceFactory::class)->createFromUserPreferences($backendUser);
    }

    private function getDateFormat(): string
    {
        return $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?? '';
    }

    /**
     * @test
     */
    public function isAbstractFormElement(): void
    {
        $subject = new EventDetailsElement(new NodeFactory(), []);

        self::assertInstanceOf(AbstractFormElement::class, $subject);
    }

    /**
     * @test
     */
    public function isGroupElement(): void
    {
        $subject = new EventDetailsElement(new NodeFactory(), []);

        self::assertInstanceOf(GroupElement::class, $subject);
    }

    /**
     * @param array<string, mixed> $additionalData
     */
    private function renderWithData(array $additionalData = []): string
    {
        $data = [
            'command' => 'edit',
            'tableName' => 'tx_seminars_attendances',
            'vanillaUid' => 8,
            'returnUrl' => '/typo3/record/edit?token=84390b2833c7669e2103011f947767878827476b&edit%5Btx_seminars_attendances%5D%5B8%5D=edit&returnUrl=%2Ftypo3%2Fmodule%2Fweb%2Flist%3Ftoken%3Dea1861930b995b634b38bed60afc15dce9c7307f%26id%3D12%26table%3D%26pointer%3D1&route=%2Frecord%2Fedit',
            'recordTitle' => 'TCCD / Anna A. Attendee,    ',
            'parentPageRow' =>
                [
                    'uid' => 12,
                    'pid' => 5,
                    'tstamp' => 1627921975,
                    'crdate' => 1627921808,
                    'cruser_id' => 1,
                    'deleted' => 0,
                    'hidden' => 0,
                    'starttime' => 0,
                    'endtime' => 0,
                    'fe_group' => '0',
                    'sorting' => 512,
                    'rowDescription' => null,
                    'editlock' => 0,
                    'sys_language_uid' => 0,
                    'l10n_parent' => 0,
                    'l10n_source' => 0,
                    'l10n_state' => null,
                    't3_origuid' => 0,
                    'l10n_diffsource' => '{"title":null}',
                    't3ver_oid' => 0,
                    't3ver_wsid' => 0,
                    't3ver_state' => 0,
                    't3ver_stage' => 0,
                    'perms_userid' => 1,
                    'perms_groupid' => 0,
                    'perms_user' => 31,
                    'perms_group' => 27,
                    'perms_everybody' => 0,
                    'title' => 'Registrations',
                    'slug' => '/registrations',
                    'doktype' => 254,
                    'TSconfig' => null,
                    'is_siteroot' => 0,
                    'php_tree_stop' => 0,
                    'url' => '',
                    'shortcut' => 0,
                    'shortcut_mode' => 0,
                    'subtitle' => '',
                    'layout' => 0,
                    'target' => '',
                    'media' => 0,
                    'lastUpdated' => 0,
                    'keywords' => null,
                    'cache_timeout' => 0,
                    'cache_tags' => '',
                    'newUntil' => 0,
                    'description' => null,
                    'no_search' => 0,
                    'SYS_LASTCHANGED' => 0,
                    'abstract' => null,
                    'module' => '',
                    'extendToSubpages' => 0,
                    'author' => '',
                    'author_email' => '',
                    'nav_title' => '',
                    'nav_hide' => 0,
                    'content_from_pid' => 0,
                    'mount_pid' => 0,
                    'mount_pid_ol' => 0,
                    'l18n_cfg' => 0,
                    'fe_login_mode' => 0,
                    'backend_layout' => '',
                    'backend_layout_next_level' => '',
                    'tsconfig_includes' => null,
                    'categories' => 0,
                    'seo_title' => '',
                    'no_index' => 0,
                    'no_follow' => 0,
                    'og_title' => '',
                    'og_description' => null,
                    'og_image' => 0,
                    'twitter_title' => '',
                    'twitter_description' => null,
                    'twitter_image' => 0,
                    'twitter_card' => '',
                    'canonical_link' => '',
                    'sitemap_priority' => '0.5',
                    'sitemap_changefreq' => '',
                ],
            'defaultLanguagePageRow' => null,
            'neighborRow' => null,
            'databaseRow' =>
                [
                    'uid' => 8,
                    'pid' => 12,
                    'tstamp' => 1745945983,
                    'crdate' => 1745945983,
                    'deleted' => 0,
                    'hidden' => 0,
                    'title' => 'TCCD / Anna A. Attendee,    ',
                    'user' =>
                        [
                            0 =>
                                [
                                    'table' => 'fe_users',
                                    'uid' => 1,
                                    'title' => 'attendee',
                                    'row' =>
                                        [
                                            'uid' => 1,
                                            'pid' => 4,
                                            'tstamp' => 1670000836,
                                            'crdate' => 1627917285,
                                            'cruser_id' => 1,
                                            'deleted' => 0,
                                            'disable' => 0,
                                            'starttime' => 0,
                                            'endtime' => 0,
                                            'description' => '',
                                            'tx_extbase_type' => '0',
                                            'username' => 'attendee',
                                            'password' => '$argon2i$v=19$m=65536,t=16,p=1$ODBXYmZrYkQ2akMwa1lHYg$iWz2uY5XHXAhjqG69uFSQDWvy/y1G931gk/s19sfBxo',
                                            'usergroup' => '1',
                                            'name' => 'Anna A. Attendee',
                                            'first_name' => 'Anna',
                                            'middle_name' => 'Ariana',
                                            'last_name' => 'Attendee',
                                            'address' => 'Bertha-von-Suttner-Platz 1',
                                            'telephone' => '+29 228 111111',
                                            'fax' => '',
                                            'email' => 'attendee@example.com',
                                            'uc' => 'a:1:{s:49:"tx_seminars_registration_editor_method_of_payment";s:1:"1";}',
                                            'title' => '',
                                            'zip' => '53111',
                                            'city' => 'Bonn',
                                            'country' => 'Germany',
                                            'www' => '',
                                            'company' => 'Anna Enterprises',
                                            'image' => '0',
                                            'TSconfig' => '',
                                            'lastlogin' => 1746027286,
                                            'is_online' => 1746032860,
                                            'felogin_redirectPid' => '',
                                            'felogin_forgotHash' => '',
                                            'tx_seminars_registration' => 0,
                                            'gender' => 0,
                                            'date_of_birth' => 0,
                                            'zone' => '',
                                            'status' => 0,
                                            'comments' => null,
                                            'full_salutation' => 'Hello Anna!',
                                            'privacy' => 0,
                                            'mfa' => null,
                                            'terms_acknowledged' => 0,
                                            'privacy_date_of_acceptance' => 0,
                                            'terms_date_of_acceptance' => 0,
                                            'vat_in' => '',
                                            'default_organizer' => 0,
                                            'available_topics' => null,
                                        ],
                                ],
                        ],
                    'seminar' =>
                        [
                            0 =>
                                [
                                    'table' => 'tx_seminars_seminars',
                                    'uid' => 5,
                                    'title' => 'TCCD-Termin',
                                    'row' =>
                                        [
                                            'uid' => 5,
                                            'pid' => 14,
                                            'tstamp' => 1742816277,
                                            'crdate' => 1628002667,
                                            'deleted' => 0,
                                            'hidden' => 0,
                                            'starttime' => 0,
                                            'endtime' => 0,
                                            'object_type' => 2,
                                            'title' => 'TCCD-Termin',
                                            'topic' => 1,
                                            'subtitle' => '',
                                            'categories' => 0,
                                            'teaser' => '',
                                            'description' => '',
                                            'event_type' => 0,
                                            'accreditation_number' => '',
                                            'credit_points' => 0,
                                            'begin_date' => 0,
                                            'end_date' => 0,
                                            'timeslots' => 0,
                                            'begin_date_registration' => 0,
                                            'deadline_registration' => 0,
                                            'deadline_early_bird' => 0,
                                            'deadline_unregistration' => 0,
                                            'expiry' => 0,
                                            'details_page' => '',
                                            'place' => 0,
                                            'room' => '',
                                            'lodgings' => 0,
                                            'foods' => 0,
                                            'speakers' => 0,
                                            'partners' => 0,
                                            'tutors' => 0,
                                            'leaders' => 0,
                                            'price_regular' => '0.00',
                                            'price_regular_early' => '0.00',
                                            'price_special' => '0.00',
                                            'price_special_early' => '0.00',
                                            'additional_information' => '',
                                            'payment_methods' => 0,
                                            'organizers' => 1,
                                            'organizing_partners' => 0,
                                            'event_takes_place_reminder_sent' => 0,
                                            'cancelation_deadline_reminder_sent' => 0,
                                            'needs_registration' => 1,
                                            'allows_multiple_registrations' => 0,
                                            'attendees_min' => 0,
                                            'attendees_max' => 0,
                                            'queue_size' => 0,
                                            'offline_attendees' => 0,
                                            'target_groups' => 0,
                                            'registrations' => 1,
                                            'cancelled' => 0,
                                            'owner_feuser' => 0,
                                            'vips' => 0,
                                            'checkboxes' => 2,
                                            'uses_terms_2' => 0,
                                            'notes' => '',
                                            'attached_files' => 3,
                                            'image' => 0,
                                            'requirements' => 0,
                                            'dependencies' => 0,
                                            'organizers_notified_about_minimum_reached' => 0,
                                            'mute_notification_emails' => 0,
                                            'automatic_confirmation_cancelation' => 0,
                                            'price_on_request' => 0,
                                            'date_of_last_registration_digest' => 0,
                                            'slug' => 'tccd/5',
                                            'event_format' => 0,
                                            'webinar_url' => null,
                                            'additional_email_text' => null,
                                            'download_start_date' => 0,
                                        ],
                                ],
                        ],
                    'registration_queue' =>
                        [0 => '0'],
                    'price' => 'Standardpreis 500,00 €',
                    'seats' => 1,
                    'registered_themselves' => 1,
                    'total_price' => '500.00',
                    'attendees_names' => '',
                    'additional_persons' => '',
                    'datepaid' => 0,
                    'method_of_payment' =>
                        [0 => '1'],
                    'company' => '',
                    'name' => '',
                    'gender' =>
                        [0 => '0'],
                    'address' => '',
                    'zip' => '',
                    'city' => '',
                    'country' => '',
                    'telephone' => '',
                    'email' => '',
                    'been_there' => 0,
                    'interests' => '',
                    'expectations' => '',
                    'background_knowledge' => '',
                    'accommodation' => '',
                    'lodgings' =>
                        [],
                    'food' => '',
                    'foods' =>
                        [],
                    'known_from' => '',
                    'notes' => '',
                    'kids' => 0,
                    'checkboxes' =>
                        [],
                    'separate_billing_address' => 0,
                    'price_code' =>
                        [0 => 'price_regular'],
                    'attendance_mode' =>
                        [0 => '0'],
                    'order_reference' => '',
                ],
            'effectivePid' => 12,
            'rootline' =>
                [
                    4 =>
                        [
                            'pid' => 5,
                            'uid' => 12,
                            'title' => 'Registrations',
                            'doktype' => 254,
                            'slug' => '/registrations',
                            'tsconfig_includes' => null,
                            'TSconfig' => null,
                            'is_siteroot' => 0,
                            't3ver_oid' => 0,
                            't3ver_wsid' => 0,
                            't3ver_state' => 0,
                            't3ver_stage' => 0,
                            'backend_layout_next_level' => '',
                            'hidden' => 0,
                            'starttime' => 0,
                            'endtime' => 0,
                            'fe_group' => '0',
                            'nav_hide' => 0,
                            'content_from_pid' => 0,
                            'module' => '',
                            'extendToSubpages' => 0,
                        ],
                    3 =>
                        [
                            'pid' => 2,
                            'uid' => 5,
                            'title' => 'Event data',
                            'doktype' => 254,
                            'slug' => '/seminars-1',
                            'tsconfig_includes' => null,
                            'TSconfig' => null,
                            'is_siteroot' => 0,
                            't3ver_oid' => 0,
                            't3ver_wsid' => 0,
                            't3ver_state' => 0,
                            't3ver_stage' => 0,
                            'backend_layout_next_level' => '',
                            'hidden' => 0,
                            'starttime' => 0,
                            'endtime' => 0,
                            'fe_group' => '0',
                            'nav_hide' => 0,
                            'content_from_pid' => 0,
                            'module' => '',
                            'extendToSubpages' => 0,
                        ],
                    2 =>
                        [
                            'pid' => 1,
                            'uid' => 2,
                            'title' => 'Data',
                            'doktype' => 254,
                            'slug' => '/1',
                            'tsconfig_includes' => null,
                            'TSconfig' => null,
                            'is_siteroot' => 0,
                            't3ver_oid' => 0,
                            't3ver_wsid' => 0,
                            't3ver_state' => 0,
                            't3ver_stage' => 0,
                            'backend_layout_next_level' => '',
                            'hidden' => 0,
                            'starttime' => 0,
                            'endtime' => 0,
                            'fe_group' => '0',
                            'nav_hide' => 0,
                            'content_from_pid' => 0,
                            'module' => '',
                            'extendToSubpages' => 0,
                        ],
                    1 =>
                        [
                            'pid' => 0,
                            'uid' => 1,
                            'title' => 'Home',
                            'doktype' => 1,
                            'slug' => '/',
                            'tsconfig_includes' => null,
                            'TSconfig' => null,
                            'is_siteroot' => 0,
                            't3ver_oid' => 0,
                            't3ver_wsid' => 0,
                            't3ver_state' => 0,
                            't3ver_stage' => 0,
                            'backend_layout_next_level' => '',
                            'hidden' => 0,
                            'starttime' => 0,
                            'endtime' => 0,
                            'fe_group' => '0',
                            'nav_hide' => 0,
                            'content_from_pid' => 0,
                            'module' => '',
                            'extendToSubpages' => 0,
                        ],
                    0 =>
                        [
                            'uid' => 0,
                            'pid' => null,
                            'title' => null,
                            'doktype' => null,
                            'slug' => null,
                            'tsconfig_includes' => null,
                            'TSconfig' => null,
                            'is_siteroot' => null,
                            't3ver_oid' => null,
                            't3ver_wsid' => null,
                            't3ver_state' => null,
                            't3ver_stage' => null,
                            'backend_layout_next_level' => null,
                            'hidden' => null,
                            'starttime' => null,
                            'endtime' => null,
                            'fe_group' => null,
                            'nav_hide' => null,
                            'content_from_pid' => null,
                            'module' => null,
                            'extendToSubpages' => null,
                        ],
                ],
            'userPermissionOnPage' => 31,
            'userTsConfig' =>
                [
                    'options.' =>
                        [
                            'enableBookmarks' => '1',
                            'file_list.' =>
                                [
                                    'enableDisplayThumbnails' => 'selectable',
                                    'enableClipBoard' => 'selectable',
                                    'thumbnail.' =>
                                        ['width' => '64', 'height' => '64'],
                                ],
                            'pageTree.' =>
                                [
                                    'doktypesToShowInNewPageDragArea' => '1,6,4,7,3,254,255,199',
                                    'showPageIdWithTitle' => '1',
                                ],
                            'contextMenu.' =>
                                [
                                    'table.' =>
                                        [
                                            'pages.' =>
                                                [
                                                    'disableItems' => '',
                                                    'tree.' =>
                                                        ['disableItems' => ''],
                                                ],
                                            'sys_file.' =>
                                                [
                                                    'disableItems' => '',
                                                    'tree.' =>
                                                        ['disableItems' => ''],
                                                ],
                                            'sys_filemounts.' =>
                                                [
                                                    'disableItems' => '',
                                                    'tree.' =>
                                                        ['disableItems' => ''],
                                                ],
                                        ],
                                ],
                            'saveDocView' => '1',
                            'saveDocNew' => '1',
                            'saveDocNew.' =>
                                ['pages' => '0', 'sys_file' => '0', 'sys_file_metadata' => '0'],
                            'disableDelete.' =>
                                ['sys_file' => '1'],
                        ],
                    'admPanel.' =>
                        [
                            'enable.' =>
                                ['all' => '1'],
                        ],
                    'TCAdefaults.' =>
                        [
                            'sys_note.' =>
                                ['author' => '', 'email' => ''],
                        ],
                ],
            'pageTsConfig' =>
                [
                    'mod.' =>
                        [
                            'web_list.' =>
                                [
                                    'enableClipBoard' => 'selectable',
                                    'tableDisplayOrder.' =>
                                        [
                                            'be_users.' =>
                                                ['after' => 'be_groups'],
                                            'sys_filemounts.' =>
                                                ['after' => 'be_users'],
                                            'sys_file_storage.' =>
                                                ['after' => 'sys_filemounts'],
                                            'sys_language.' =>
                                                ['after' => 'sys_file_storage'],
                                            'fe_users.' =>
                                                ['after' => 'fe_groups', 'before' => 'pages'],
                                            'sys_template.' =>
                                                ['after' => 'pages'],
                                            'backend_layout.' =>
                                                ['after' => 'pages'],
                                            'tt_content.' =>
                                                ['after' => 'pages,backend_layout,sys_template'],
                                            'sys_category.' =>
                                                ['after' => 'tt_content'],
                                        ],
                                    'searchLevel.' =>
                                        [
                                            'items.' =>
                                                [
                                                    -1 => 'EXT:core/Resources/Private/Language/locallang_core.xlf:labels.searchLevel.infinite',
                                                    0 => 'EXT:core/Resources/Private/Language/locallang_core.xlf:labels.searchLevel.0',
                                                    1 => 'EXT:core/Resources/Private/Language/locallang_core.xlf:labels.searchLevel.1',
                                                    2 => 'EXT:core/Resources/Private/Language/locallang_core.xlf:labels.searchLevel.2',
                                                    3 => 'EXT:core/Resources/Private/Language/locallang_core.xlf:labels.searchLevel.3',
                                                    4 => 'EXT:core/Resources/Private/Language/locallang_core.xlf:labels.searchLevel.4',
                                                ],
                                        ],
                                ],
                            'wizards.' =>
                                [
                                    'newRecord.' =>
                                        [
                                            'pages.' =>
                                                [
                                                    'show.' =>
                                                        [
                                                            'pageInside' => '1',
                                                            'pageAfter' => '1',
                                                            'pageSelectPosition' => '1',
                                                        ],
                                                ],
                                        ],
                                    'newContentElement.' =>
                                        [
                                            'wizardItems.' =>
                                                [
                                                    'common.' =>
                                                        [
                                                            'header' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common',
                                                            'elements.' =>
                                                                [
                                                                    'header.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-header',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_headerOnly_title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_headerOnly_description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'header'],
                                                                        ],
                                                                    'text.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-text',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_regularText_title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_regularText_description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'text'],
                                                                        ],
                                                                    'textpic.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-textpic',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_textImage_title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_textImage_description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'textpic'],
                                                                        ],
                                                                    'image.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-image',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_imagesOnly_title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_imagesOnly_description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'image'],
                                                                        ],
                                                                    'textmedia.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-textmedia',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_textMedia_title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_textMedia_description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'textmedia'],
                                                                        ],
                                                                    'bullets.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-bullets',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_bulletList_title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_bulletList_description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'bullets'],
                                                                        ],
                                                                    'table.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-table',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_table_title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:common_table_description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'table'],
                                                                        ],
                                                                    'uploads.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-special-uploads',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special_filelinks_title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special_filelinks_description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'uploads'],
                                                                        ],
                                                                ],
                                                            'show' => 'header,text,textpic,image,textmedia,bullets,table,uploads',
                                                        ],
                                                    'menu.' =>
                                                        [
                                                            'header' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu',
                                                            'elements.' =>
                                                                [
                                                                    'menu_abstract.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-menu-abstract',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_abstract.title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_abstract.description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'menu_abstract'],
                                                                        ],
                                                                    'menu_categorized_content.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-menu-categorized',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_categorized_content.title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_categorized_content.description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'menu_categorized_content'],
                                                                        ],
                                                                    'menu_categorized_pages.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-menu-categorized',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_categorized_pages.title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_categorized_pages.description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'menu_categorized_pages'],
                                                                        ],
                                                                    'menu_pages.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-menu-pages',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_pages.title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_pages.description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'menu_pages'],
                                                                        ],
                                                                    'menu_subpages.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-menu-pages',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_subpages.title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_subpages.description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'menu_subpages'],
                                                                        ],
                                                                    'menu_recently_updated.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-menu-recently-updated',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_recently_updated.title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_recently_updated.description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'menu_recently_updated'],
                                                                        ],
                                                                    'menu_related_pages.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-menu-related',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_related_pages.title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_related_pages.description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'menu_related_pages'],
                                                                        ],
                                                                    'menu_section.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-menu-section',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_section.title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_section.description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'menu_section'],
                                                                        ],
                                                                    'menu_section_pages.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-menu-section',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_section_pages.title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_section_pages.description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'menu_section_pages'],
                                                                        ],
                                                                    'menu_sitemap.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-menu-sitemap',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_sitemap.title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_sitemap.description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'menu_sitemap'],
                                                                        ],
                                                                    'menu_sitemap_pages.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-menu-sitemap-pages',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_sitemap_pages.title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:menu_sitemap_pages.description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'menu_sitemap_pages'],
                                                                        ],
                                                                ],
                                                            'show' => 'menu_abstract,menu_categorized_content,menu_categorized_pages,menu_pages,menu_subpages,menu_recently_updated,menu_related_pages,menu_section,menu_section_pages,menu_sitemap,menu_sitemap_pages',
                                                        ],
                                                    'special.' =>
                                                        [
                                                            'header' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special',
                                                            'elements.' =>
                                                                [
                                                                    'html.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-special-html',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special_plainHTML_title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special_plainHTML_description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'html'],
                                                                        ],
                                                                    'div.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-special-div',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special_divider_title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special_divider_description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'div'],
                                                                            'saveAndClose' => 'true',
                                                                        ],
                                                                    'shortcut.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-special-shortcut',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special_shortcut_title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:special_shortcut_description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'shortcut'],
                                                                        ],
                                                                ],
                                                            'show' => 'html,div,shortcut',
                                                        ],
                                                    'forms.' =>
                                                        [
                                                            'header' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:forms',
                                                            'show' => 'formframework,search,felogin_login',
                                                            'elements.' =>
                                                                [
                                                                    'formframework.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-form',
                                                                            'title' => 'LLL:EXT:form/Resources/Private/Language/locallang.xlf:form_new_wizard_title',
                                                                            'description' => 'LLL:EXT:form/Resources/Private/Language/locallang.xlf:form_new_wizard_description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'form_formframework'],
                                                                        ],
                                                                    'search.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-elements-searchform',
                                                                            'title' => 'LLL:EXT:indexed_search/Resources/Private/Language/locallang_db.xlf:plugin_title',
                                                                            'description' => 'LLL:EXT:indexed_search/Resources/Private/Language/locallang_db.xlf:plugin_description',
                                                                            'tt_content_defValues.' =>
                                                                                [
                                                                                    'CType' => 'list',
                                                                                    'list_type' => 'indexedsearch_pi2',
                                                                                ],
                                                                        ],
                                                                    'felogin_login.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-elements-login',
                                                                            'title' => 'LLL:EXT:felogin/Resources/Private/Language/Database.xlf:tt_content.CType.felogin_login.title',
                                                                            'description' => 'LLL:EXT:felogin/Resources/Private/Language/Database.xlf:tt_content.CType.felogin_login.description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'felogin_login'],
                                                                        ],
                                                                ],
                                                        ],
                                                    'plugins.' =>
                                                        [
                                                            'header' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:plugins',
                                                            'elements.' =>
                                                                [
                                                                    'general.' =>
                                                                        [
                                                                            'iconIdentifier' => 'content-plugin',
                                                                            'title' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:plugins_general_title',
                                                                            'description' => 'LLL:EXT:backend/Resources/Private/Language/locallang_db_new_content_el.xlf:plugins_general_description',
                                                                            'tt_content_defValues.' =>
                                                                                ['CType' => 'list'],
                                                                        ],
                                                                    'seminars.' =>
                                                                        [
                                                                            'iconIdentifier' => 'ext-seminars-wizard-icon',
                                                                            'title' => 'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:pi1_title',
                                                                            'description' => 'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:pi1_description',
                                                                            'tt_content_defValues.' =>
                                                                                [
                                                                                    'CType' => 'list',
                                                                                    'list_type' => 'seminars_pi1',
                                                                                ],
                                                                        ],
                                                                ],
                                                            'show' => '*',
                                                        ],
                                                ],
                                        ],
                                ],
                            'web_view.' =>
                                [
                                    'previewFrameWidths.' =>
                                        [
                                            '1920.' =>
                                                [
                                                    'label' => 'LLL:EXT:viewpage/Resources/Private/Language/locallang.xlf:computer',
                                                    'type' => 'desktop',
                                                    'width' => '1920',
                                                    'height' => '1080',
                                                ],
                                            '1366.' =>
                                                [
                                                    'label' => 'LLL:EXT:viewpage/Resources/Private/Language/locallang.xlf:computer',
                                                    'type' => 'desktop',
                                                    'width' => '1366',
                                                    'height' => '768',
                                                ],
                                            '1280.' =>
                                                [
                                                    'label' => 'LLL:EXT:viewpage/Resources/Private/Language/locallang.xlf:computer',
                                                    'type' => 'desktop',
                                                    'width' => '1280',
                                                    'height' => '1024',
                                                ],
                                            '1024.' =>
                                                [
                                                    'label' => 'LLL:EXT:viewpage/Resources/Private/Language/locallang.xlf:computer',
                                                    'type' => 'desktop',
                                                    'width' => '1024',
                                                    'height' => '768',
                                                ],
                                            'nexus7.' =>
                                                [
                                                    'label' => 'Nexus 7',
                                                    'type' => 'tablet',
                                                    'width' => '600',
                                                    'height' => '960',
                                                ],
                                            'nexus6p.' =>
                                                [
                                                    'label' => 'Nexus 6P',
                                                    'type' => 'mobile',
                                                    'width' => '411',
                                                    'height' => '731',
                                                ],
                                            'ipadpro.' =>
                                                [
                                                    'label' => 'iPad Pro',
                                                    'type' => 'tablet',
                                                    'width' => '1024',
                                                    'height' => '1366',
                                                ],
                                            'ipadair.' =>
                                                [
                                                    'label' => 'iPad Air',
                                                    'type' => 'tablet',
                                                    'width' => '768',
                                                    'height' => '1024',
                                                ],
                                            'iphone7plus.' =>
                                                [
                                                    'label' => 'iPhone 7 Plus',
                                                    'type' => 'mobile',
                                                    'width' => '414',
                                                    'height' => '736',
                                                ],
                                            'iphone6.' =>
                                                [
                                                    'label' => 'iPhone 6',
                                                    'type' => 'mobile',
                                                    'width' => '375',
                                                    'height' => '667',
                                                ],
                                            'iphone5.' =>
                                                [
                                                    'label' => 'iPhone 5',
                                                    'type' => 'mobile',
                                                    'width' => '320',
                                                    'height' => '568',
                                                ],
                                            'iphone4.' =>
                                                [
                                                    'label' => 'iPhone 4',
                                                    'type' => 'mobile',
                                                    'width' => '320',
                                                    'height' => '480',
                                                ],
                                        ],
                                ],
                            'web_info.' =>
                                [
                                    'fieldDefinitions.' =>
                                        [
                                            '0.' =>
                                                [
                                                    'label' => 'LLL:EXT:info/Resources/Private/Language/locallang_webinfo.xlf:pages_0',
                                                    'fields' => 'title,uid,slug,starttime,endtime,fe_group,target,url,shortcut,shortcut_mode',
                                                ],
                                            '1.' =>
                                                [
                                                    'label' => 'LLL:EXT:info/Resources/Private/Language/locallang_webinfo.xlf:pages_1',
                                                    'fields' => 'title,uid,###ALL_TABLES###',
                                                ],
                                            '2.' =>
                                                [
                                                    'label' => 'LLL:EXT:info/Resources/Private/Language/locallang_webinfo.xlf:pages_2',
                                                    'fields' => 'title,uid,lastUpdated,newUntil,cache_timeout,php_tree_stop,TSconfig,is_siteroot,fe_login_mode',
                                                ],
                                            '3.' =>
                                                [
                                                    'label' => 'LLL:EXT:info/Resources/Private/Language/locallang_webinfo.xlf:pages_layouts',
                                                    'fields' => 'title,uid,actual_backend_layout,backend_layout,backend_layout_next_level,layout',
                                                ],
                                            'seo.' =>
                                                [
                                                    'label' => 'LLL:EXT:seo/Resources/Private/Language/locallang_webinfo.xlf:seo',
                                                    'fields' => 'title,uid,slug,seo_title,description,no_index,no_follow,canonical_link,sitemap_changefreq,sitemap_priority',
                                                ],
                                            'social_media.' =>
                                                [
                                                    'label' => 'LLL:EXT:seo/Resources/Private/Language/locallang_webinfo.xlf:social_media',
                                                    'fields' => 'title,uid,og_title,og_description,twitter_title,twitter_description',
                                                ],
                                        ],
                                ],
                            'SHARED.' =>
                                ['colPos_list' => '0'],
                            'web_layout.' =>
                                ['hideRestrictedCols' => '1'],
                        ],
                    'TCEMAIN.' =>
                        [
                            'translateToMessage' => 'Translate to %s:',
                            'linkHandler.' =>
                                [
                                    'page.' =>
                                        [
                                            'handler' => PageLinkHandler::class,
                                            'label' => 'LLL:EXT:recordlist/Resources/Private/Language/locallang_browse_links.xlf:page',
                                        ],
                                    'file.' =>
                                        [
                                            'handler' => FileLinkHandler::class,
                                            'label' => 'LLL:EXT:recordlist/Resources/Private/Language/locallang_browse_links.xlf:file',
                                            'displayAfter' => 'page',
                                            'scanAfter' => 'page',
                                        ],
                                    'folder.' =>
                                        [
                                            'handler' => FolderLinkHandler::class,
                                            'label' => 'LLL:EXT:recordlist/Resources/Private/Language/locallang_browse_links.xlf:folder',
                                            'displayAfter' => 'page,file',
                                            'scanAfter' => 'page,file',
                                        ],
                                    'url.' =>
                                        [
                                            'handler' => UrlLinkHandler::class,
                                            'label' => 'LLL:EXT:recordlist/Resources/Private/Language/locallang_browse_links.xlf:extUrl',
                                            'displayAfter' => 'page,file,folder',
                                            'scanAfter' => 'telephone',
                                        ],
                                    'mail.' =>
                                        [
                                            'handler' => MailLinkHandler::class,
                                            'label' => 'LLL:EXT:recordlist/Resources/Private/Language/locallang_browse_links.xlf:email',
                                            'displayAfter' => 'page,file,folder,url',
                                            'scanBefore' => 'url',
                                        ],
                                    'telephone.' =>
                                        [
                                            'handler' => TelephoneLinkHandler::class,
                                            'label' => 'LLL:EXT:recordlist/Resources/Private/Language/locallang_browse_links.xlf:telephone',
                                            'displayAfter' => 'page,file,folder,url,mail',
                                            'scanBefore' => 'url',
                                        ],
                                ],
                        ],
                    'TCEFORM.' =>
                        [
                            'tt_content.' =>
                                [
                                    'imageorient.' =>
                                        [
                                            'types.' =>
                                                [
                                                    'image.' =>
                                                        ['removeItems' => '8,9,10,17,18,25,26'],
                                                ],
                                        ],
                                ],
                            'static_countries.' =>
                                [
                                    'cn_currency_uid.' =>
                                        [
                                            'suggest.' =>
                                                [
                                                    'default.' =>
                                                        ['renderFunc' => 'SJBR\\StaticInfoTables\\Hook\\Backend\\Form\\FormDataProvider\\SuggestLabelProcessor->translateLabel'],
                                                ],
                                        ],
                                ],
                        ],
                ],
            'systemLanguageRows' =>
                [
                    -1 =>
                        [
                            'uid' => -1,
                            'title' => 'Alle Sprachen',
                            'iso' => 'DEF',
                            'flagIconIdentifier' => 'flags-multiple',
                        ],
                    0 =>
                        ['uid' => 0, 'title' => 'Deutsch', 'iso' => 'DEF', 'flagIconIdentifier' => 'flags-de'],
                    1 =>
                        ['uid' => 1, 'title' => 'Englisch', 'iso' => 'en', 'flagIconIdentifier' => 'flags-en-us-gb'],
                ],
            'pageLanguageOverlayRows' =>
                [],
            'defaultLanguageRow' => null,
            'sourceLanguageRow' => null,
            'defaultLanguageDiffRow' => null,
            'additionalLanguageRows' =>
                [],
            'recordTypeValue' => '0',
            'processedTca' =>
                [
                    'ctrl' =>
                        [
                            'title' => 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances',
                            'label' => 'title',
                            'tstamp' => 'tstamp',
                            'crdate' => 'crdate',
                            'default_sortby' => 'ORDER BY crdate DESC',
                            'delete' => 'deleted',
                            'enablecolumns' =>
                                ['disabled' => 'hidden'],
                            'iconfile' => 'EXT:seminars/Resources/Public/Icons/Registration.gif',
                            'searchFields' => 'title, order_reference',
                        ],
                    'columns' =>
                        [
                            'title' =>
                                [
                                    'exclude' => 0,
                                    'label' => 'Titel',
                                    'config' =>
                                        ['type' => 'input', 'readOnly' => 1],
                                ],
                            'crdate' =>
                                [
                                    'exclude' => true,
                                    'label' => 'Anmeldedatum',
                                    'config' =>
                                        [
                                            'type' => 'input',
                                            'renderType' => 'inputDateTime',
                                            'size' => 8,
                                            'eval' => 'datetime, int',
                                            'default' => 0,
                                            'readOnly' => true,
                                        ],
                                ],
                            'uid' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Ticket-ID',
                                    'config' =>
                                        ['type' => 'none', 'readOnly' => 1],
                                ],
                            'seminar' =>
                                [
                                    'exclude' => false,
                                    'label' => 'Veranstaltung',
                                    'config' =>
                                        [
                                            'type' => 'group',
                                            'renderType' => 'eventDetails',
                                            'allowed' => 'tx_seminars_seminars',
                                            'default' => 0,
                                            'size' => 1,
                                            'minitems' => 1,
                                            'maxitems' => 1,
                                            'clipboardElements' =>
                                                [],
                                        ],
                                ],
                            'user' =>
                                [
                                    'exclude' => 0,
                                    'label' => 'Frontend-User',
                                    'config' =>
                                        [
                                            'type' => 'group',
                                            'allowed' => 'fe_users',
                                            'default' => 0,
                                            'size' => 1,
                                            'minitems' => 1,
                                            'maxitems' => 1,
                                            'clipboardElements' =>
                                                [],
                                        ],
                                ],
                            'been_there' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Hat teilgenommen',
                                    'config' =>
                                        [
                                            'type' => 'check',
                                            'items' =>
                                                [],
                                        ],
                                ],
                            'hidden' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Verbergen',
                                    'config' =>
                                        [
                                            'type' => 'check',
                                            'default' => 0,
                                            'items' =>
                                                [],
                                        ],
                                ],
                            'attendance_mode' =>
                                [
                                    'exclude' => true,
                                    'label' => 'Teilnahmemodus',
                                    'config' =>
                                        [
                                            'type' => 'select',
                                            'renderType' => 'selectSingle',
                                            'default' => 0,
                                            'items' =>
                                                [
                                                    0 =>
                                                        [0 => '', 1 => 0, 2 => null, 3 => null, 4 => null],
                                                    1 =>
                                                        [0 => 'vor Ort', 1 => 1, 2 => null, 3 => null, 4 => null],
                                                    2 =>
                                                        [0 => 'online', 1 => 2, 2 => null, 3 => null, 4 => null],
                                                    3 =>
                                                        [0 => 'hybrid', 1 => 3, 2 => null, 3 => null, 4 => null],
                                                ],
                                            'maxitems' => 99999,
                                        ],
                                ],
                            'registration_queue' =>
                                [
                                    'exclude' => true,
                                    'label' => 'Status',
                                    'config' =>
                                        [
                                            'type' => 'select',
                                            'renderType' => 'selectSingle',
                                            'default' => 0,
                                            'items' =>
                                                [
                                                    0 =>
                                                        [
                                                            0 => 'reguläre Anmeldung',
                                                            1 => 0,
                                                            2 => null,
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                    1 =>
                                                        [0 => 'Warteliste', 1 => 1, 2 => null, 3 => null, 4 => null],
                                                    2 =>
                                                        [
                                                            0 => 'unverbindliche Reservierung',
                                                            1 => 2,
                                                            2 => null,
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                ],
                                            'maxitems' => 99999,
                                        ],
                                ],
                            'seats' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Anzahl Plätze',
                                    'config' =>
                                        [
                                            'type' => 'input',
                                            'size' => 3,
                                            'max' => 3,
                                            'eval' => 'int',
                                            'range' =>
                                                ['upper' => 999, 'lower' => 0],
                                            'default' => 1,
                                        ],
                                ],
                            'registered_themselves' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Hat sich (auch) selbst angemeldet',
                                    'config' =>
                                        [
                                            'type' => 'check',
                                            'default' => 1,
                                            'items' =>
                                                [],
                                        ],
                                ],
                            'price' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'zu bezahlender Preis',
                                    'config' =>
                                        ['type' => 'input', 'size' => 30, 'eval' => 'trim'],
                                ],
                            'price_code' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Preiscode',
                                    'config' =>
                                        [
                                            'type' => 'select',
                                            'renderType' => 'selectSingle',
                                            'items' =>
                                                [
                                                    0 =>
                                                        [0 => '', 1 => '', 2 => null, 3 => null, 4 => null],
                                                    1 =>
                                                        [
                                                            0 => 'Standardpreis',
                                                            1 => 'price_regular',
                                                            2 => null,
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                    2 =>
                                                        [
                                                            0 => 'Spezialpreis',
                                                            1 => 'price_special',
                                                            2 => null,
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                    3 =>
                                                        [
                                                            0 => 'Frühbucherpreis',
                                                            1 => 'price_regular_early',
                                                            2 => null,
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                    4 =>
                                                        [
                                                            0 => 'Frühbucher-Spezialpreis',
                                                            1 => 'price_special_early',
                                                            2 => null,
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                ],
                                            'maxitems' => 99999,
                                        ],
                                ],
                            'total_price' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Gesamtpreis dieser Buchung',
                                    'config' =>
                                        [
                                            'type' => 'input',
                                            'size' => 10,
                                            'max' => 10,
                                            'eval' => 'double2',
                                            'range' =>
                                                ['upper' => '999999.99', 'lower' => '0.00'],
                                            'default' => '0.00',
                                        ],
                                ],
                            'attendees_names' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Zusätzlich angemeldete Personen',
                                    'config' =>
                                        ['type' => 'text', 'cols' => 30, 'rows' => 5],
                                ],
                            'additional_persons' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Zusätzlich angemeldete Personen',
                                    'config' =>
                                        [
                                            'type' => 'inline',
                                            'foreign_table' => 'fe_users',
                                            'foreign_field' => 'tx_seminars_registration',
                                            'foreign_default_sortby' => 'name',
                                            'maxitems' => 999,
                                            'appearance' =>
                                                [
                                                    'levelLinksPosition' => 'bottom',
                                                    'expandSingle' => 1,
                                                    'showPossibleLocalizationRecords' => false,
                                                    'enabledControls' =>
                                                        [
                                                            'info' => true,
                                                            'new' => true,
                                                            'dragdrop' => true,
                                                            'sort' => true,
                                                            'hide' => true,
                                                            'delete' => true,
                                                            'localize' => true,
                                                        ],
                                                ],
                                            'minitems' => 0,
                                        ],
                                    'children' =>
                                        [],
                                ],
                            'kids' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Anzahl Kinder',
                                    'config' =>
                                        [
                                            'type' => 'input',
                                            'size' => 3,
                                            'max' => 3,
                                            'eval' => 'int',
                                            'range' =>
                                                ['upper' => 999, 'lower' => 0],
                                            'default' => 0,
                                        ],
                                ],
                            'foods' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Gewählte Verpflegungsmöglichkeiten',
                                    'config' =>
                                        [
                                            'type' => 'select',
                                            'renderType' => 'selectMultipleSideBySide',
                                            'foreign_table' => 'tx_seminars_foods',
                                            'foreign_table_where' => 'ORDER BY title',
                                            'size' => 10,
                                            'minitems' => 0,
                                            'maxitems' => 999,
                                            'MM' => 'tx_seminars_attendances_foods_mm',
                                            'items' =>
                                                [
                                                    0 =>
                                                        [
                                                            0 => 'glutenfrei',
                                                            1 => 4,
                                                            2 => 'tcarecords-tx_seminars_foods-default',
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                    1 =>
                                                        [
                                                            0 => 'halal',
                                                            1 => 5,
                                                            2 => 'tcarecords-tx_seminars_foods-default',
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                    2 =>
                                                        [
                                                            0 => 'koscher',
                                                            1 => 6,
                                                            2 => 'tcarecords-tx_seminars_foods-default',
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                    3 =>
                                                        [
                                                            0 => 'low-carb',
                                                            1 => 2,
                                                            2 => 'tcarecords-tx_seminars_foods-default',
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                    4 =>
                                                        [
                                                            0 => 'vegan',
                                                            1 => 3,
                                                            2 => 'tcarecords-tx_seminars_foods-default',
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                    5 =>
                                                        [
                                                            0 => 'vegetarisch',
                                                            1 => 1,
                                                            2 => 'tcarecords-tx_seminars_foods-default',
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                ],
                                        ],
                                ],
                            'food' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Welche Kost bevorzugen Sie (Normalkost, vegetarisch oder eine spezielle Diät)',
                                    'config' =>
                                        ['type' => 'text', 'cols' => 30, 'rows' => 5],
                                ],
                            'lodgings' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Gewählte Unterbringungsmöglichkeiten',
                                    'config' =>
                                        [
                                            'type' => 'select',
                                            'renderType' => 'selectMultipleSideBySide',
                                            'foreign_table' => 'tx_seminars_lodgings',
                                            'foreign_table_where' => 'ORDER BY title',
                                            'size' => 10,
                                            'minitems' => 0,
                                            'maxitems' => 999,
                                            'MM' => 'tx_seminars_attendances_lodgings_mm',
                                            'items' =>
                                                [
                                                    0 =>
                                                        [
                                                            0 => 'Doppelzimmer',
                                                            1 => 2,
                                                            2 => 'tcarecords-tx_seminars_lodgings-default',
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                    1 =>
                                                        [
                                                            0 => 'Einzelzimmer',
                                                            1 => 1,
                                                            2 => 'tcarecords-tx_seminars_lodgings-default',
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                    2 =>
                                                        [
                                                            0 => 'Ich organisiere meine Übernachtung selbst.',
                                                            1 => 4,
                                                            2 => 'tcarecords-tx_seminars_lodgings-default',
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                    3 =>
                                                        [
                                                            0 => 'Mehrbettzimmer',
                                                            1 => 3,
                                                            2 => 'tcarecords-tx_seminars_lodgings-default',
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                ],
                                        ],
                                ],
                            'accommodation' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Wie möchten Sie übernachten (wenn möglich im Einzelzimmer, Doppelzimmer, keine Übernachtung)',
                                    'config' =>
                                        ['type' => 'text', 'cols' => 30, 'rows' => 5],
                                ],
                            'checkboxes' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Gewählte Optionen',
                                    'config' =>
                                        [
                                            'type' => 'select',
                                            'renderType' => 'selectMultipleSideBySide',
                                            'foreign_table' => 'tx_seminars_checkboxes',
                                            'foreign_table_where' => 'ORDER BY title',
                                            'size' => 10,
                                            'minitems' => 0,
                                            'maxitems' => 999,
                                            'MM' => 'tx_seminars_attendances_checkboxes_mm',
                                            'items' =>
                                                [
                                                    0 =>
                                                        [
                                                            0 => 'Ich kenne schon jemanden aus der Gruppe.',
                                                            1 => 2,
                                                            2 => 'tcarecords-tx_seminars_checkboxes-default',
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                    1 =>
                                                        [
                                                            0 => 'Ich komme später an.',
                                                            1 => 1,
                                                            2 => 'tcarecords-tx_seminars_checkboxes-default',
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                ],
                                        ],
                                ],
                            'order_reference' =>
                                [
                                    'exclude' => true,
                                    'label' => 'Bestellzeichen',
                                    'config' =>
                                        ['type' => 'input', 'size' => 30, 'max' => 255, 'eval' => 'trim'],
                                ],
                            'interests' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Spezielle Interessen',
                                    'config' =>
                                        ['type' => 'text', 'cols' => 30, 'rows' => 5],
                                ],
                            'expectations' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Erwartungen an die Veranstaltung',
                                    'config' =>
                                        ['type' => 'text', 'cols' => 30, 'rows' => 5],
                                ],
                            'background_knowledge' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Vorkenntnisse',
                                    'config' =>
                                        ['type' => 'text', 'cols' => 30, 'rows' => 5],
                                ],
                            'known_from' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Wie haben Sie von dieser Veranstaltung erfahren?',
                                    'config' =>
                                        ['type' => 'text', 'cols' => 30, 'rows' => 5],
                                ],
                            'notes' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Weitere Mitteilungen',
                                    'config' =>
                                        ['type' => 'text', 'cols' => 30, 'rows' => 5],
                                ],
                            'datepaid' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Datum der Bezahlung',
                                    'config' =>
                                        [
                                            'type' => 'input',
                                            'renderType' => 'inputDateTime',
                                            'size' => 8,
                                            'eval' => 'date, int',
                                            'default' => 0,
                                        ],
                                ],
                            'method_of_payment' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Zahlungsart',
                                    'config' =>
                                        [
                                            'type' => 'select',
                                            'renderType' => 'selectSingle',
                                            'foreign_table' => 'tx_seminars_payment_methods',
                                            'foreign_table_where' => 'ORDER BY title',
                                            'default' => 0,
                                            'size' => 1,
                                            'minitems' => 0,
                                            'maxitems' => 1,
                                            'items' =>
                                                [
                                                    0 =>
                                                        [0 => '', 1 => '0', 2 => null, 3 => null, 4 => null],
                                                    1 =>
                                                        [
                                                            0 => 'bar vor Ort',
                                                            1 => 2,
                                                            2 => 'tcarecords-tx_seminars_payment_methods-default',
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                    2 =>
                                                        [
                                                            0 => 'Rechnung',
                                                            1 => 1,
                                                            2 => 'tcarecords-tx_seminars_payment_methods-default',
                                                            3 => null,
                                                            4 => null,
                                                        ],
                                                ],
                                        ],
                                ],
                            'separate_billing_address' =>
                                [
                                    'exclude' => 1,
                                    'label' => 'Abweichende Rechnungsadresse',
                                    'onChange' => 'reload',
                                    'config' =>
                                        [
                                            'type' => 'check',
                                            'items' =>
                                                [],
                                        ],
                                ],
                        ],
                    'types' =>
                        [
                            0 =>
                                ['showitem' => '--div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.divLabelOverview, title, crdate, uid, seminar, user, been_there, hidden, --div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.divLabelBookingInformation, registration_queue, attendance_mode, registered_themselves, seats, price, price_code, total_price, attendees_names, additional_persons, kids, foods, food, lodgings, accommodation, checkboxes, order_reference, --div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.divLabelRegistrationComments, interests, expectations, background_knowledge, known_from, notes, --div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.divLabelPaymentInformation, datepaid, method_of_payment, --div--;LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tx_seminars_attendances.divLabelBillingAddress, separate_billing_address, company, gender, name, address, zip, city, country, telephone, email'],
                        ],
                ],
            'columnsToProcess' =>
                [
                    0 => 'title',
                    1 => 'title',
                    2 => 'crdate',
                    3 => 'uid',
                    4 => 'seminar',
                    5 => 'user',
                    6 => 'been_there',
                    7 => 'hidden',
                    8 => 'registration_queue',
                    9 => 'attendance_mode',
                    10 => 'registered_themselves',
                    11 => 'seats',
                    12 => 'price',
                    13 => 'price_code',
                    14 => 'total_price',
                    15 => 'attendees_names',
                    16 => 'additional_persons',
                    17 => 'kids',
                    18 => 'foods',
                    19 => 'food',
                    20 => 'lodgings',
                    21 => 'accommodation',
                    22 => 'checkboxes',
                    23 => 'order_reference',
                    24 => 'interests',
                    25 => 'expectations',
                    26 => 'background_knowledge',
                    27 => 'known_from',
                    28 => 'notes',
                    29 => 'datepaid',
                    30 => 'method_of_payment',
                    31 => 'separate_billing_address',
                    32 => 'company',
                    33 => 'gender',
                    34 => 'name',
                    35 => 'address',
                    36 => 'zip',
                    37 => 'city',
                    38 => 'country',
                    39 => 'telephone',
                    40 => 'email',
                ],
            'disabledWizards' => false,
            'flexParentDatabaseRow' =>
                [],
            'flexSectionContainerPreparation' =>
                [],
            'selectTreeCompileItems' => false,
            'inlineExpandCollapseStateArray' =>
                [],
            'inlineFirstPid' => 12,
            'inlineParentConfig' =>
                [],
            'isInlineChild' => false,
            'isInlineChildExpanded' => false,
            'isInlineAjaxOpeningContext' => false,
            'inlineParentUid' => '',
            'inlineParentTableName' => '',
            'inlineParentFieldName' => '',
            'inlineTopMostParentUid' => '',
            'inlineTopMostParentTableName' => '',
            'inlineTopMostParentFieldName' => '',
            'isOnSymmetricSide' => false,
            'inlineChildChildUid' => null,
            'isInlineDefaultLanguageRecordInLocalizedParentContext' => false,
            'inlineResolveExistingChildren' => true,
            'inlineCompileExistingChildren' => true,
            'elementBaseName' => '[tx_seminars_attendances][8][seminar]',
            'tabAndInlineStack' =>
                [
                    0 =>
                        [0 => 'tab', 1 => 'DTM-21f1fe701e6ea1afcdfc0c4da70d6609-1'],
                ],
            'inlineData' =>
                [],
            'inlineStructure' =>
                [],
            'overrideValues' =>
                [],
            'defaultValues' =>
                [],
            'renderData' =>
                [],
            'customData' =>
                [],
            'renderType' => 'eventDetails',
            'fieldsArray' =>
                [
                    0 => 'title;;',
                    1 => 'crdate;;',
                    2 => 'uid;;',
                    3 => 'seminar;;',
                    4 => 'user;;',
                    5 => 'been_there;;',
                    6 => 'hidden;;',
                ],
            'fieldName' => 'seminar',
            'parameterArray' =>
                [
                    'fieldConf' =>
                        [
                            'exclude' => false,
                            'label' => 'Veranstaltung',
                            'config' =>
                                [
                                    'type' => 'group',
                                    'renderType' => 'eventDetails',
                                    'allowed' => 'tx_seminars_seminars',
                                    'default' => 0,
                                    'size' => 1,
                                    'minitems' => 1,
                                    'maxitems' => 1,
                                    'clipboardElements' =>
                                        [],
                                ],
                        ],
                    'fieldTSConfig' =>
                        [],
                    'itemFormElName' => 'data[tx_seminars_attendances][8][seminar]',
                    'itemFormElID' => 'data_tx_seminars_attendances_8_seminar',
                    'itemFormElValue' =>
                        [
                            0 =>
                                [
                                    'table' => 'tx_seminars_seminars',
                                    'uid' => 5,
                                    'title' => 'TCCD-Termin',
                                    'row' =>
                                        [
                                            'uid' => 5,
                                            'pid' => 14,
                                            'tstamp' => 1742816277,
                                            'crdate' => 1628002667,
                                            'deleted' => 0,
                                            'hidden' => 0,
                                            'starttime' => 0,
                                            'endtime' => 0,
                                            'object_type' => 2,
                                            'title' => 'TCCD-Termin',
                                            'topic' => 1,
                                            'subtitle' => '',
                                            'categories' => 0,
                                            'teaser' => '',
                                            'description' => '',
                                            'event_type' => 0,
                                            'accreditation_number' => '',
                                            'credit_points' => 0,
                                            'begin_date' => 0,
                                            'end_date' => 0,
                                            'timeslots' => 0,
                                            'begin_date_registration' => 0,
                                            'deadline_registration' => 0,
                                            'deadline_early_bird' => 0,
                                            'deadline_unregistration' => 0,
                                            'expiry' => 0,
                                            'details_page' => '',
                                            'place' => 0,
                                            'room' => '',
                                            'lodgings' => 0,
                                            'foods' => 0,
                                            'speakers' => 0,
                                            'partners' => 0,
                                            'tutors' => 0,
                                            'leaders' => 0,
                                            'price_regular' => '0.00',
                                            'price_regular_early' => '0.00',
                                            'price_special' => '0.00',
                                            'price_special_early' => '0.00',
                                            'additional_information' => '',
                                            'payment_methods' => 0,
                                            'organizers' => 1,
                                            'organizing_partners' => 0,
                                            'event_takes_place_reminder_sent' => 0,
                                            'cancelation_deadline_reminder_sent' => 0,
                                            'needs_registration' => 1,
                                            'allows_multiple_registrations' => 0,
                                            'attendees_min' => 0,
                                            'attendees_max' => 0,
                                            'queue_size' => 0,
                                            'offline_attendees' => 0,
                                            'target_groups' => 0,
                                            'registrations' => 1,
                                            'cancelled' => 0,
                                            'owner_feuser' => 0,
                                            'vips' => 0,
                                            'checkboxes' => 2,
                                            'uses_terms_2' => 0,
                                            'notes' => '',
                                            'attached_files' => 3,
                                            'image' => 0,
                                            'requirements' => 0,
                                            'dependencies' => 0,
                                            'organizers_notified_about_minimum_reached' => 0,
                                            'mute_notification_emails' => 0,
                                            'automatic_confirmation_cancelation' => 0,
                                            'price_on_request' => 0,
                                            'date_of_last_registration_digest' => 0,
                                            'slug' => 'tccd/5',
                                            'event_format' => 0,
                                            'webinar_url' => null,
                                            'additional_email_text' => null,
                                            'download_start_date' => 0,
                                        ],
                                ],
                        ],
                    'fieldChangeFunc' => [],
                ],
        ];
        ArrayUtility::mergeRecursiveWithOverrule($data, $additionalData);
        $subject = new EventDetailsElement(new NodeFactory(), $data);

        $subject->render();

        return $subject->render()['html'] ?? '';
    }

    /**
     * @test
     */
    public function renderForNonRegistrationTableThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('EventDetailsElement can only be used for the "tx_seminars_attendances" table.');
        $this->expectExceptionCode(1752769757);

        $this->renderWithData(['tableName' => 'tx_seminars_seminars']);
    }

    /**
     * @test
     */
    public function renderForNonSeminarFieldThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('EventDetailsElement can only be used for the "seminar" field.');
        $this->expectExceptionCode(1752769855);

        $this->renderWithData(['fieldName' => 'title']);
    }

    /**
     * @test
     */
    public function renderRendersHiddenInputOfOriginalGroupSelector(): void
    {
        $result = $this->renderWithData();

        self::assertStringContainsString(
            '<input type="hidden" name="data[tx_seminars_attendances][8][seminar]"',
            $result
        );
    }

    /**
     * @test
     */
    public function renderForEventWithDateRendersDateOfEvent(): void
    {
        $timestamp = 1752771356;
        $additionalData = [
            'databaseRow' => [
                'seminar' => [
                    0 => [
                        'row' => [
                            'begin_date' => $timestamp,
                        ],
                    ],
                ],
            ],
        ];
        $result = $this->renderWithData($additionalData);

        $expectedDate = \date($this->getDateFormat(), $timestamp);
        self::assertStringContainsString($expectedDate, $result);
    }

    /**
     * @test
     */
    public function renderForEventWithoutDateDoesNotRenderZeroDate(): void
    {
        $additionalData = [
            'databaseRow' => [
                'seminar' => [
                    0 => [
                        'row' => [
                            'begin_date' => 0,
                        ],
                    ],
                ],
            ],
        ];
        $result = $this->renderWithData($additionalData);

        $expectedDate = \date($this->getDateFormat(), 0);
        self::assertStringNotContainsString($expectedDate, $result);
    }

    /**
     * @test
     */
    public function renderRendersUidOfEvent(): void
    {
        $uid = 145;
        $additionalData = [
            'databaseRow' => [
                'seminar' => [
                    0 => [
                        'row' => [
                            'uid' => $uid,
                        ],
                    ],
                ],
            ],
        ];
        $result = $this->renderWithData($additionalData);

        self::assertStringContainsString('[' . $uid . ']', $result);
    }

    /**
     * @test
     */
    public function renderRendersTitleOfEvent(): void
    {
        $title = 'Better unit testing with TYPO3';
        $additionalData = [
            'databaseRow' => [
                'seminar' => [
                    0 => [
                        'row' => [
                            'title' => $title,
                        ],
                    ],
                ],
            ],
        ];
        $result = $this->renderWithData($additionalData);

        self::assertStringContainsString($title, $result);
    }

    /**
     * @test
     */
    public function renderEncodesTitleOfEvent(): void
    {
        $title = 'Testing & quality assurance';
        $additionalData = [
            'databaseRow' => [
                'seminar' => [
                    0 => [
                        'row' => [
                            'title' => $title,
                        ],
                    ],
                ],
            ],
        ];
        $result = $this->renderWithData($additionalData);

        $expected = htmlspecialchars($title, ENT_QUOTES | ENT_HTML5);
        self::assertStringContainsString($expected, $result);
    }
}
