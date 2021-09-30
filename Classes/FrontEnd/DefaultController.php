<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\FrontEnd;

use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\FallbackConfiguration;
use OliverKlee\Oelib\Configuration\FlexformsConfiguration;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Oelib\Interfaces\Configuration;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Templating\TemplateHelper;
use OliverKlee\Seminars\Bag\AbstractBag;
use OliverKlee\Seminars\Bag\EventBag;
use OliverKlee\Seminars\Bag\RegistrationBag;
use OliverKlee\Seminars\BagBuilder\EventBagBuilder;
use OliverKlee\Seminars\BagBuilder\RegistrationBagBuilder;
use OliverKlee\Seminars\Configuration\CategoryListConfigurationCheck;
use OliverKlee\Seminars\Configuration\CountdownConfigurationCheck;
use OliverKlee\Seminars\Configuration\CsvExportConfigurationCheck;
use OliverKlee\Seminars\Configuration\EventEditorConfigurationCheck;
use OliverKlee\Seminars\Configuration\EventHeadlineConfigurationCheck;
use OliverKlee\Seminars\Configuration\ListViewConfigurationCheck;
use OliverKlee\Seminars\Configuration\MyEnteredEventsConfigurationCheck;
use OliverKlee\Seminars\Configuration\MyVipEventsConfigurationCheck;
use OliverKlee\Seminars\Configuration\RegistrationFormConfigurationCheck;
use OliverKlee\Seminars\Configuration\RegistrationListConfigurationCheck;
use OliverKlee\Seminars\Configuration\SharedConfigurationCheck;
use OliverKlee\Seminars\Configuration\SingleViewConfigurationCheck;
use OliverKlee\Seminars\Configuration\Traits\SharedPluginConfiguration;
use OliverKlee\Seminars\Csv\CsvDownloader;
use OliverKlee\Seminars\Hooks\HookProvider;
use OliverKlee\Seminars\Hooks\Interfaces\SeminarListView;
use OliverKlee\Seminars\Hooks\Interfaces\SeminarRegistrationForm;
use OliverKlee\Seminars\Hooks\Interfaces\SeminarSingleView;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Plugin "Seminar Manager".
 */
class DefaultController extends TemplateHelper
{
    use SharedPluginConfiguration;

    /**
     * @var string[]
     */
    private const VALID_SPEAKER_TYPES = ['speakers', 'partners', 'tutors', 'leaders'];

    /**
     * @var string prefix for request parameters
     */
    public $prefixId = 'tx_seminars_pi1';

    /**
     * faking $this->scriptRelPath so the locallang.xlf file is found
     *
     * @var string
     */
    public $scriptRelPath = 'Resources/Private/Language/locallang.xlf';

    /**
     * @var string the extension key
     */
    public $extKey = 'seminars';

    /**
     * @var \Tx_Seminars_Mapper_Event an event mapper used to retrieve event models
     */
    protected $eventMapper = null;

    /**
     * configuration in plugin.tx_seminars (not plugin.tx_seminars_pi1)
     *
     * @var \Tx_Seminars_Service_ConfigurationService
     */
    private $configurationService = null;

    /**
     * @var LegacyEvent|null the seminar which we want to list/show or
     *                          for which the user wants to register
     */
    private $seminar = null;

    /**
     * @var LegacyRegistration|null the registration which we want to
     *                               list/show in the "my events" view
     */
    private $registration = null;

    /** @var string the previous event's category (used for the list view) */
    private $previousCategory = '';

    /** @var string the previous event's date (used for the list view) */
    private $previousDate = '';

    /**
     * @var string[] field names (as keys) by which we can sort plus the corresponding SQL sort criteria (as value).
     *
     * We cannot use the database table name constants here because default
     * values for member variable don't allow for compound expression.
     */
    public $orderByList = [
        // The MIN gives us the first category if there are more than one.
        // The clause before the OR gets the events made up of topics (type=1)
        // and concrete dates (type=2).
        // After the OR we get the straight events.
        'category' => '(SELECT MIN(tx_seminars_categories.title)
            FROM tx_seminars_seminars_categories_mm, tx_seminars_categories,
                    tx_seminars_seminars s1, tx_seminars_seminars s2
            WHERE ( ( s1.uid=s2.topic
                        AND s1.object_type <> 2
                        AND s2.object_type=2
                        AND s2.uid=tx_seminars_seminars.uid
                ) OR ( s1.uid=s2.uid
                        AND s2.object_type=0
                        AND s1.uid=tx_seminars_seminars.uid
                        )
                )
                AND tx_seminars_seminars_categories_mm.uid_foreign=tx_seminars_categories.uid
                AND tx_seminars_seminars_categories_mm.uid_local=s1.uid)',
        // Sort by title.
        // Complete event records get the title directly.
        // Date records get it from their topic record.
        'title' => '(SELECT s1.title
            FROM tx_seminars_seminars s1, tx_seminars_seminars s2
            WHERE ((s1.uid=s2.topic AND s2.object_type=2) OR (s1.uid=s2.uid AND s1.object_type <> 2))
                AND s2.uid=tx_seminars_seminars.uid)',
        'subtitle' => '(SELECT s1.subtitle
            FROM tx_seminars_seminars s1, tx_seminars_seminars s2
            WHERE ((s1.uid=s2.topic AND s2.object_type=2) OR (s1.uid=s2.uid AND s1.object_type <> 2))
                AND s2.uid=tx_seminars_seminars.uid)',
        'uid' => 'tx_seminars_seminars.uid',
        'event_type' => '(SELECT s1.event_type
            FROM tx_seminars_seminars s1, tx_seminars_seminars s2
            WHERE ((s1.uid=s2.topic AND s2.object_type=2) OR (s1.uid=s2.uid AND s1.object_type <> 2))
                AND s2.uid=tx_seminars_seminars.uid)',
        'accreditation_number' => 'tx_seminars_seminars.accreditation_number',
        'credit_points' => '(SELECT s1.credit_points
            FROM tx_seminars_seminars s1, tx_seminars_seminars s2
            WHERE ((s1.uid=s2.topic AND s2.object_type=2) OR (s1.uid=s2.uid AND s1.object_type <> 2))
                AND s2.uid=tx_seminars_seminars.uid)',
        // This will sort by the speaker names or the alphabetically lowest
        // speaker name (if there is more than one speaker).
        'speakers' => '(SELECT MIN(tx_seminars_speakers.title)
            FROM tx_seminars_seminars_speakers_mm, tx_seminars_speakers
            WHERE tx_seminars_seminars_speakers_mm.uid_local=tx_seminars_seminars.uid
                AND tx_seminars_seminars_speakers_mm.uid_foreign=tx_seminars_speakers.uid)',
        'date' => 'tx_seminars_seminars.begin_date',
        // 86400 seconds are one day, so this calculates us just the time of day.
        'time' => 'tx_seminars_seminars.begin_date % 86400',
        // This will sort by the place names or the alphabetically lowest
        // place name (if there is more than one place).
        'place' => '(SELECT MIN(tx_seminars_sites.title)
            FROM tx_seminars_seminars_place_mm, tx_seminars_sites
            WHERE tx_seminars_seminars_place_mm.uid_local=tx_seminars_seminars.uid
                AND tx_seminars_seminars_place_mm.uid_foreign=tx_seminars_sites.uid)',
        'price_regular' => '(SELECT s1.price_regular
            FROM tx_seminars_seminars s1, tx_seminars_seminars s2
            WHERE ((s1.uid=s2.topic AND s2.object_type=2) OR (s1.uid=s2.uid AND s1.object_type <> 2))
                AND s2.uid=tx_seminars_seminars.uid)',
        'price_special' => '(SELECT s1.price_special
            FROM tx_seminars_seminars s1, tx_seminars_seminars s2
            WHERE ((s1.uid=s2.topic AND s2.object_type=2) OR (s1.uid=s2.uid AND s1.object_type <> 2))
                AND s2.uid=tx_seminars_seminars.uid)',
        'organizers' => '(SELECT MIN(tx_seminars_organizers.title)
            FROM tx_seminars_seminars_organizers_mm, tx_seminars_organizers
            WHERE tx_seminars_seminars_organizers_mm.uid_local=tx_seminars_seminars.uid
                AND tx_seminars_seminars_organizers_mm.uid_foreign=tx_seminars_organizers.uid)',
        'vacancies' => 'tx_seminars_seminars.attendees_max
                -(
                    (SELECT COUNT(*)
                    FROM tx_seminars_attendances
                    WHERE tx_seminars_attendances.seminar=tx_seminars_seminars.uid
                        AND tx_seminars_attendances.seats=0
                        AND tx_seminars_attendances.deleted=0)
                    +(SELECT SUM(tx_seminars_attendances.seats)
                    FROM tx_seminars_attendances
                    WHERE tx_seminars_attendances.seminar=tx_seminars_seminars.uid
                        AND  tx_seminars_attendances.seats <> 0
                        AND tx_seminars_attendances.deleted=0)
                )',
        // This will sort by the target groups titles or the alphabetically lowest
        // target group title (if there is more than one speaker).
        'target_groups' => '(SELECT MIN(tx_seminars_target_groups.title)
            FROM tx_seminars_seminars_target_groups_mm, tx_seminars_target_groups
            WHERE tx_seminars_seminars_target_groups_mm.uid_local=tx_seminars_seminars.uid
                AND tx_seminars_seminars_target_groups_mm.uid_foreign=tx_seminars_target_groups.uid)',
        'status_registration' => 'tx_seminars_attendances.registration_queue',
    ];

    /**
     * @var HookProvider|null
     */
    protected $listViewHookProvider = null;

    /**
     * @var HookProvider|null
     */
    protected $singleViewHookProvider = null;

    /**
     * @var HookProvider|null
     */
    protected $registrationFormHookProvider = null;

    /**
     * a link builder instance
     *
     * @var \Tx_Seminars_Service_SingleViewLinkBuilder
     */
    private $linkBuilder = null;

    /**
     * @var FrontendUserAuthentication
     */
    protected $feuser = null;

    /**
     * int
     */
    protected $showUid = 0;

    /**
     * @var string
     */
    protected $whatToDisplay = '';

    /**
     * Displays the seminar manager HTML.
     *
     * @param array $conf TypoScript configuration for the plugin
     *
     * @return string HTML for the plugin
     */
    public function main(string $unused, array $conf): string
    {
        $this->init($conf);
        $this->pi_initPIflexForm();

        $this->getTemplateCode();
        $this->setLabels();
        $this->createHelperObjects();

        // Sets the UID of a single event that is requested (either by the
        // configuration in the flexform or by a parameter in the URL).
        if ($this->hasConfValueInteger('showSingleEvent', 's_template_special')) {
            $this->showUid = $this->getConfValueInteger('showSingleEvent', 's_template_special');
        } else {
            $this->showUid = (int)$this->piVars['showUid'];
        }

        $this->whatToDisplay = $this->getConfValueString('what_to_display');
        switch ($this->whatToDisplay) {
            case 'single_view':
                $result = $this->createSingleView();
                break;
            case 'edit_event':
                $result = $this->createEventEditorHtml();
                break;
            case 'seminar_registration':
                $result = $this->createRegistrationPage();
                break;
            case 'list_vip_registrations':
                // The fallthrough is intended
                // because createRegistrationsListPage() will differentiate later.
            case 'list_registrations':
                /** @var \Tx_Seminars_FrontEnd_RegistrationsList $registrationsList */
                $registrationsList = GeneralUtility::makeInstance(
                    \Tx_Seminars_FrontEnd_RegistrationsList::class,
                    $this->conf,
                    $this->whatToDisplay,
                    (int)$this->piVars['seminar'],
                    $this->cObj
                );
                $result = $registrationsList->render();
                if ($this->isConfigurationCheckEnabled()) {
                    $configurationCheck = new RegistrationListConfigurationCheck(
                        $this->buildConfigurationWithFlexForms(),
                        'plugin.tx_seminars_pi1'
                    );
                    $configurationCheck->check();
                    $result .= \implode("\n", $configurationCheck->getWarningsAsHtml());
                }
                break;
            case 'countdown':
                /** @var \Tx_Seminars_FrontEnd_Countdown $countdown */
                $countdown = GeneralUtility::makeInstance(
                    \Tx_Seminars_FrontEnd_Countdown::class,
                    $this->conf,
                    $this->cObj
                );
                $countdown->injectEventMapper($this->eventMapper);
                $result = $countdown->render();
                if ($this->isConfigurationCheckEnabled()) {
                    $configurationCheck = new CountdownConfigurationCheck(
                        $this->buildConfigurationWithFlexForms(),
                        'plugin.tx_seminars_pi1'
                    );
                    $configurationCheck->check();
                    $result .= \implode("\n", $configurationCheck->getWarningsAsHtml());
                }
                break;
            case 'category_list':
                /** @var \Tx_Seminars_FrontEnd_CategoryList $categoryList */
                $categoryList = GeneralUtility::makeInstance(
                    \Tx_Seminars_FrontEnd_CategoryList::class,
                    $this->conf,
                    $this->cObj
                );
                $result = $categoryList->render();
                if ($this->isConfigurationCheckEnabled()) {
                    $configurationCheck = new CategoryListConfigurationCheck(
                        $this->buildConfigurationWithFlexForms(),
                        'plugin.tx_seminars_pi1'
                    );
                    $configurationCheck->check();
                    $result .= \implode("\n", $configurationCheck->getWarningsAsHtml());
                }
                break;
            case 'event_headline':
                /** @var \Tx_Seminars_FrontEnd_EventHeadline $eventHeadline */
                $eventHeadline = GeneralUtility::makeInstance(
                    \Tx_Seminars_FrontEnd_EventHeadline::class,
                    $this->conf,
                    $this->cObj
                );
                $eventHeadline->injectEventMapper($this->eventMapper);
                $result = $eventHeadline->render();
                if ($this->isConfigurationCheckEnabled()) {
                    $configurationCheck = new EventHeadlineConfigurationCheck(
                        $this->buildConfigurationWithFlexForms(),
                        'plugin.tx_seminars_pi1'
                    );
                    $configurationCheck->check();
                    $result .= \implode("\n", $configurationCheck->getWarningsAsHtml());
                }
                break;
            case 'my_vip_events':
                // The fallthrough is intended
                // because createListView() will differentiate later.
                // We still use the processEventEditorActions call in the next case.
            case 'my_entered_events':
                $this->processEventEditorActions();
            // The fallthrough is intended
            // because createListView() will differentiate later.
            // no break
            case 'topic_list':
                // The fallthrough is intended
                // because createListView() will differentiate later.
            case 'my_events':
                // The fallthrough is intended
                // because createListView() will differentiate later.
            case 'seminar_list':
                // The fallthrough is intended
                // because createListView() will differentiate later.
            case 'favorites_list':
                // The fallthrough is intended
                // because createListView() will differentiate later.
            default:
                $result = $this->createListView($this->whatToDisplay);
        }

        if ($this->isConfigurationCheckEnabled()) {
            $configuration = ConfigurationRegistry::get('plugin.tx_seminars');
            $configurationCheck = new SharedConfigurationCheck($configuration, 'plugin.tx_seminars');
            $configurationCheck->check();
            $result .= \implode("\n", $configurationCheck->getWarningsAsHtml());
        }

        return $this->pi_wrapInBaseClass($result);
    }

    ///////////////////////
    // General functions.
    ///////////////////////

    /**
     * Checks that we are properly initialized and that we have a config getter.
     *
     * @return bool TRUE if we are properly initialized, FALSE otherwise
     */
    public function isInitialized(): bool
    {
        return $this->isInitialized && is_object($this->configurationService);
    }

    protected function getListViewHookProvider(): HookProvider
    {
        if (!$this->listViewHookProvider instanceof HookProvider) {
            $this->listViewHookProvider = GeneralUtility::makeInstance(HookProvider::class, SeminarListView::class);
        }

        return $this->listViewHookProvider;
    }

    protected function getSingleViewHookProvider(): HookProvider
    {
        if ($this->singleViewHookProvider === null) {
            $this->singleViewHookProvider = GeneralUtility::makeInstance(HookProvider::class, SeminarSingleView::class);
        }

        return $this->singleViewHookProvider;
    }

    protected function getRegistrationFormHookProvider(): HookProvider
    {
        if ($this->registrationFormHookProvider === null) {
            $this->registrationFormHookProvider = GeneralUtility::makeInstance(
                HookProvider::class,
                SeminarRegistrationForm::class
            );
        }

        return $this->registrationFormHookProvider;
    }

    /**
     * Creates a seminar in $this->seminar.
     * If the seminar cannot be created, $this->seminar will be NULL, and
     * this function will return FALSE.
     *
     * @param bool $showHidden whether hidden records should be retrieved as well
     *
     * @return bool TRUE if the seminar UID is valid and the object has been created, FALSE otherwise
     */
    public function createSeminar(int $seminarUid, bool $showHidden = false): bool
    {
        if ($this->seminar !== null) {
            unset($this->seminar);
        }

        /** @var LegacyEvent|null $event */
        $event = LegacyEvent::fromUid($seminarUid, $showHidden);
        if ($event instanceof LegacyEvent) {
            $this->setSeminar($event);
            $result = $showHidden ? $this->canShowCurrentEvent() : true;
        } else {
            $this->setSeminar();
            $result = false;
        }

        return $result;
    }

    /**
     * Sets the current seminar for the list view.
     */
    protected function setSeminar(?LegacyEvent $event = null): void
    {
        $this->seminar = $event;
    }

    /**
     * Creates a registration in $this->registration from the database record
     * with the UID specified in the parameter $registrationUid.
     * If the registration cannot be created, $this->registration will be NULL,
     * and this function will return FALSE.
     *
     * @param int $uid a registration UID
     *
     * @return bool TRUE if the registration UID is valid and the object has been created, FALSE otherwise
     */
    public function createRegistration(int $uid): bool
    {
        $this->registration = LegacyRegistration::fromUid($uid);
        $exists = $this->registration instanceof LegacyRegistration;

        if ($exists) {
            $this->registration->setContentObject($this->cObj);
        }

        return $exists;
    }

    /**
     * Creates the config getter and the registration manager.
     */
    public function createHelperObjects(): void
    {
        if ($this->configurationService === null) {
            $this->configurationService = GeneralUtility::makeInstance(
                \Tx_Seminars_Service_ConfigurationService::class
            );
        }

        if ($this->eventMapper === null) {
            $this->eventMapper = GeneralUtility::makeInstance(\Tx_Seminars_Mapper_Event::class);
        }
    }

    public function getSeminar(): ?LegacyEvent
    {
        return $this->seminar;
    }

    /**
     * Returns the current registration.
     */
    public function getRegistration(): ?LegacyRegistration
    {
        return $this->registration;
    }

    public function getRegistrationManager(): \Tx_Seminars_Service_RegistrationManager
    {
        return \Tx_Seminars_Service_RegistrationManager::getInstance();
    }

    /**
     * This function is intended for testing purposes only.
     */
    public function getConfigurationService(): ?\Tx_Seminars_Service_ConfigurationService
    {
        return $this->configurationService;
    }

    /**
     * Creates the link to the list of registrations for the current seminar.
     * Returns an empty string if this link is not allowed.
     * For standard lists, a link is created if either the user is a VIP or is
     * registered for that seminar (with the link to the VIP list taking
     * precedence).
     *
     * @return string HTML for the link (may be an empty string)
     */
    protected function getRegistrationsListLink(): string
    {
        $result = '';
        $targetPageId = 0;

        if (
            $this->seminar->canViewRegistrationsList(
                $this->whatToDisplay,
                0,
                $this->getConfValueInteger('registrationsVipListPID'),
                $this->getConfValueInteger('defaultEventVipsFeGroupID', 's_template_special')
            )
        ) {
            // So a link to the VIP list is possible.
            $targetPageId = $this->getConfValueInteger('registrationsVipListPID');
        } elseif (
            $this->seminar->canViewRegistrationsList(
                $this->whatToDisplay,
                $this->getConfValueInteger('registrationsListPID')
            )
        ) {
            // No link to the VIP list ... so maybe to the list for the participants.
            $targetPageId = $this->getConfValueInteger('registrationsListPID');
        }

        if ($targetPageId) {
            $result = $this->cObj->getTypoLink(
                $this->translate('label_listRegistrationsLink'),
                (string)$targetPageId,
                ['tx_seminars_pi1[seminar]' => $this->seminar->getUid()]
            );
        }

        return $result;
    }

    /**
     * Returns a label wrapped in <a> tags. The link points to the login page
     * and contains a redirect parameter that points back to a certain page
     * (must be provided as a parameter to this function). The user will be
     * redirected to this page after a successful login.
     *
     * If an event uid is provided, the return parameter will contain a showUid
     * parameter with this UID.
     *
     * @param string $label the label to wrap into a link
     * @param int $pageId the PID of the page to redirect to after login (must not be empty)
     * @param int $eventId the UID of the event (may be empty)
     *
     * @return string the wrapped label
     */
    public function getLoginLink(string $label, int $pageId, int $eventId = 0): string
    {
        $linkConfiguration = ['parameter' => $pageId];

        if ($eventId) {
            $linkConfiguration['additionalParams'] = GeneralUtility::implodeArrayForUrl(
                'tx_seminars_pi1',
                ['seminar' => $eventId, 'action' => 'register'],
                '',
                false,
                true
            );
        }

        $redirectUrl = GeneralUtility::locationHeaderUrl(
            $this->cObj->typoLink_URL($linkConfiguration)
        );

        // XXX We need to do this workaround of manually encoding brackets in
        // the URL due to a bug in the TYPO3 core:
        // http://bugs.typo3.org/view.php?id=3808
        $redirectUrl = preg_replace(
            ['/\\[/', '/\\]/'],
            ['%5B', '%5D'],
            $redirectUrl
        );

        return $this->cObj->typoLink(
            $label,
            [
                'parameter' => $this->getConfValueInteger('loginPID'),
                'additionalParams' => GeneralUtility::implodeArrayForUrl(
                    '',
                    [
                        rawurlencode('tx_seminars_pi1[uid]') => $eventId,
                        'redirect_url' => $redirectUrl,
                    ]
                ),
            ]
        );
    }

    // Single view functions.

    /**
     * Displays detailed data for an event.
     *
     * Fields listed in $this->subpartsToHide get hidden (i.e., not displayed).
     */
    protected function createSingleView(): string
    {
        $this->hideSubparts($this->getConfValueString('hideFields', 's_template_special'), 'FIELD_WRAPPER');

        if ($this->showUid <= 0) {
            $this->setMarker('error_text', $this->translate('message_missingSeminarNumber'));
            $result = $this->getSubpart('ERROR_VIEW');
            HeaderProxyFactory::getInstance()->getHeaderProxy()->addHeader('Status: 404 Not Found');
        } elseif ($this->createSeminar($this->showUid, $this->isLoggedIn())) {
            $result = $this->createSingleViewForExistingEvent();
        } else {
            $this->setMarker('error_text', $this->translate('message_wrongSeminarNumber'));
            $result = $this->getSubpart('ERROR_VIEW');
            HeaderProxyFactory::getInstance()->getHeaderProxy()->addHeader('Status: 404 Not Found');
        }

        $this->setMarker(
            'backlink',
            $this->pi_linkTP($this->translate('label_back'), [], true, $this->getConfValueInteger('listPID'))
        );
        $result .= $this->getSubpart('BACK_VIEW');

        if ($this->isConfigurationCheckEnabled()) {
            $configurationCheck = new SingleViewConfigurationCheck(
                $this->buildConfigurationWithFlexForms(),
                'plugin.tx_seminars_pi1'
            );
            $configurationCheck->check();
            $result .= \implode("\n", $configurationCheck->getWarningsAsHtml());
        }

        return $result;
    }

    /**
     * Creates the single view for the event with the event in $this->seminar.
     *
     * @return string the rendered single view
     */
    protected function createSingleViewForExistingEvent(): string
    {
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);
        $event = $mapper->find($this->showUid);

        // This sets the title of the page for use in indexed search results:
        $this->getFrontEndController()->indexedDocTitle = $this->seminar->getTitle();

        $this->setEventTypeMarker();

        // This is for old templates that still have the removed marker.
        $this->setMarker('STYLE_SINGLEVIEWTITLE', '');

        if ($this->seminar->hasImage()) {
            $this->setMarker('SINGLE_VIEW_IMAGE', $this->createImageForSingleView());
        } else {
            $this->hideSubparts('image', 'FIELD_WRAPPER');
        }

        $this->setMarker('title', \htmlspecialchars($this->seminar->getTitle(), ENT_QUOTES | ENT_HTML5));
        $this->setMarker('uid', $this->seminar->getUid());

        $this->setSubtitleMarker();
        $this->setDescriptionMarker();

        $this->setAccreditationNumberMarker();
        $this->setCreditPointsMarker();

        $this->setCategoriesMarker();

        $this->setMarker('date', $this->seminar->getDate());
        $this->setMarker('time', $this->seminar->getTime());
        $this->setPlaceMarker();
        $this->setRoomMarker();

        $this->setTimeSlotsMarkers();

        $this->setExpiryMarker();

        $this->setGenderSpecificHeading('speakers');
        $this->setSpeakersMarker();
        $this->setGenderSpecificHeading('partners');
        $this->setPartnersMarker();
        $this->setGenderSpecificHeading('tutors');
        $this->setTutorsMarker();
        $this->setGenderSpecificHeading('leaders');
        $this->setLeadersMarker();

        $this->setLanguageMarker();

        $this->setSingleViewPriceMarkers();
        $this->setPaymentMethodsMarker();

        $this->setAdditionalInformationMarker();

        $this->setTargetGroupsMarkers();

        $this->setRequirementsMarker();
        $this->setDependenciesMarker();

        $this->setMarker('organizers', $this->getOrganizersMarkerContent());
        $this->setOrganizingPartnersMarker();

        $this->setOwnerDataMarker();

        $this->setAttachedFilesMarkers();

        $this->setVacanciesMarker();

        $this->setRegistrationDeadlineMarker();
        $this->setRegistrationMarker();
        $this->setListOfRegistrationMarker();

        $this->hideUnneededSubpartsForTopicRecords();

        $this->getSingleViewHookProvider()->executeHook('modifySingleView', $this);

        $result = $this->getSubpart('SINGLE_VIEW');

        // Caches $this->seminar because the list view will overwrite
        // $this->seminar.
        // TODO: This needs to be removed as soon as the list view is moved
        // to its own class.
        // @see https://bugs.oliverklee.com/show_bug.cgi?id=290
        $seminar = $this->seminar;
        if ($this->seminar->hasEndDate()) {
            $result .= $this->createEventsOnNextDayList();
        }
        $this->setSeminar($seminar);
        if ($this->seminar->isEventTopic() || $this->seminar->isEventDate()) {
            $result .= $this->createOtherDatesList();
        }

        return $result;
    }

    private function createImageForSingleView(): string
    {
        $imageConfiguration = [
            'altText' => '',
            'file' => \Tx_Seminars_FrontEnd_AbstractView::UPLOAD_PATH . $this->seminar->getImage(),
            'file.' => [
                'width' => $this->getConfValueInteger('seminarImageSingleViewWidth'),
                'height' => $this->getConfValueInteger('seminarImageSingleViewHeight'),
            ],
        ];

        return $this->cObj->cObjGetSingle('IMAGE', $imageConfiguration);
    }

    /**
     * Fills in the matching marker for the event type or hides the subpart if there is no event type.
     */
    private function setEventTypeMarker(): void
    {
        if (!$this->seminar->hasEventType()) {
            $this->hideSubparts('event_type', 'field_wrapper');
            return;
        }

        $this->setMarker('event_type', \htmlspecialchars($this->seminar->getEventType(), ENT_QUOTES | ENT_HTML5));
    }

    /**
     * Fills in the matching marker for the subtitle or hides the subpart if there is no subtitle.
     */
    private function setSubtitleMarker(): void
    {
        if (!$this->seminar->hasSubtitle()) {
            $this->hideSubparts('subtitle', 'field_wrapper');
            return;
        }

        $this->setMarker('subtitle', \htmlspecialchars($this->seminar->getSubtitle(), ENT_QUOTES | ENT_HTML5));
    }

    /**
     * Fills in the matching marker for the description or hides the subpart if there is no description.
     */
    private function setDescriptionMarker(): void
    {
        if (!$this->seminar->hasDescription()) {
            $this->hideSubparts('description', 'field_wrapper');
            return;
        }

        $this->setMarker(
            'description',
            $this->pi_RTEcssText($this->seminar->getDescription())
        );
    }

    /**
     * Fills in the matching marker for the accreditation number or hides the subpart if there is no accreditation number.
     */
    private function setAccreditationNumberMarker(): void
    {
        if (!$this->seminar->hasAccreditationNumber()) {
            $this->hideSubparts('accreditation_number', 'field_wrapper');
            return;
        }

        $this->setMarker(
            'accreditation_number',
            \htmlspecialchars($this->seminar->getAccreditationNumber(), ENT_QUOTES | ENT_HTML5)
        );
    }

    /**
     * Fills in the matching marker for the credit points or hides the subpart if there are no credit points.
     */
    private function setCreditPointsMarker(): void
    {
        if (!$this->seminar->hasCreditPoints()) {
            $this->hideSubparts('credit_points', 'field_wrapper');
            return;
        }

        $this->setMarker('credit_points', $this->seminar->getCreditPoints());
    }

    /**
     * Fills in the matching marker for the categories or hides the subpart if there are no categories.
     */
    private function setCategoriesMarker(): void
    {
        if (!$this->seminar->hasCategories()) {
            $this->hideSubparts('category', 'field_wrapper');
            return;
        }

        $categoryMarker = '';
        foreach ($this->seminar->getCategories() as $category) {
            $this->setMarker('category_title', \htmlspecialchars($category['title'], ENT_QUOTES | ENT_HTML5));
            $this->setMarker(
                'category_icon',
                $this->createCategoryIcon($category)
            );
            $categoryMarker .= $this->getSubpart('SINGLE_CATEGORY');
        }
        $this->setSubpart('SINGLE_CATEGORY', $categoryMarker);
    }

    /**
     * Fills in the matching marker for the place.
     */
    private function setPlaceMarker(): void
    {
        $this->setMarker(
            'place',
            $this->getConfValueBoolean('showSiteDetails', 's_template_special')
                ? $this->seminar->getPlaceWithDetails($this)
                : \htmlspecialchars($this->seminar->getPlaceShort(), ENT_QUOTES | ENT_HTML5)
        );
    }

    /**
     * Fills in the matching marker for the room or hides the subpart if there is no room.
     */
    private function setRoomMarker(): void
    {
        if (!$this->seminar->hasRoom()) {
            $this->hideSubparts('room', 'field_wrapper');
            return;
        }

        $this->setMarker('room', \htmlspecialchars($this->seminar->getRoom(), ENT_QUOTES | ENT_HTML5));
    }

    /**
     * Fills in the matching markers for the time slots or hides the subpart if there are no time slots.
     */
    protected function setTimeSlotsMarkers(): void
    {
        if (!$this->seminar->hasTimeslots()) {
            $this->hideSubparts('timeslots', 'field_wrapper');
            return;
        }

        $this->hideSubparts('date,time', 'field_wrapper');

        $timeSlotMapper = MapperRegistry::get(\Tx_Seminars_Mapper_TimeSlot::class);

        $timeSlotsOutput = '';
        foreach ($this->seminar->getTimeSlotsAsArrayWithMarkers() as $timeSlotData) {
            $this->setMarker('timeslot_date', $timeSlotData['date']);
            $this->setMarker('timeslot_time', $timeSlotData['time']);
            $this->setMarker('timeslot_entry_date', $timeSlotData['entry_date']);
            $this->setMarker('timeslot_room', \htmlspecialchars($timeSlotData['room'], ENT_QUOTES | ENT_HTML5));
            $this->setMarker('timeslot_place', \htmlspecialchars($timeSlotData['place'], ENT_QUOTES | ENT_HTML5));
            $this->setMarker('timeslot_speakers', \htmlspecialchars($timeSlotData['speakers'], ENT_QUOTES | ENT_HTML5));

            $timeSlotsOutput .= $this->getSubpart('SINGLE_TIMESLOT');
        }

        $this->setSubpart('SINGLE_TIMESLOT', $timeSlotsOutput);
    }

    /**
     * Fills in the matching marker for the expiry or hides the subpart if there is no expiry.
     */
    private function setExpiryMarker(): void
    {
        if (!$this->seminar->hasExpiry()) {
            $this->hideSubparts('expiry', 'field_wrapper');
            return;
        }

        $this->setMarker('expiry', $this->seminar->getExpiry());
    }

    /**
     * Fills in the matching markers for the speakers or hides the subpart if there are no speakers.
     */
    private function setSpeakersMarker(): void
    {
        if (!$this->seminar->hasSpeakers()) {
            $this->hideSubparts('speakers', 'field_wrapper');
            return;
        }

        $this->setSpeakersMarkerWithoutCheck('speakers');
    }

    /**
     * Fills in the matching markers for the partners or hides the subpart if there are no partners.
     */
    private function setPartnersMarker(): void
    {
        if (!$this->seminar->hasPartners()) {
            $this->hideSubparts('partners', 'field_wrapper');
            return;
        }

        $this->setSpeakersMarkerWithoutCheck('partners');
    }

    /**
     * Fills in the matching markers for the tutors or hides the subpart if there are no tutors.
     */
    private function setTutorsMarker(): void
    {
        if (!$this->seminar->hasTutors()) {
            $this->hideSubparts('tutors', 'field_wrapper');
            return;
        }

        $this->setSpeakersMarkerWithoutCheck('tutors');
    }

    /**
     * Fills in the matching markers for the leaders or hides the subpart if there are no leaders.
     */
    private function setLeadersMarker(): void
    {
        if (!$this->seminar->hasLeaders()) {
            $this->hideSubparts('leaders', 'field_wrapper');
            return;
        }

        $this->setSpeakersMarkerWithoutCheck('leaders');
    }

    /**
     * Sets the speaker markers for the type given in $speakerType without
     * checking whether the current event has any speakers of the given type.
     *
     * @param string $speakerType the speaker type to set the markers for, must not be empty, must be
     *        one of the following: "speakers", "partners", "tutors" or "leaders"
     */
    private function setSpeakersMarkerWithoutCheck(string $speakerType): void
    {
        if (!in_array($speakerType, self::VALID_SPEAKER_TYPES, true)) {
            throw new \InvalidArgumentException('The given speaker type is not valid.', 1333293083);
        }

        $speakerContent = $this->getConfValueBoolean('showSpeakerDetails', 's_template_special')
            ? $this->seminar->getSpeakersWithDetails($this, $speakerType)
            : $this->seminar->getSpeakersShort($this, $speakerType);
        $this->setMarker($speakerType, $speakerContent);
    }

    /**
     * Fills in the matching marker for the language or hides the unused subpart.
     */
    private function setLanguageMarker(): void
    {
        if (!$this->seminar->hasLanguage()) {
            $this->hideSubparts('language', 'field_wrapper');
            return;
        }

        $this->setMarker('language', \htmlspecialchars($this->seminar->getLanguageName(), ENT_QUOTES | ENT_HTML5));
    }

    /**
     * Fills in the matching markers for the prices or hides the unused subparts.
     */
    private function setSingleViewPriceMarkers(): void
    {
        if ($this->seminar->getPriceOnRequest()) {
            $this->setSingleViewPriceMarkersForOnRequest();
            return;
        }

        $this->setSingleViewRegularPriceMarkers();
        $this->setSingleViewSpecialPriceMarkers();
        $this->setSingleViewBoardPriceMarkers();
    }

    /**
     * Sets the price to "on request" and hides all other price markers
     *
     * This method may only be called if the current event is set to "price on request".
     */
    private function setSingleViewPriceMarkersForOnRequest(): void
    {
        if (!$this->seminar->getPriceOnRequest()) {
            return;
        }

        $this->setMarker('price_regular', $this->seminar->getCurrentPriceRegular());
        $this->hideSubparts(
            'price_special,price_earlybird_regular,price_earlybird_special,price_board_regular,price_board_special',
            'field_wrapper'
        );
    }

    /**
     * Set the regular price (with or without early bird rebate).
     */
    private function setSingleViewRegularPriceMarkers(): void
    {
        if ($this->seminar->hasEarlyBirdPrice() && !$this->seminar->isEarlyBirdDeadlineOver()) {
            $this->setMarker('price_earlybird_regular', $this->seminar->getEarlyBirdPriceRegular());
            $this->setMarker(
                'message_earlybird_price_regular',
                sprintf($this->translate('message_earlybird_price'), $this->seminar->getEarlyBirdDeadline())
            );
            $this->setMarker('price_regular', $this->seminar->getPriceRegular());
        } else {
            $this->setMarker('price_regular', $this->seminar->getPriceRegular());
            if ($this->getConfValueBoolean('generalPriceInSingle', 's_template_special')) {
                $this->setMarker('label_price_regular', $this->translate('label_price_general'));
            }
            $this->hideSubparts('price_earlybird_regular', 'field_wrapper');
        }
    }

    /**
     * Set the special price (with or without early bird rebate).
     */
    private function setSingleViewSpecialPriceMarkers(): void
    {
        if ($this->seminar->hasPriceSpecial()) {
            if ($this->seminar->hasEarlyBirdPrice() && !$this->seminar->isEarlyBirdDeadlineOver()) {
                $this->setMarker('price_earlybird_special', $this->seminar->getEarlyBirdPriceSpecial());
                $this->setMarker(
                    'message_earlybird_price_special',
                    sprintf($this->translate('message_earlybird_price'), $this->seminar->getEarlyBirdDeadline())
                );
                $this->setMarker('price_special', $this->seminar->getPriceSpecial());
            } else {
                $this->setMarker('price_special', $this->seminar->getPriceSpecial());
                $this->hideSubparts('price_earlybird_special', 'field_wrapper');
            }
        } else {
            $this->hideSubparts('price_special', 'field_wrapper');
            $this->hideSubparts('price_earlybird_special', 'field_wrapper');
        }
    }

    /**
     * Sets the prices with board (regular and special) or hides the corresponding subparts if those prices
     * are not available.
     */
    private function setSingleViewBoardPriceMarkers(): void
    {
        if ($this->seminar->hasPriceRegularBoard()) {
            $this->setMarker('price_board_regular', $this->seminar->getPriceRegularBoard());
        } else {
            $this->hideSubparts('price_board_regular', 'field_wrapper');
        }

        if ($this->seminar->hasPriceSpecialBoard()) {
            $this->setMarker('price_board_special', $this->seminar->getPriceSpecialBoard());
        } else {
            $this->hideSubparts('price_board_special', 'field_wrapper');
        }
    }

    /**
     * Fills in the matching marker for the payment methods or hides the subpart
     * if there are no payment methods.
     */
    private function setPaymentMethodsMarker(): void
    {
        if (!$this->seminar->hasPaymentMethods()) {
            $this->hideSubparts('paymentmethods', 'field_wrapper');
            return;
        }

        $paymentMethods = $this->seminar->getPaymentMethods();

        $paymentMethodOutput = '';
        foreach ($paymentMethods as $paymentMethod) {
            $this->setMarker('payment_method', \htmlspecialchars($paymentMethod, ENT_QUOTES | ENT_HTML5));
            $paymentMethodOutput .= $this->getSubpart('SINGLE_PAYMENT_METHOD');
        }

        $this->setSubpart('SINGLE_PAYMENT_METHOD', $paymentMethodOutput);
    }

    /**
     * Fills in the matching marker for the additional information or hides the
     * subpart if there is no additional information.
     */
    private function setAdditionalInformationMarker(): void
    {
        if (!$this->seminar->hasAdditionalInformation()) {
            $this->hideSubparts('additional_information', 'field_wrapper');
            return;
        }

        $this->setMarker(
            'additional_information',
            $this->pi_RTEcssText($this->seminar->getAdditionalInformation())
        );
    }

    /**
     * Fills in the matching markers for the attached files or hides the subpart if there are no attached files.
     */
    private function setAttachedFilesMarkers(): void
    {
        if (!$this->seminar->hasAttachedFiles() || !$this->mayUserAccessAttachedFiles()) {
            $this->hideSubparts('attached_files', 'field_wrapper');
            return;
        }

        $attachedFilesOutput = '';

        /** @var string[] $attachedFile */
        foreach ($this->seminar->getAttachedFiles($this) as $attachedFile) {
            $this->setMarker('attached_file_name', $attachedFile['name']);
            $this->setMarker('attached_file_size', $attachedFile['size']);
            $this->setMarker('attached_file_type', $attachedFile['type']);

            $attachedFilesOutput .= $this->getSubpart(
                'ATTACHED_FILES_LIST_ITEM'
            );
        }

        $this->setSubpart(
            'ATTACHED_FILES_LIST_ITEM',
            $attachedFilesOutput
        );
    }

    /**
     * Fills in the matching marker for the target groups or hides the subpart if there are no target groups.
     */
    private function setTargetGroupsMarkers(): void
    {
        if (!$this->seminar->hasTargetGroups()) {
            $this->hideSubparts('target_groups', 'field_wrapper');
            return;
        }

        $targetGroupsOutput = '';

        foreach ($this->seminar->getTargetGroupsAsArray() as $targetGroup) {
            $this->setMarker('target_group', \htmlspecialchars($targetGroup, ENT_QUOTES | ENT_HTML5));
            $targetGroupsOutput .= $this->getSubpart('SINGLE_TARGET_GROUP');
        }

        $this->setSubpart('SINGLE_TARGET_GROUP', $targetGroupsOutput);
    }

    /**
     * Fills the matching marker for the requirements or hides the subpart
     * if there are no requirements for the current event.
     */
    private function setRequirementsMarker(): void
    {
        if (!$this->seminar->hasRequirements()) {
            $this->hideSubparts('requirements', 'field_wrapper');
            return;
        }

        $requirementsLists = $this->createRequirementsList();
        $requirementsLists->setEvent($this->seminar);

        $this->setSubpart(
            'FIELD_WRAPPER_REQUIREMENTS',
            $requirementsLists->render()
        );
    }

    /**
     * Fills the matching marker for the dependencies or hides the subpart
     * if there are no dependencies for the current event.
     */
    private function setDependenciesMarker(): void
    {
        if (!$this->seminar->hasDependencies()) {
            $this->hideSubparts('dependencies', 'field_wrapper');
            return;
        }

        $output = '';

        $eventMapper = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);

        /** @var LegacyEvent $dependency */
        foreach ($this->seminar->getDependencies() as $dependency) {
            $event = $eventMapper->find($dependency->getUid());
            $this->setMarker(
                'dependency_title',
                $this->createSingleViewLink($event, $event->getTitle())
            );
            $output .= $this->getSubpart('SINGLE_DEPENDENCY');
        }

        $this->setSubpart('SINGLE_DEPENDENCY', $output);
    }

    /**
     * Fills in the matching marker for the organizing partners or hides the
     * subpart if there are no organizing partners.
     */
    private function setOrganizingPartnersMarker(): void
    {
        if (!$this->seminar->hasOrganizingPartners()) {
            $this->hideSubparts('organizing_partners', 'field_wrapper');
            return;
        }

        $this->setMarker('organizing_partners', $this->seminar->getOrganizingPartners($this));
    }

    /**
     * Fills in the matching marker for the owner data or hides the subpart if
     * the event has no owner or the owner data should not be displayed.
     */
    private function setOwnerDataMarker(): void
    {
        if (!$this->getConfValueBoolean('showOwnerDataInSingleView', 's_singleView') || !$this->seminar->hasOwner()) {
            $this->hideSubparts('owner_data', 'field_wrapper');
            return;
        }

        $owner = $this->seminar->getOwner();
        $ownerData = [];
        // getName always returns a non-empty string for valid records.
        $ownerData[] = \htmlspecialchars($owner->getName(), ENT_QUOTES | ENT_HTML5);
        if ($owner->hasPhoneNumber()) {
            $ownerData[] = \htmlspecialchars($owner->getPhoneNumber(), ENT_QUOTES | ENT_HTML5);
        }
        if ($owner->hasEmailAddress()) {
            $ownerData[] = \htmlspecialchars($owner->getEmailAddress(), ENT_QUOTES | ENT_HTML5);
        }
        $this->setSubpart(
            'OWNER_DATA',
            implode($this->getSubpart('OWNER_DATA_SEPARATOR'), $ownerData)
        );
    }

    /**
     * Fills in the matching marker for the vacancies or hides the subpart no registration is possible.
     */
    private function setVacanciesMarker(): void
    {
        $vacancies = $this->seminar->getVacanciesString();
        if ($vacancies !== '') {
            $this->setMarker('vacancies', $vacancies);
        } else {
            $this->hideSubparts('vacancies', 'field_wrapper');
        }
    }

    /**
     * Fills in the matching marker for the registration deadline or hides the
     * subpart if there is no registration deadline.
     */
    private function setRegistrationDeadlineMarker(): void
    {
        if (!$this->seminar->hasRegistrationDeadline()) {
            $this->hideSubparts('deadline_registration', 'field_wrapper');
            return;
        }

        $this->setMarker('deadline_registration', $this->seminar->getRegistrationDeadline());
    }

    /**
     * Checks whether online registration is enabled at all by configuration.
     */
    protected function isRegistrationEnabled(): bool
    {
        return $this->getConfValueBoolean('enableRegistration');
    }

    /**
     * Checks whether a front-end user is logged in.
     */
    public function isLoggedIn(): bool
    {
        return FrontEndLoginManager::getInstance()->isLoggedIn();
    }

    /**
     * Returns the UID of the logged-in front-end user (or 0 if no user is logged in).
     */
    protected function getLoggedInFrontEndUserUid(): int
    {
        $loginManager = FrontEndLoginManager::getInstance();
        return $loginManager->isLoggedIn() ? $loginManager->getLoggedInUser(
            \Tx_Seminars_Mapper_FrontEndUser::class
        )->getUid() : 0;
    }

    /**
     * Fills in the matching marker for the link to the registration form or
     * hides the subpart if the registration is disabled.
     */
    private function setRegistrationMarker(): void
    {
        if (!$this->isRegistrationEnabled() || $this->seminar->getPriceOnRequest()) {
            $this->hideSubparts('registration', 'field_wrapper');
            return;
        }

        $this->setMarker(
            'registration',
            $this->getRegistrationManager()->canRegisterIfLoggedIn($this->seminar)
                ? $this->getRegistrationManager()->getLinkToRegistrationOrLoginPage($this, $this->seminar)
                : $this->getRegistrationManager()->canRegisterIfLoggedInMessage($this->seminar)
        );
    }

    /**
     * Fills in the matching marker for the link to the list of registrations
     * or hides the subpart if the currently logged in FE user is not allowed
     * to view the list of registrations.
     */
    private function setListOfRegistrationMarker(): void
    {
        $canViewListOfRegistrations = $this->seminar->canViewRegistrationsList(
            $this->whatToDisplay,
            $this->getConfValueInteger('registrationsListPID'),
            $this->getConfValueInteger('registrationsVipListPID')
        );

        if (!$canViewListOfRegistrations) {
            $this->hideSubparts('list_registrations', 'field_wrapper');
            return;
        }

        $this->setMarker('list_registrations', $this->getRegistrationsListLink());
    }

    /**
     * Hides unneeded subparts for topic records.
     */
    private function hideUnneededSubpartsForTopicRecords(): void
    {
        if ($this->seminar->getRecordType() != \Tx_Seminars_Model_Event::TYPE_TOPIC) {
            return;
        }

        $this->hideSubparts(
            'accreditation_number,date,time,place,room,speakers,organizers,' .
            'vacancies,deadline_registration,registration,' .
            'list_registrations,eventsnextday',
            'field_wrapper'
        );
    }

    /**
     * Creates the list of events that start the next day (after the current
     * event has ended). Practically, this is just a special kind of list view.
     * In case the current record is a topic record, this function will return
     * an empty string.
     *
     * Note: This function relies on $this->seminar, but also overwrites
     * $this->seminar.
     *
     * @return string HTML for the events list (may be an empty string)
     */
    private function createEventsOnNextDayList(): string
    {
        $result = '';

        $seminarBag = $this->initListView('events_next_day');

        if ($this->internal['res_count']) {
            $tableEventsNextDay = $this->createListTable($seminarBag, 'events_next_day');

            $this->setMarker('table_eventsnextday', $tableEventsNextDay);

            $result = $this->getSubpart('EVENTSNEXTDAY_VIEW');
        }
        $result .= $this->checkListViewConfiguration();

        return $result;
    }

    /**
     * @return string error messages as HTML; will be empty if there are none of if the configuration check is disabled
     */
    private function checkListViewConfiguration(): string
    {
        if (!$this->isConfigurationCheckEnabled()) {
            return '';
        }

        $configurationCheck = new ListViewConfigurationCheck(
            $this->buildConfigurationWithFlexForms(),
            'plugin.tx_seminars_pi1'
        );
        $configurationCheck->check();

        return \implode("\n", $configurationCheck->getWarningsAsHtml());
    }

    /**
     * Creates the list of (other) dates for this topic. Practically, this is
     * just a special kind of list view. In case this topic has no other dates,
     * this function will return an empty string.
     *
     * Note: This function relies on $this->seminar, but also overwrites
     * $this->seminar.
     *
     * @return string HTML for the events list (may be an empty string)
     */
    private function createOtherDatesList(): string
    {
        $result = '';

        $seminarBag = $this->initListView('other_dates');

        if ($this->internal['res_count']) {
            // If we are on a topic record, overwrite the label with an alternative text.
            if (
                \in_array(
                    $this->seminar->getRecordType(),
                    [\Tx_Seminars_Model_Event::TYPE_COMPLETE, \Tx_Seminars_Model_Event::TYPE_TOPIC],
                    true
                )
            ) {
                $this->setMarker('label_list_otherdates', $this->translate('label_list_dates'));
            }

            // Hides unneeded columns from the list.
            $temporaryHiddenColumns = ['title', 'list_registrations'];
            $this->hideColumns($temporaryHiddenColumns);

            $tableOtherDates = $this->createListTable($seminarBag, 'other_dates');

            $this->setMarker('table_otherdates', $tableOtherDates);

            $result = $this->getSubpart('OTHERDATES_VIEW');

            // Un-hides the previously hidden columns.
            $this->unhideColumns($temporaryHiddenColumns);
        }
        $result .= $this->checkListViewConfiguration();

        return $result;
    }

    /////////////////////////
    // List view functions.
    /////////////////////////

    /**
     * Creates the HTML for the event list view.
     * This function is used for the normal event list as well as the
     * "my events" and the "my VIP events" list.
     *
     * @param string $whatToDisplay a string selecting the flavor of list view: either an empty string
     *        (for the default list view), the value from "what_to_display" or "other_dates"
     *
     * @return string HTML code with the event list
     */
    protected function createListView(string $whatToDisplay): string
    {
        $configurationCheckResult = $this->checkListViewConfiguration();
        if ($configurationCheckResult !== '') {
            // There are configuration check errors. As some of those detected configuration problems
            // could cause exceptions, we'd rather display the (more helpful) warnings than crash.
            return $configurationCheckResult;
        }

        $result = '';
        $isOkay = true;
        $this->ensureIntegerPiVars(
            [
                'from_day',
                'from_month',
                'from_year',
                'to_day',
                'to_month',
                'to_year',
                'age',
                'price_from',
                'price_to',
            ]
        );

        $this->ensureIntegerArrayValues(['event_type', 'place', 'organizer']);

        switch ($whatToDisplay) {
            case 'my_events':
                if ($this->isLoggedIn()) {
                    $result .= $this->getSubpart('MESSAGE_MY_EVENTS');
                } else {
                    $this->setMarker('error_text', $this->translate('message_notLoggedIn'));
                    $result .= $this->getSubpart('ERROR_VIEW');
                    $result .= $this->getLoginLink(
                        $this->translate('message_pleaseLogIn'),
                        $this->getFrontEndController()->id
                    );
                    $isOkay = false;
                }
                break;
            case 'my_vip_events':
                if ($this->isLoggedIn()) {
                    $result .= $this->getSubpart('MESSAGE_MY_VIP_EVENTS');
                } else {
                    $this->setMarker('error_text', $this->translate('message_notLoggedIn'));
                    $result .= $this->getSubpart('ERROR_VIEW');
                    $result .= $this->getLoginLink(
                        $this->translate('message_pleaseLogIn'),
                        (int)$this->getFrontEndController()->id
                    );
                    $isOkay = false;
                }

                if ($this->isConfigurationCheckEnabled()) {
                    $configurationCheck = new MyVipEventsConfigurationCheck(
                        $this->buildConfigurationWithFlexForms(),
                        'plugin.tx_seminars_pi1'
                    );
                    $configurationCheck->check();
                    $result .= \implode("\n", $configurationCheck->getWarningsAsHtml());
                    $configuration = ConfigurationRegistry::get('plugin.tx_seminars');
                    $configurationCheck = new CsvExportConfigurationCheck($configuration, 'plugin.tx_seminars');
                    $configurationCheck->check();
                    $result .= \implode("\n", $configurationCheck->getWarningsAsHtml());
                }

                break;
            case 'my_entered_events':
                if ($this->hasEventEditorAccess()) {
                    $result .= $this->getSubpart('MESSAGE_MY_ENTERED_EVENTS');
                    if ($this->isConfigurationCheckEnabled()) {
                        $configurationCheck = new MyEnteredEventsConfigurationCheck(
                            $this->buildConfigurationWithFlexForms(),
                            'plugin.tx_seminars_pi1'
                        );
                        $configurationCheck->check();
                        $result .= \implode("\n", $configurationCheck->getWarningsAsHtml());
                    }
                } else {
                    $isOkay = false;
                }
                break;
            case 'favorites_list':
                $result = 'Hello World. When I grow up I will be the list of favorites';
                break;
            default:
        }

        if ($isOkay) {
            $result .= $this->getSelectorWidgetIfNecessary($whatToDisplay);

            // Creates the seminar or registration bag for the list view (with
            // all the filtering applied).
            $seminarOrRegistrationBag = $this->initListView($whatToDisplay);

            if ($this->internal['res_count']) {
                $result .= $this->createListTable($seminarOrRegistrationBag, $whatToDisplay);
            } else {
                $this->setMarker(
                    'error_text',
                    $this->translate('message_noResults')
                );
                $result .= $this->getSubpart('ERROR_VIEW');
            }

            // Shows the page browser (if not deactivated in the configuration).
            if (!$this->getConfValueBoolean('hidePageBrowser', 's_template_special')) {
                $result .= $this->pi_list_browseresults();
            }
        }

        return $result;
    }

    /**
     * Initializes the list view (normal list, my events or my VIP events) and
     * creates a seminar bag or a registration bag (for the "my events" view),
     * but does not create any actual HTML output.
     *
     * @param string $whatToDisplay the flavor of list view: either an empty string (for the default list view),
     *        the value from "what_to_display", or "other_dates"
     *
     * @return RegistrationBag|EventBag a bag containing the items for the list view
     */
    public function initListView(string $whatToDisplay = ''): AbstractBag
    {
        if (\strpos((string)$this->cObj->currentRecord, 'tt_content') !== false) {
            $this->conf['pidList'] = $this->getConfValueString('pages');
            $this->conf['recursive'] = $this->getConfValueInteger('recursive');
        }

        $this->hideColumnsForAllViewsFromTypoScriptSetup();
        $this->hideRegisterColumnIfNecessary($whatToDisplay);
        $this->hideColumnsForAllViewsExceptMyEvents($whatToDisplay);
        $this->hideCsvExportOfRegistrationsColumnIfNecessary($whatToDisplay);
        $this->hideListRegistrationsColumnIfNecessary($whatToDisplay);
        $this->hideEditColumnIfNecessary($whatToDisplay);
        $this->hideFilesColumnIfUserCannotAccessFiles();
        $this->hideStatusColumnIfNotUsed($whatToDisplay);

        if (!isset($this->piVars['pointer'])) {
            $this->piVars['pointer'] = 0;
        }

        $this->internal['descFlag'] = $this->getListViewConfValueBoolean('descFlag');
        $this->internal['orderBy'] = $this->getListViewConfValueString('orderBy');

        // number of results to show in a listing
        $this->internal['results_at_a_time'] = MathUtility::forceIntegerInRange(
            $this->getListViewConfValueInteger('results_at_a_time'),
            0,
            1000,
            20
        );
        // maximum number of 'pages' in the browse-box: 'Page 1', 'Page 2', etc.
        $this->internal['maxPages'] = MathUtility::forceIntegerInRange(
            $this->getListViewConfValueInteger('maxPages'),
            0,
            1000,
            2
        );

        if ($whatToDisplay === 'my_events') {
            $builder = $this->createRegistrationBagBuilder();
        } else {
            $builder = $this->createSeminarBagBuilder();
        }

        if ($whatToDisplay !== 'my_events') {
            $this->limitForAdditionalParameters($builder);
        }
        if (!in_array($whatToDisplay, ['my_entered_events', 'my_events', 'topic_list'], true)) {
            $builder->limitToDateAndSingleRecords();
            $this->limitToTimeFrameSetting($builder);
        }

        /** @var \Tx_Seminars_Model_FrontEndUser|null $user */
        $user = FrontEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_FrontEndUser::class);

        switch ($whatToDisplay) {
            case 'topic_list':
                $builder->limitToTopicRecords();
                $this->hideColumnsForTheTopicListView();
                break;
            case 'my_events':
                $builder->limitToAttendee($user);
                break;
            case 'my_vip_events':
                $groupForDefaultVips = $this->getConfValueInteger('defaultEventVipsFeGroupID', 's_template_special');
                $isDefaultVip = $groupForDefaultVips !== 0 && $user->hasGroupMembership((string)$groupForDefaultVips);

                if (!$isDefaultVip) {
                    // The current user is not listed as a default VIP for all
                    // events. Change the query to show only events where the
                    // current user is manually added as a VIP.
                    $builder->limitToEventManager($this->getLoggedInFrontEndUserUid());
                }
                break;
            case 'my_entered_events':
                $builder->limitToOwner($user !== null ? $user->getUid() : 0);
                $builder->showHiddenRecords();
                break;
            case 'events_next_day':
                $builder->limitToEventsNextDay($this->seminar);
                break;
            case 'other_dates':
                $builder->limitToOtherDatesForTopic($this->seminar);
                break;
            default:
        }

        if (($whatToDisplay === 'other_dates') || ($whatToDisplay === 'seminar_list')) {
            $hideBookedOutEvents = $this->getConfValueBoolean('showOnlyEventsWithVacancies', 's_listView');
            if ($hideBookedOutEvents) {
                $builder->limitToEventsWithVacancies();
            }
        }

        $pointer = (int)$this->piVars['pointer'];
        $resultsAtATime = MathUtility::forceIntegerInRange($this->internal['results_at_a_time'], 1, 1000);
        $builder->setLimit(($pointer * $resultsAtATime) . ',' . $resultsAtATime);

        if ($builder instanceof EventBagBuilder) {
            $this->getListViewHookProvider()->executeHook('modifyEventBagBuilder', $this, $builder, $whatToDisplay);
        } elseif ($builder instanceof RegistrationBagBuilder) {
            $this->getListViewHookProvider()
                ->executeHook('modifyRegistrationBagBuilder', $this, $builder, $whatToDisplay);
        }

        $seminarOrRegistrationBag = $builder->build();

        $this->internal['res_count'] = $seminarOrRegistrationBag->countWithoutLimit();

        $this->previousDate = '';
        $this->previousCategory = '';

        return $seminarOrRegistrationBag;
    }

    /**
     * Creates just the table for the list view (without any result browser or
     * search form).
     * This function should only be called when there are actually any list
     * items.
     *
     * @param RegistrationBag|EventBag $seminarOrRegistrationBag initialized bag
     * @param string $whatToDisplay a string selecting the flavor of list view: either an empty string
     *        (for the default list view), the value from "what_to_display" or "other_dates"
     *
     * @return string HTML for the table (will not be empty)
     */
    protected function createListTable(AbstractBag $seminarOrRegistrationBag, string $whatToDisplay): string
    {
        $result = $this->createListHeader();
        $rowCounter = 0;

        foreach ($seminarOrRegistrationBag as $currentItem) {
            if ($whatToDisplay === 'my_events') {
                /** @var LegacyRegistration $currentItem */
                $this->registration = $currentItem;
                $this->setSeminar($this->registration->getSeminarObject());
            } else {
                /** @var LegacyEvent $currentItem */
                $this->setSeminar($currentItem);
            }

            $result .= $this->createListRow($rowCounter, $whatToDisplay);
            $rowCounter++;
        }

        $result .= $this->createListFooter();

        return $result;
    }

    /**
     * Returns the list view header: Start of table, header row, start of table body.
     *
     * Columns listed in `$this->subpartsToHide` are hidden (i.e., not displayed).
     *
     * @return string HTML output: the table header
     */
    protected function createListHeader(): string
    {
        $availableColumns = [
            'image',
            'category',
            'title',
            'subtitle',
            'uid',
            'event_type',
            'accreditation_number',
            'credit_points',
            'speakers',
            'language',
            'date',
            'time',
            'place',
            'country',
            'city',
            'seats',
            'price_regular',
            'price_special',
            'total_price',
            'organizers',
            'target_groups',
            'attached_files',
            'vacancies',
            'status_registration',
            'registration',
            'list_registrations',
            'status',
            'edit',
            'registrations',
        ];

        foreach ($availableColumns as $column) {
            $this->setMarker('header_' . $column, $this->getFieldHeader($column));
        }

        $this->getListViewHookProvider()->executeHook('modifyListHeader', $this);

        return $this->getSubpart('LIST_HEADER');
    }

    /**
     * Returns the list view footer: end of table body, end of table.
     *
     * @return string HTML output: the table footer
     */
    protected function createListFooter(): string
    {
        $this->getListViewHookProvider()->executeHook('modifyListFooter', $this);

        return $this->getSubpart('LIST_FOOTER');
    }

    /**
     * Returns a list row as a TR. Gets data from $this->seminar.
     *
     * Columns listed in $this->subpartsToHide are hidden (ie. not displayed).
     * If $this->seminar is invalid, an empty string is returned.
     *
     * @param int $rowCounter Row counter. Starts at 0 (zero). Used for alternating class values in the output rows.
     * @param string $whatToDisplay a string selecting the flavor of list view: either an empty string
     *        (for the default list view), the value from "what_to_display", or "other_dates"
     *
     * @return string HTML output, a table row with a class attribute set (alternative based on odd/even rows)
     */
    protected function createListRow(int $rowCounter = 0, string $whatToDisplay = ''): string
    {
        $result = '';

        if ($this->seminar->comesFromDatabase()) {
            $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);
            $event = $mapper->find($this->getSeminar()->getUid());

            $cssClasses = [];

            $cssClasses[] = ($rowCounter % 2) ? 'listrow-odd' : 'listrow-even';
            if ($this->seminar->isCanceled()) {
                $cssClasses[] = $this->pi_getClassName('canceled');
            }
            if ($this->seminar->isOwnerFeUser()) {
                $cssClasses[] = $this->pi_getClassName('owner');
            }
            $completeClass = implode(' ', $cssClasses);

            $this->setMarker('class_itemrow', $completeClass);

            // Retrieves the data for the columns "number of seats", "total
            // price" and "status", but only if we are on the "my_events" list.
            if ($whatToDisplay == 'my_events') {
                $attendanceData = [
                    'seats' => $this->registration->getSeats(),
                    'total_price' => $this->registration->getTotalPrice(),
                ];
                $this->setMarker('status_registration', $this->registration->getStatus());
            } else {
                $attendanceData = ['seats' => '', 'total_price' => ''];
            }

            if ($this->seminar->hasImage()) {
                $imageConfiguration = [
                    'altText' => $this->seminar->getTitle(),
                    'titleText' => $this->seminar->getTitle(),
                    'file' => \Tx_Seminars_FrontEnd_AbstractView::UPLOAD_PATH . $this->seminar->getImage(),
                    'file.' => [
                        'width' => $this->getConfValueInteger('seminarImageListViewWidth') . 'c',
                        'height' => $this->getConfValueInteger('seminarImageListViewHeight') . 'c',
                    ],
                ];
                $image = $this->cObj->cObjGetSingle('IMAGE', $imageConfiguration);
            } else {
                $image = '';
            }
            $this->setMarker('image', $image);

            /** @var \Tx_Seminars_FrontEnd_CategoryList $categoryList */
            $categoryList = GeneralUtility::makeInstance(
                \Tx_Seminars_FrontEnd_CategoryList::class,
                $this->conf,
                $this->cObj
            );
            $listOfCategories = $categoryList->createCategoryList($this->seminar->getCategories());

            if (
                $listOfCategories === $this->previousCategory
                && $this->getConfValueBoolean('sortListViewByCategory', 's_template_special')
            ) {
                $listOfCategories = '';
            } else {
                $this->previousCategory = $listOfCategories;
            }
            $this->setMarker('category', $listOfCategories);

            $this->setMarker('title_link', $this->createSingleViewLink($event, $this->seminar->getTitle()));
            $this->setMarker('subtitle', \htmlspecialchars($this->seminar->getSubtitle(), ENT_QUOTES | ENT_HTML5));
            $this->setMarker('uid', $this->seminar->getUid());
            $this->setMarker('event_type', \htmlspecialchars($this->seminar->getEventType(), ENT_QUOTES | ENT_HTML5));
            $this->setMarker(
                'accreditation_number',
                \htmlspecialchars($this->seminar->getAccreditationNumber(), ENT_QUOTES | ENT_HTML5)
            );
            $this->setMarker('credit_points', $this->seminar->getCreditPoints());
            $this->setMarker('teaser', $this->createSingleViewLink($event, $event->getTeaser()));
            $this->setMarker('speakers', $this->seminar->getSpeakersShort($this));
            $this->setMarker('language', \htmlspecialchars($this->seminar->getLanguageName(), ENT_QUOTES | ENT_HTML5));

            $currentDate = $this->seminar->getDate();
            if (
                $currentDate == $this->previousDate
                && $this->getConfValueBoolean('omitDateIfSameAsPrevious', 's_template_special')
            ) {
                $dateToShow = '';
            } else {
                if ($whatToDisplay === 'other_dates') {
                    $dateToShow = $this->createSingleViewLink($event, $this->seminar->getDate(), false);
                } else {
                    $dateToShow = $currentDate;
                }
                $this->previousDate = $currentDate;
            }
            $this->setMarker('date', $dateToShow);

            $this->setMarker('time', $this->seminar->getTime());
            $this->setMarker('expiry', $this->seminar->getExpiry());

            $this->setMarker('place', \htmlspecialchars($this->seminar->getPlaceShort(), ENT_QUOTES | ENT_HTML5));
            $this->setMarker('country', \htmlspecialchars($this->seminar->getCountry(), ENT_QUOTES | ENT_HTML5));
            $this->setMarker('city', \htmlspecialchars($this->seminar->getCities(), ENT_QUOTES | ENT_HTML5));
            $this->setMarker('seats', $attendanceData['seats']);
            $this->setMarker('price_regular', $this->seminar->getCurrentPriceRegular());
            $this->setMarker('price_special', $this->seminar->getCurrentPriceSpecial());
            $this->setMarker('total_price', $attendanceData['total_price']);
            $this->setMarker('organizers', $this->seminar->getOrganizers($this));
            $this->setMarker(
                'target_groups',
                \htmlspecialchars($this->seminar->getTargetGroupNames(), ENT_QUOTES | ENT_HTML5)
            );
            $this->setMarker('attached_files', $this->getAttachedFilesListMarkerContent());
            $this->setMarker('vacancies', $this->seminar->getVacanciesString());
            $this->setMarker('class_listvacancies', $this->getVacanciesClasses($this->seminar));

            $this->setRegistrationLinkMarker($whatToDisplay);

            $this->setMarker('list_registrations', $this->getRegistrationsListLink());

            $this->setVisibilityStatusMarker();

            $this->setMarker('edit', $this->createAllEditorLinks());

            $this->setMarker('registrations', $this->getCsvExportLink());

            $this->getListViewHookProvider()->executeHook('modifyListRow', $this);

            if ($whatToDisplay === 'my_events') {
                $this->getListViewHookProvider()->executeHook('modifyMyEventsListRow', $this);
            }

            $result = $this->getSubpart('LIST_ITEM');
        }

        return $result;
    }

    /**
     * Returns a seminarBagBuilder object with the source pages set for the list view.
     */
    private function createSeminarBagBuilder(): EventBagBuilder
    {
        /** @var EventBagBuilder $seminarBagBuilder */
        $seminarBagBuilder = GeneralUtility::makeInstance(EventBagBuilder::class);

        $seminarBagBuilder->setSourcePages(
            $this->getConfValueString('pidList'),
            $this->getConfValueInteger('recursive')
        );
        $seminarBagBuilder->setOrderBy($this->getOrderByForListView());

        return $seminarBagBuilder;
    }

    /**
     * Returns a registrationBagBuilder object limited for registrations of the
     * currently logged in front-end user as attendee for the "my events" list view.
     *
     * @return RegistrationBagBuilder the registrations for the "my events" list
     */
    private function createRegistrationBagBuilder(): RegistrationBagBuilder
    {
        /** @var RegistrationBagBuilder $registrationBagBuilder */
        $registrationBagBuilder = GeneralUtility::makeInstance(RegistrationBagBuilder::class);

        /** @var \Tx_Seminars_Model_FrontEndUser $loggedInUser */
        $loggedInUser = FrontEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_FrontEndUser::class);
        $registrationBagBuilder->limitToAttendee($loggedInUser);
        $registrationBagBuilder->setOrderByEventColumn($this->getOrderByForListView());

        return $registrationBagBuilder;
    }

    /**
     * @return \Tx_Seminars_FrontEnd_RequirementsList the object to build the requirements list with
     */
    private function createRequirementsList(): \Tx_Seminars_FrontEnd_RequirementsList
    {
        /** @var \Tx_Seminars_FrontEnd_RequirementsList $list */
        $list = GeneralUtility::makeInstance(\Tx_Seminars_FrontEnd_RequirementsList::class, $this->conf, $this->cObj);
        return $list;
    }

    /**
     * Returns the ORDER BY statement for the list view.
     *
     * @return string the ORDER BY statement for the list view, may be empty
     */
    private function getOrderByForListView(): string
    {
        $orderBy = [];

        if ($this->getConfValueBoolean('sortListViewByCategory', 's_template_special')) {
            $orderBy[] = $this->orderByList['category'];
        }

        // Overwrites the default sort order with values given by the browser.
        // This happens if the user changes the sort order manually.
        if (!empty($this->piVars['sort'])) {
            list($this->internal['orderBy'], $this->internal['descFlag']) = \explode(':', $this->piVars['sort']);
        }

        if (isset($this->internal['orderBy'], $this->orderByList[$this->internal['orderBy']])) {
            $orderBy[] = $this->orderByList[$this->internal['orderBy']] . ($this->internal['descFlag'] ? ' DESC' : '');
        }

        return implode(', ', $orderBy);
    }

    /**
     * Gets the heading for a field type, automatically wrapped in a hyperlink
     * that sorts by that column if sorting by that column is available.
     *
     * @param string $fieldName key of the field type for which the heading should be retrieved, must not be empty
     *
     * @return string the heading label, may be completely wrapped in a hyperlink for sorting
     */
    public function getFieldHeader(string $fieldName): string
    {
        $label = $this->translate('label_' . $fieldName);
        if ($fieldName === 'price_regular' && $this->getConfValueBoolean('generalPriceInList', 's_template_special')) {
            $label = $this->translate('label_price_general');
        }

        // Can we sort by that field?
        if (isset($this->orderByList[$fieldName]) && $this->getConfValueBoolean('enableSortingLinksInListView')) {
            $result = $this->pi_linkTP_keepPIvars(
                $label,
                ['sort' => $fieldName . ':' . ($this->internal['descFlag'] ? 0 : 1)]
            );
        } else {
            $result = \htmlspecialchars($label, ENT_QUOTES | ENT_HTML5);
        }

        return $result;
    }

    /**
     * Returns the selector widget for the "seminars_list" view.
     *
     * @param string $whatToDisplay a string selecting the flavor of list view:
     *        either an empty string (for the default list view), the value from "what_to_display" or "other_dates"
     *
     * @return string the HTML code of the selector widget, may be empty
     */
    private function getSelectorWidgetIfNecessary(string $whatToDisplay): string
    {
        if ($whatToDisplay != 'seminar_list') {
            return '';
        }

        /** @var \Tx_Seminars_FrontEnd_SelectorWidget $selectorWidget */
        $selectorWidget = GeneralUtility::makeInstance(
            \Tx_Seminars_FrontEnd_SelectorWidget::class,
            $this->conf,
            $this->cObj
        );

        return $selectorWidget->render();
    }

    /**
     * Limits the given bag builder for additional parameters needed to build the list view.
     */
    protected function limitForAdditionalParameters(EventBagBuilder $builder): void
    {
        // Adds the query parameter that result from the user selection in the
        // selector widget (including the search form).
        if (\is_array($this->piVars['language'])) {
            $builder->limitToLanguages(
                \Tx_Seminars_FrontEnd_SelectorWidget::removeDummyOptionFromFormData($this->piVars['language'])
            );
        }

        // TODO: This needs to be changed when bug 3410 gets fixed.
        // @see https://bugs.oliverklee.com/show_bug.cgi?id=3410
        if (\is_array($this->piVars['place'])) {
            $builder->limitToPlaces(
                \Tx_Seminars_FrontEnd_SelectorWidget::removeDummyOptionFromFormData($this->piVars['place'])
            );
        } else {
            // TODO: This needs to be changed as soon as we are using the new
            // TypoScript configuration class from tx_oelib which offers a getAsIntegerArray() method.
            $builder->limitToPlaces(
                GeneralUtility::trimExplode(
                    ',',
                    $this->getConfValueString('limitListViewToPlaces', 's_listView'),
                    true
                )
            );
        }

        if (\is_array($this->piVars['city'])) {
            $builder->limitToCities(
                \Tx_Seminars_FrontEnd_SelectorWidget::removeDummyOptionFromFormData($this->piVars['city'])
            );
        }
        if (\is_array($this->piVars['country'])) {
            $builder->limitToCountries(
                \Tx_Seminars_FrontEnd_SelectorWidget::removeDummyOptionFromFormData($this->piVars['country'])
            );
        }
        if (\is_array($this->piVars['organizer'])) {
            $builder->limitToOrganizers(
                \implode(
                    ',',
                    \Tx_Seminars_FrontEnd_SelectorWidget::removeDummyOptionFromFormData($this->piVars['organizer'])
                )
            );
        } else {
            $builder->limitToOrganizers($this->getConfValueString('limitListViewToOrganizers', 's_listView'));
        }
        if (!empty($this->piVars['sword'])) {
            $builder->limitToFullTextSearch($this->piVars['sword']);
        }

        if ($this->getConfValueBoolean('hideCanceledEvents', 's_template_special')) {
            $builder->ignoreCanceledEvents();
        }

        if (isset($this->piVars['event_type']) && \is_array($this->piVars['event_type'])) {
            $builder->limitToEventTypes(
                \Tx_Seminars_FrontEnd_SelectorWidget::removeDummyOptionFromFormData($this->piVars['event_type'])
            );
        } else {
            // TODO: This needs to be changed as soon as we are using the new
            // TypoScript configuration class from tx_oelib which offers a
            // getAsIntegerArray() method.
            $builder->limitToEventTypes(
                GeneralUtility::trimExplode(
                    ',',
                    $this->getConfValueString('limitListViewToEventTypes', 's_listView'),
                    true
                )
            );
        }

        $categoryUid = isset($this->piVars['category']) ? (int)$this->piVars['category'] : 0;
        $categoryUids = isset($this->piVars['categories']) ? (array)$this->piVars['categories'] : [];
        array_walk($categoryUids, '\\intval');
        if ($categoryUid > 0) {
            $categories = (string)$categoryUid;
        } elseif (empty($categoryUids)) {
            $categories = $this->getConfValueString('limitListViewToCategories', 's_listView');
        } else {
            $categories = implode(',', $categoryUids);
        }
        $builder->limitToCategories($categories);

        if ($this->piVars['age'] > 0) {
            $builder->limitToAge($this->piVars['age']);
        }

        if ($this->piVars['price_from'] > 0) {
            $builder->limitToMinimumPrice($this->piVars['price_from']);
        }
        if ($this->piVars['price_to'] > 0) {
            $builder->limitToMaximumPrice($this->piVars['price_to']);
        }

        $this->filterByDate($builder);
    }

    /**
     * Gets the CSS classes (space-separated) for the Vacancies TD.
     *
     * @param LegacyEvent $event the current seminar object
     *
     * @return string class attribute value filled with a list a space-separated CSS classes
     */
    public function getVacanciesClasses(LegacyEvent $event): string
    {
        if (
            !$event->needsRegistration()
            || (!$event->hasDate()
                && !$this->getSharedConfiguration()->getAsBoolean('allowRegistrationForEventsWithoutDate'))
        ) {
            return '';
        }

        $classes = [];

        if ($event->hasDate() && $event->hasStarted()) {
            $classes[] = 'event-begin-date-over';
        }

        if ($event->hasVacancies()) {
            $classes[] = 'vacancies-available';
            if ($event->hasUnlimitedVacancies()) {
                $classes[] = 'vacancies-unlimited';
            } else {
                $classes[] = 'vacancies-' . $event->getVacancies();
            }
        } else {
            $classes[] = 'vacancies-0';
            if ($event->hasRegistrationQueue()) {
                $classes[] = 'has-registration-queue';
            }
        }

        // We add this class in addition to the number of vacancies so that
        // user stylesheets still can use the number of vacancies even for
        // events for which the registration deadline is over.
        if ($event->hasDate() && $event->isRegistrationDeadlineOver()) {
            $classes[] = 'registration-deadline-over';
        }

        $prefixedClasses = array_map(
            [$this, 'pi_getClassName'],
            $classes
        );

        return ' ' . implode(' ', $prefixedClasses);
    }

    /**
     * Creates the "edit", "hide" and "unhide" links for the current event in
     * the list view, depending on the logged-in FE user's permissions and the event's status.
     *
     * @return string HTML with the links, will be empty if the FE user can not edit the current event
     */
    protected function createAllEditorLinks(): string
    {
        if (!$this->mayCurrentUserEditCurrentEvent()) {
            return '';
        }

        /** @var string[] $links */
        $links = [$this->createEditLink()];

        if ($this->seminar->isPublished()) {
            $links[] = $this->createCopyLink();
            $links[] = $this->seminar->isHidden() ? $this->createUnhideLink() : $this->createHideLink();
        }

        return implode(' ', $links);
    }

    /**
     * Creates the link to the event editor for the current event.
     *
     * This function does not check the edit permissions for this event.
     *
     * @return string HTML for the link, will not be empty
     */
    protected function createEditLink(): string
    {
        return $this->cObj->getTypoLink(
            $this->translate('label_edit'),
            (string)$this->getConfValueInteger('eventEditorPID', 's_fe_editing'),
            ['tx_seminars_pi1[seminar]' => $this->seminar->getUid()]
        );
    }

    /**
     * Creates a "hide" link (to the current page) for the current event.
     *
     * This function does not check the edit permissions for this event.
     *
     * @return string HTML for the link, will not be empty
     */
    protected function createHideLink(): string
    {
        return $this->createActionLink('hide');
    }

    /**
     * Creates a "unhide" link (to the current page) for the current event.
     *
     * This function does not check the edit permissions for this event.
     *
     * @return string HTML for the link, will not be empty
     */
    protected function createUnhideLink(): string
    {
        return $this->createActionLink('unhide');
    }

    /**
     * Creates a "copy" link (to the current page) for the current event.
     *
     * This function does not check the edit permissions for this event.
     *
     * @return string HTML for the link, will not be empty
     */
    protected function createCopyLink(): string
    {
        return $this->createActionLink('copy');
    }

    /**
     * Creates a an action link (to the current page or $pageUid) for the current event.
     *
     * This function does not check the edit permissions for this event.
     *
     * @param string $action "hide", "unhide" or "copy"
     *
     * @return string HTML for the link, will not be empty
     */
    protected function createActionLink(string $action): string
    {
        $seminarUid = $this->seminar->getUid();

        $aTag = $this->cObj
            ->getTypoLink($this->translate('label_' . $action), (string)$this->getFrontEndController()->id);

        /** @var string[] $dataAttributes */
        $dataAttributes = [
            'method' => 'post',
            'post-tx_seminars_pi1-action' => $action,
            'post-tx_seminars_pi1-seminar' => $seminarUid,
        ];
        $flattenedDataAttributes = '';
        foreach ($dataAttributes as $key => $value) {
            $flattenedDataAttributes .= ' data-' . $key . '="' . $value . '"';
        }

        $replacement = preg_replace('/" *>/', '"' . $flattenedDataAttributes . '>', $aTag);

        return $replacement;
    }

    /**
     * Checks whether the currently logged-in FE user is allowed to edit the current event in the list view.
     */
    protected function mayCurrentUserEditCurrentEvent(): bool
    {
        if ($this->seminar->isOwnerFeUser()) {
            return true;
        }

        $mayManagersEditTheirEvents = $this->getConfValueBoolean('mayManagersEditTheirEvents', 's_listView');

        $isUserManager = $this->seminar->isUserVip(
            $this->getLoggedInFrontEndUserUid(),
            $this->getConfValueInteger('defaultEventVipsFeGroupID')
        );

        return $mayManagersEditTheirEvents && $isUserManager;
    }

    /**
     * Hides the columns specified in the first parameter $columnsToHide.
     *
     * @param string[] $columnsToHide the columns to hide, may be empty
     */
    protected function hideColumns(array $columnsToHide): void
    {
        $this->hideSubpartsArray($columnsToHide, 'LISTHEADER_WRAPPER');
        $this->hideSubpartsArray($columnsToHide, 'LISTITEM_WRAPPER');
    }

    /**
     * Un-hides the columns specified in the first parameter $columnsToHide.
     *
     * @param string[] $columnsToUnhide the columns to un-hide, may be empty
     */
    protected function unhideColumns(array $columnsToUnhide): void
    {
        $permanentlyHiddenColumns = GeneralUtility::trimExplode(
            ',',
            $this->getConfValueString('hideColumns', 's_template_special'),
            true
        );

        $this->unhideSubpartsArray(
            $columnsToUnhide,
            $permanentlyHiddenColumns,
            'LISTHEADER_WRAPPER'
        );
        $this->unhideSubpartsArray(
            $columnsToUnhide,
            $permanentlyHiddenColumns,
            'LISTITEM_WRAPPER'
        );
    }

    /**
     * Hides the edit column if necessary.
     *
     * It is necessary if the list to display is not the "events which I have
     * entered" list and is not the "my vip events" list and VIPs are not
     * allowed to edit their events.
     *
     * @param string $whatToDisplay a string selecting the flavor of list view:
     *        either an empty string (for the default list view), the value from "what_to_display" or "other_dates"
     */
    private function hideEditColumnIfNecessary(string $whatToDisplay): void
    {
        $mayManagersEditTheirEvents = $this->getConfValueBoolean('mayManagersEditTheirEvents', 's_listView');

        if (
            $whatToDisplay !== 'my_entered_events'
            && !($whatToDisplay === 'my_vip_events' && $mayManagersEditTheirEvents)
        ) {
            $this->hideColumns(['edit']);
        }
    }

    /**
     * Hides the column with the link to the list of registrations if online
     * registration is disabled, no user is logged in or there is no page
     * specified to link to.
     *
     * Also hides it for the "other_dates" and "events_next_day" lists.
     *
     * @param string $whatToDisplay the flavor of list view: either an empty string (for the default list view),
     *        the value from "what_to_display", or "other_dates"
     */
    public function hideListRegistrationsColumnIfNecessary(string $whatToDisplay): void
    {
        $alwaysHideInViews = ['topic_list', 'other_dates', 'events_next_day'];
        if (
            !$this->isRegistrationEnabled() || !$this->isLoggedIn()
            || \in_array($whatToDisplay, $alwaysHideInViews, true)
        ) {
            $this->hideColumns(['list_registrations']);
            return;
        }

        switch ($whatToDisplay) {
            case 'seminar_list':
                $hideIt = !$this->hasConfValueInteger('registrationsListPID')
                    && !$this->hasConfValueInteger('registrationsVipListPID');
                break;
            case 'my_events':
                $hideIt = !$this->hasConfValueInteger('registrationsListPID');
                break;
            case 'my_vip_events':
                $hideIt = !$this->hasConfValueInteger('registrationsVipListPID');
                break;
            default:
                $hideIt = false;
        }

        if ($hideIt) {
            $this->hideColumns(['list_registrations']);
        }
    }

    /**
     * Hides the registration column if online registration is disabled.
     *
     * @param string $whatToDisplay a string selecting the flavor of list view: either an empty string
     *        (for the default list view), the value from "what_to_display" or "other_dates"
     */
    private function hideRegisterColumnIfNecessary(string $whatToDisplay): void
    {
        if (
            $whatToDisplay === 'my_vip_events'
            || $whatToDisplay === 'my_entered_events'
            || !$this->isRegistrationEnabled()
        ) {
            $this->hideColumns(['registration']);
        }
    }

    /**
     * Hides the registrations column if we are not on the "my_vip_events" view
     * or the CSV export of registrations is not allowed on the "my_vip_events" view.
     *
     * @param string $whatToDisplay a string selecting the flavor of list view: either an empty string
     *        (for the default list view), the value from "what_to_display" or "other_dates"
     */
    private function hideCsvExportOfRegistrationsColumnIfNecessary(string $whatToDisplay): void
    {
        $isCsvExportOfRegistrationsInMyVipEventsViewAllowed
            = $this->getConfValueBoolean('allowCsvExportOfRegistrationsInMyVipEventsView');

        if ($whatToDisplay != 'my_vip_events' || !$isCsvExportOfRegistrationsInMyVipEventsViewAllowed) {
            $this->hideColumns(['registrations']);
        }
    }

    /**
     * Hides columns which are not needed for the "topic_list" view.
     */
    private function hideColumnsForTheTopicListView(): void
    {
        $this->hideColumns(
            [
                'uid',
                'accreditation_number',
                'speakers',
                'date',
                'time',
                'place',
                'organizers',
                'vacancies',
                'registration',
            ]
        );
    }

    /**
     * Hides the number of seats, the total price and the registration status
     * columns when we're not on the "my_events" list view.
     *
     * @param string $whatToDisplay a string selecting the flavor of list view: either an empty string
     *        (for the default list view), the value from "what_to_display" or "other_dates"
     */
    private function hideColumnsForAllViewsExceptMyEvents(string $whatToDisplay): void
    {
        if ($whatToDisplay != 'my_events') {
            $this->hideColumns(
                ['expiry', 'seats', 'total_price', 'status_registration']
            );
        }
    }

    /**
     * Hides the columns which are listed in the TypoScript setup variable "hideColumns".
     */
    private function hideColumnsForAllViewsFromTypoScriptSetup(): void
    {
        $this->hideColumns(
            GeneralUtility::trimExplode(
                ',',
                $this->getConfValueString('hideColumns', 's_template_special'),
                true
            )
        );
    }

    /**
     * Gets the link to the CSV export.
     *
     * @return string the link to the CSV export
     */
    private function getCsvExportLink(): string
    {
        return $this->cObj->typoLink(
            $this->translate('label_registrationsAsCsv'),
            [
                'parameter' => (int)$this->getFrontEndController()->id,
                'additionalParams' => GeneralUtility::implodeArrayForUrl(
                    '',
                    [
                        'type' => CsvDownloader::CSV_TYPE_NUMBER,
                        'tx_seminars_pi2' => [
                            'table' => 'tx_seminars_attendances',
                            'eventUid' => $this->seminar->getUid(),
                        ],
                    ]
                ),
            ]
        );
    }

    // Registration view functions.

    /**
     * Creates the HTML for the registration page.
     *
     * @return string HTML for the registration page
     */
    protected function createRegistrationPage(): string
    {
        $this->feuser = $this->getFrontEndController()->fe_user;

        $errorMessage = '';
        $registrationForm = '';
        $isOkay = false;

        $this->toggleEventFieldsOnRegistrationPage();

        $eventUid = (int)$this->piVars['seminar'];
        if ($this->createSeminar($eventUid)) {
            if ($this->getRegistrationManager()->canRegisterIfLoggedIn($this->seminar)) {
                if ($this->isLoggedIn()) {
                    $isOkay = true;
                } else {
                    $errorMessage = $this->getLoginLink(
                        $this->translate('message_notLoggedIn'),
                        (int)$this->getFrontEndController()->id,
                        $this->seminar->getUid()
                    );
                }
            } else {
                $errorMessage = $this->getRegistrationManager()->canRegisterIfLoggedInMessage($this->seminar);
            }
        } elseif ($this->createRegistration((int)$this->piVars['registration'])) {
            if ($this->createSeminar($this->registration->getSeminar())) {
                if ($this->seminar->isUnregistrationPossible()) {
                    $isOkay = true;
                } else {
                    $errorMessage = $this->translate('message_unregistrationNotPossible');
                }
            }
        } else {
            switch ($this->piVars['action']) {
                case 'unregister':
                    $errorMessage = $this->translate('message_notRegisteredForThisEvent');
                    break;
                case 'register':
                    // The fall-through is intended.
                default:
                    $errorMessage = $this->getRegistrationManager()->existsSeminarMessage($eventUid);
            }
        }

        if ($isOkay) {
            if (
                $this->piVars['action'] === 'unregister'
                || $this->getRegistrationManager()->userFulfillsRequirements($this->seminar)
            ) {
                $registrationForm = $this->createRegistrationForm();
            } else {
                $errorMessage = $this->translate('message_requirementsNotFulfilled');
                $requirementsList = $this->createRequirementsList();
                $requirementsList->setEvent($this->seminar);
                $requirementsList->limitToMissingRegistrations();
                $registrationForm = $requirementsList->render();
            }
        }

        $output = $this->createRegistrationHeading($errorMessage) . $registrationForm .
            $this->createRegistrationFooter();

        if ($this->isConfigurationCheckEnabled()) {
            $configurationCheck = new RegistrationFormConfigurationCheck(
                $this->buildConfigurationWithFlexForms(),
                'plugin.tx_seminars_pi1'
            );
            $configurationCheck->check();
            $output .= \implode("\n", $configurationCheck->getWarningsAsHtml());
        }

        return $output;
    }

    /**
     * Creates the registration page title and (if applicable) any error messages.
     * Data from the event will only be displayed if $this->seminar is non-NULL.
     *
     * @param string $errorMessage error message to be displayed (may be empty if there is no error)
     *
     * @return string HTML including the title and error message
     */
    protected function createRegistrationHeading(string $errorMessage): string
    {
        $this->setMarker('registration', $this->translate('label_registration'));
        $this->setMarker(
            'title',
            $this->seminar ? \htmlspecialchars($this->seminar->getTitle(), ENT_QUOTES | ENT_HTML5) : ''
        );

        if ($this->seminar && $this->seminar->hasDate()) {
            $this->setMarker('date', $this->seminar->getDate());
        } else {
            $this->hideSubparts('date', 'registration_wrapper');
        }

        $this->setMarker('uid', $this->seminar ? $this->seminar->getUid() : '');

        if (empty($errorMessage)) {
            $this->hideSubparts('error', 'wrapper');
        } else {
            $this->setMarker('error_text', $errorMessage);
        }

        $this->getRegistrationFormHookProvider()->executeHook('modifyRegistrationHeader', $this);

        return $this->getSubpart('REGISTRATION_HEAD');
    }

    /**
     * Creates the registration form.
     *
     * Note that $this->seminar must be set before calling this function and if "unregister" is the action to perform,
     * $this->registration must also be set.
     *
     * @return string HTML for the form
     */
    protected function createRegistrationForm(): string
    {
        /** @var \Tx_Seminars_FrontEnd_RegistrationForm $registrationEditor */
        $registrationEditor = GeneralUtility::makeInstance(
            \Tx_Seminars_FrontEnd_RegistrationForm::class,
            $this->conf,
            $this->cObj
        );
        $registrationEditor->setSeminar($this->seminar);
        $action = $this->piVars['action'] ?? 'register';
        $registrationEditor->setAction((string)$action);
        if ($this->piVars['action'] == 'unregister') {
            $registrationEditor->setRegistration($this->registration);
        }

        $this->getRegistrationFormHookProvider()->executeHook('modifyRegistrationForm', $this, $registrationEditor);

        return $registrationEditor->render();
    }

    /**
     * Creates the registration page footer.
     *
     * @return string HTML including the title and error message
     */
    protected function createRegistrationFooter(): string
    {
        $this->getRegistrationFormHookProvider()->executeHook('modifyRegistrationFooter', $this);

        return $this->getSubpart('REGISTRATION_BOTTOM');
    }

    /**
     * Enables/disables the display of data from event records on the registration page depending on the config variable
     * "eventFieldsOnRegistrationPage".
     */
    protected function toggleEventFieldsOnRegistrationPage(): void
    {
        $fieldsToShow = [];
        if ($this->hasConfValueString('eventFieldsOnRegistrationPage', 's_template_special')) {
            $fieldsToShow = GeneralUtility::trimExplode(
                ',',
                $this->getConfValueString('eventFieldsOnRegistrationPage', 's_template_special'),
                true
            );
        }

        // First, we have a list of all fields that are removal candidates.
        $fieldsToRemove = [
            'uid',
            'title',
            'price_regular',
            'price_special',
            'vacancies',
            'message',
        ];

        // Now iterate over the fields to show and delete them from the list
        // of items to remove.
        foreach ($fieldsToShow as $currentField) {
            $key = array_search($currentField, $fieldsToRemove, true);
            // $key will be false if the item has not been found.
            // Zero, on the other hand, is a valid key.
            if ($key !== false) {
                /** @var string|int $key */
                unset($fieldsToRemove[$key]);
            }
        }

        if (!empty($fieldsToRemove)) {
            $this->hideSubparts(implode(',', $fieldsToRemove), 'registration_wrapper');
        }
    }

    /////////////////////////////////
    // Event editor view functions.
    /////////////////////////////////

    /**
     * Checks whether logged-in FE user has access to the event editor and then
     * either creates the event editor HTML or a localized error message.
     *
     * @return string HTML for the event editor, or an error message if the
     *                FE user doesn't have access to the editor
     */
    protected function createEventEditorHtml(): string
    {
        $eventEditor = $this->createEventEditorInstance();
        $hasAccessMessage = $eventEditor->hasAccessMessage();

        if ($hasAccessMessage === '') {
            $result = $eventEditor->render();
            if ($this->isConfigurationCheckEnabled()) {
                $configurationCheck = new EventEditorConfigurationCheck(
                    $this->buildConfigurationWithFlexForms(),
                    'plugin.tx_seminars_pi1'
                );
                $configurationCheck->check();
                $result .= \implode("\n", $configurationCheck->getWarningsAsHtml());
            }
        } else {
            $result = $hasAccessMessage;
            HeaderProxyFactory::getInstance()->getHeaderProxy()->addHeader('Status: 403 Forbidden');
        }

        return $result;
    }

    /**
     * Creates an event editor instance and returns it.
     */
    protected function createEventEditorInstance(): \Tx_Seminars_FrontEnd_EventEditor
    {
        /** @var \Tx_Seminars_FrontEnd_EventEditor $eventEditor */
        $eventEditor = GeneralUtility::makeInstance(\Tx_Seminars_FrontEnd_EventEditor::class, $this->conf, $this->cObj);
        $eventEditor->setObjectUid((int)$this->piVars['seminar']);

        return $eventEditor;
    }

    /**
     * Creates the category icon IMG tag with the icon title as title attribute.
     *
     * @param string[] $iconData the filename and title of the icon in an associative array with "icon" as key
     *        for the filename and "title" as key for the icon title, the values for "title" and "icon" may be empty
     *
     * @return string the icon IMG tag with the given icon, will be empty if the category has no icon
     */
    private function createCategoryIcon(array $iconData): string
    {
        if ($iconData['icon'] == '') {
            return '';
        }

        $imageConfiguration = [
            'file' => \Tx_Seminars_FrontEnd_AbstractView::UPLOAD_PATH . $iconData['icon'],
            'titleText' => $iconData['title'],
        ];
        return $this->cObj->cObjGetSingle('IMAGE', $imageConfiguration);
    }

    /**
     * Sets a gender-specific heading for speakers, tutors, leaders or partners,
     * depending on the speakers, tutors, leaders or partners belonging to the current seminar.
     *
     * @param string $speakerType type of gender specific heading, must be 'speaker', 'tutors', 'leaders' or 'partners'
     */
    private function setGenderSpecificHeading(string $speakerType): void
    {
        if (!\in_array($speakerType, ['speakers', 'partners', 'tutors', 'leaders'])) {
            throw new \InvalidArgumentException(
                'The given speaker type "' . $speakerType .
                '" is not an allowed type. Allowed types are "speakers", "partners", "tutors" or "leaders".',
                1333293103
            );
        }

        $this->setMarker(
            'label_' . $speakerType,
            $this->translate(
                'label_' . $this->seminar->getLanguageKeySuffixForType($speakerType)
            )
        );
    }

    /**
     * Returns the data for the organizers marker.
     *
     * @return string the organizers subpart with the data of the organizers,
     *                will be empty if the event has no organizers
     */
    private function getOrganizersMarkerContent(): string
    {
        if (!$this->seminar->hasOrganizers()) {
            return '';
        }

        $result = '';
        /** @var \Tx_Seminars_OldModel_Organizer $organizer */
        foreach ($this->seminar->getOrganizerBag() as $organizer) {
            if ($organizer->hasHomepage()) {
                $organizerTitle = $this->cObj->getTypoLink(
                    \htmlspecialchars($organizer->getName(), ENT_QUOTES | ENT_HTML5),
                    $organizer->getHomepage(),
                    [],
                    $this->getConfValueString('externalLinkTarget')
                );
            } else {
                $organizerTitle = \htmlspecialchars($organizer->getName(), ENT_QUOTES | ENT_HTML5);
            }
            $this->setMarker('organizer_item_title', $organizerTitle);

            if ($organizer->hasDescription()) {
                $this->setMarker(
                    'organizer_description_content',
                    $this->pi_RTEcssText($organizer->getDescription())
                );
                $description = $this->getSubpart(
                    'ORGANIZER_DESCRIPTION_ITEM'
                );
            } else {
                $description = '';
            }
            $this->setMarker('organizer_item_description', $description);

            $result .= $this->getSubpart('ORGANIZER_LIST_ITEM');
        }

        return $result;
    }

    /**
     * Sets the marker for the registration link in the list view.
     *
     * @param string $whatToDisplay the list type which should be shown, must not be empty
     */
    private function setRegistrationLinkMarker(string $whatToDisplay): void
    {
        if ($whatToDisplay === 'my_events') {
            $this->setMarker(
                'registration',
                $this->seminar->isUnregistrationPossible()
                    ? $this->getRegistrationManager()->getLinkToUnregistrationPage($this, $this->registration) : ''
            );

            return;
        }

        $registrationLink = $this->getRegistrationManager()->getRegistrationLink($this, $this->seminar);

        if ($registrationLink === '' && !$this->getRegistrationManager()->registrationHasStarted($this->seminar)) {
            $registrationLink = sprintf(
                $this->translate('message_registrationOpensOn'),
                $this->seminar->getRegistrationBegin()
            );
        }

        $this->setMarker('registration', $registrationLink);
    }

    /**
     * Filters the given seminar bag builder to the date set in piVars.
     *
     * @param EventBagBuilder $builder the bag builder to limit by date
     */
    private function filterByDate(EventBagBuilder $builder): void
    {
        $dateFrom = $this->getTimestampFromDatePiVars('from');
        if ($dateFrom > 0) {
            $builder->limitToEarliestBeginOrEndDate($dateFrom);
        }

        $dateTo = $this->getTimestampFromDatePiVars('to');
        if ($dateTo > 0) {
            $builder->limitToLatestBeginOrEndDate($dateTo);
        }
    }

    /**
     * Retrieves the date which was sent via piVars and returns it as timestamp.
     *
     * @param string $fromOrTo must be "from" or "to", depending on the date part which should be retrieved.
     *
     * @return int the timestamp for the date set in piVars, will be 0 if no date was set
     */
    private function getTimestampFromDatePiVars(string $fromOrTo): int
    {
        if (
            $this->piVars[$fromOrTo . '_day'] == 0
            && $this->piVars[$fromOrTo . '_month'] == 0
            && $this->piVars[$fromOrTo . '_year'] == 0
        ) {
            return 0;
        }

        return ($fromOrTo == 'from') ? $this->getFromDate() : $this->getToDate();
    }

    /**
     * Gets the fromDate for the filtering of the list view, replacing empty
     * values with default values.
     *
     * Before this function is called, the piVars from_day, from_month and
     * from_year must be run through ensureIntegerPiVars.
     *
     * @return int the timestamp for the fromDate, will be > 0
     */
    private function getFromDate(): int
    {
        $day = (int)$this->piVars['from_day'] > 0 ? (int)$this->piVars['from_day'] : 1;
        $month = (int)$this->piVars['from_month'] > 0 ? (int)$this->piVars['from_month'] : 1;
        $year = (int)$this->piVars['from_year'] > 0
            ? (int)$this->piVars['from_year'] : (int)date('Y', $GLOBALS['SIM_EXEC_TIME']);

        return mktime(0, 0, 0, $month, $day, $year);
    }

    /**
     * Gets the toDate for the filtering of the list view, replacing empty
     * values with default values.
     *
     * Before this function is called, the piVars to_day, to_month and to_year
     * must be run through ensureIntegerPiVars.
     *
     * @return int the timestamp for the toDate, will be > 0
     */
    private function getToDate(): int
    {
        $longMonths = [1, 3, 5, 7, 8, 10, 12];

        $month = (int)$this->piVars['to_month'] > 0 ? (int)$this->piVars['to_month'] : 12;
        $year = (int)$this->piVars['to_year'] > 0
            ? (int)$this->piVars['to_year'] : (int)date('Y', $GLOBALS['SIM_EXEC_TIME']);

        $day = (int)$this->piVars['to_day'];

        if ($month === 2) {
            // the last day of february can be 29 or 28, depending on the year
            // so we use a behaviour of mktime which gives us the timestamp for
            // the last day of february when asking for the 0 day of march
            if (($day > 28) || ($day === 0)) {
                $day = 0;
                $month++;
            }
        } elseif (in_array($month, $longMonths, true)) {
            $day = ($day > 0) ? $day : 31;
        } else {
            $day = ($day > 0) ? $day : 30;
        }

        return mktime(23, 59, 59, $month, $day, $year);
    }

    /**
     * Hides the list view subparts for the attached files if the user is not
     * allowed to access the attached files.
     */
    private function hideFilesColumnIfUserCannotAccessFiles(): void
    {
        $limitToAttendees = $this->getConfValueBoolean('limitFileDownloadToAttendees');

        if ($limitToAttendees && !$this->isLoggedIn()) {
            $this->hideColumns(['attached_files']);
        }
    }

    /**
     * Creates the marker content for the "attached files" list item.
     *
     * @return string the marker content for the "attached files" list item, will
     *                be empty if the user does not have the permissions to
     *                download the files, or no user is logged in at the front
     *                end
     */
    private function getAttachedFilesListMarkerContent(): string
    {
        if (!$this->seminar->hasAttachedFiles()) {
            return '';
        }
        if (!$this->mayUserAccessAttachedFiles()) {
            return '';
        }

        $attachedFiles = '';
        foreach ($this->seminar->getAttachedFiles($this) as $attachedFile) {
            $this->setMarker('attached_files_single_title', $attachedFile['name']);

            $attachedFiles .= $this->getSubpart('ATTACHED_FILES_SINGLE_ITEM');
        }

        $this->setMarker('attached_files_items', $attachedFiles);

        return ($attachedFiles != '')
            ? $this->getSubpart('ATTACHED_FILES_LIST_VIEW_ITEM')
            : '';
    }

    /**
     * Checks if the current user has permission to access the attached files of an event.
     */
    private function mayUserAccessAttachedFiles(): bool
    {
        $limitToAttendees = $this->getConfValueBoolean('limitFileDownloadToAttendees');

        return !$limitToAttendees
            || ($this->isLoggedIn() && $this->seminar->isUserRegistered($this->getLoggedInFrontEndUserUid()));
    }

    /**
     * Checks if the current FE user has access to the event editor and thus may
     * see the "my entered events" list.
     */
    private function hasEventEditorAccess(): bool
    {
        $eventEditor = $this->createEventEditorInstance();

        return $eventEditor->hasAccessMessage() == '';
    }

    /**
     * Checks whether the currently logged-in user can display the current event.
     *
     * When this function is called, $this->seminar must contain a seminar, and
     * a user must be logged in at the front end.
     */
    private function canShowCurrentEvent(): bool
    {
        if (!$this->seminar->isHidden()) {
            return true;
        }
        if (!$this->seminar->hasOwner()) {
            return false;
        }

        return $this->seminar->getOwner()->getUid() === $this->getLoggedInFrontEndUserUid();
    }

    /**
     * Hides the status column for all views where it is not applicable.
     *
     * @param string $whatToDisplay the current list view, may be empty
     */
    private function hideStatusColumnIfNotUsed(string $whatToDisplay): void
    {
        if ($whatToDisplay === 'my_entered_events' || $whatToDisplay === 'my_vip_events') {
            return;
        }

        $this->hideColumns(['status']);
    }

    private function setVisibilityStatusMarker(): void
    {
        $visibilityMarker = $this->seminar->isHidden()
            ? 'pending'
            : 'published';

        $this->setMarker(
            'status',
            $this->translate('visibility_status_' . $visibilityMarker)
        );
    }

    /**
     * Limits the bag to events within the time frame set by setup.
     */
    private function limitToTimeFrameSetting(EventBagBuilder $builder): void
    {
        try {
            $builder->setTimeFrame(
                $this->getConfValueString(
                    'timeframeInList',
                    's_template_special'
                )
            );
        } catch (\Exception $exception) {
            // Ignores the exception because the user will be warned of the
            // problem by the configuration check.
        }
    }

    /**
     * Processes hide/unhide and copy events for the FE-editable events.
     */
    protected function processEventEditorActions(): void
    {
        $this->ensureIntegerPiVars(['seminar']);
        if ($this->piVars['seminar'] <= 0) {
            return;
        }

        // hasAccessMessage returns an empty string only if an event record with
        // the UID set in the piVar "seminar" exists and the currently
        // logged-in FE user is allowed to edit it.
        if ($this->createEventEditorInstance()->hasAccessMessage() !== '') {
            return;
        }

        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);
        $event = $mapper->find($this->piVars['seminar']);
        if (!$event->isPublished()) {
            return;
        }

        switch ($this->piVars['action']) {
            case 'hide':
                $this->hideEvent($event);
                break;
            case 'unhide':
                $this->unhideEvent($event);
                break;
            case 'copy':
                $this->copyEvent($event);
                break;
            default:
        }
    }

    /**
     * Marks the given event as hidden and saves it.
     */
    protected function hideEvent(\Tx_Seminars_Model_Event $event): void
    {
        $event->markAsHidden();
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);
        $mapper->save($event);

        $this->redirectToCurrentUrl();
    }

    /**
     * Marks the given event as visible and saves it.
     */
    protected function unhideEvent(\Tx_Seminars_Model_Event $event): void
    {
        $event->markAsVisible();
        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);
        $mapper->save($event);

        $this->redirectToCurrentUrl();
    }

    /**
     * Creates a hidden copy of the given and saves it.
     */
    protected function copyEvent(\Tx_Seminars_Model_Event $event): void
    {
        /** @var Collection<\Tx_Seminars_Model_Registration> $registrations */
        $registrations = new Collection();
        $copy = clone $event;
        $copy->markAsHidden();
        $copy->setRegistrations($registrations);

        $mapper = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);
        $mapper->save($copy);

        $this->redirectToCurrentUrl();
    }

    /**
     * Redirects to the current URL.
     */
    protected function redirectToCurrentUrl(): void
    {
        $currentUrl = GeneralUtility::locationHeaderUrl(GeneralUtility::getIndpEnv('REQUEST_URI'));
        HeaderProxyFactory::getInstance()->getHeaderProxy()->addHeader('Location: ' . $currentUrl);
    }

    /**
     * Creates a hyperlink to the single view page of the given event.
     *
     * @param string $linkText the link text, must not be empty
     *
     * @return string HTML  for the link to the event's single view page
     */
    public function createSingleViewLink(
        \Tx_Seminars_Model_Event $event,
        string $linkText,
        bool $htmlspecialcharLinkText = true
    ): string {
        $processedLinkText = $htmlspecialcharLinkText
            ? \htmlspecialchars($linkText, ENT_QUOTES | ENT_HTML5) : $linkText;
        $linkConditionConfiguration = $this->getConfValueString('linkToSingleView', 's_listView');
        $createLink = ($linkConditionConfiguration === 'always')
            || (($linkConditionConfiguration === 'onlyForNonEmptyDescription') && $event->hasDescription());
        if (!$createLink) {
            return $processedLinkText;
        }

        $url = $this->getLinkBuilder()->createRelativeUrlForEvent($event);
        return '<a href="' . \htmlspecialchars($url, ENT_QUOTES | ENT_HTML5) . '">' . $processedLinkText . '</a>';
    }

    protected function getLinkBuilder(): \Tx_Seminars_Service_SingleViewLinkBuilder
    {
        if ($this->linkBuilder === null) {
            /** @var \Tx_Seminars_Service_SingleViewLinkBuilder $linkBuilder */
            $linkBuilder = GeneralUtility::makeInstance(\Tx_Seminars_Service_SingleViewLinkBuilder::class);
            $this->injectLinkBuilder($linkBuilder);
        }
        $this->linkBuilder->setPlugin($this);

        return $this->linkBuilder;
    }

    public function injectLinkBuilder(\Tx_Seminars_Service_SingleViewLinkBuilder $linkBuilder): void
    {
        $this->linkBuilder = $linkBuilder;
    }

    private function buildConfigurationWithFlexForms(): Configuration
    {
        $typoScriptConfiguration = ConfigurationRegistry::get('plugin.tx_seminars_pi1');
        if (!$this->cObj instanceof ContentObjectRenderer) {
            return $typoScriptConfiguration;
        }

        $flexFormsConfiguration = new FlexformsConfiguration($this->cObj);

        return new FallbackConfiguration($flexFormsConfiguration, $typoScriptConfiguration);
    }
}
