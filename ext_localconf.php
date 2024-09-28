<?php

use OliverKlee\Seminars\Controller\EventRegistrationController;
use OliverKlee\Seminars\Controller\EventUnregistrationController;
use OliverKlee\Seminars\FrontEnd\DefaultController;
use OliverKlee\Seminars\Hooks\DataHandlerHook;
use OliverKlee\Seminars\SchedulerTasks\MailNotifier;
use OliverKlee\Seminars\SchedulerTasks\MailNotifierConfiguration;
use OliverKlee\Seminars\UpgradeWizards\GenerateEventSlugsUpgradeWizard;
use OliverKlee\Seminars\UpgradeWizards\RemoveDuplicateEventVenueRelationsUpgradeWizard;
use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die('Access denied.');

(static function (): void {
    // Adds our custom function to a hook in \TYPO3\CMS\Core\DataHandling\DataHandler
    // Used for post-validation of fields in back-end forms.
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['seminars']
        = DataHandlerHook::class;
    // Used for keeping registrations from getting duplicated when copying event records.
    // @deprecated #1324 will be removed in seminars 6.0
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['seminars']
        = DataHandlerHook::class;

    ExtensionManagementUtility::addPItoST43(
        'seminars',
        'Classes/FrontEnd/DefaultController.php',
        '_pi1'
    );
    ExtensionManagementUtility::addTypoScript(
        'seminars',
        'setup',
        '
        plugin.' . ExtensionManagementUtility::getCN('seminars')
        . '_pi1.userFunc = ' . DefaultController::class . '->main
        ',
        43
    );

    ExtensionManagementUtility::addTypoScript(
        'seminars',
        'setup',
        '
        tt_content.shortcut.20.conf.tx_seminars_seminars = < plugin.'
        . ExtensionManagementUtility::getCN('seminars') . '_pi1
        tt_content.shortcut.20.conf.tx_seminars_seminars.CMD = singleView
    ',
        43
    );

    $languagePrefix = 'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:schedulerTasks.';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']
    [MailNotifier::class] = [
        'extension' => 'seminars',
        'title' => $languagePrefix . 'mailNotifier.name',
        'description' => $languagePrefix . 'mailNotifier.description',
        'additionalFields' => MailNotifierConfiguration::class,
    ];

    $icons = [
        'tx-seminars-canceled' => 'Canceled.png',
        'tx-seminars-category' => 'Category.gif',
        'tx-seminars-registration' => 'Registration.gif',
        'tx-seminars-event-complete' => 'EventComplete.gif',
        'tx-seminars-event-topic' => 'EventTopic.gif',
        'tx-seminars-event-date' => 'EventDate.gif',
        'tcarecords-tx_seminars_speakers-default' => 'Speaker.gif',
        'tcarecords-tx_seminars_organizers-default' => 'Organizer.gif',
        'tcarecords-tx_seminars_registrations-default' => 'Registration.gif',
        'ext-seminars-wizard-icon' => 'Extension.svg',
    ];

    /** @var IconRegistry $iconRegistry */
    $iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
    foreach ($icons as $key => $fileName) {
        $iconRegistry->registerIcon(
            $key,
            BitmapIconProvider::class,
            ['source' => 'EXT:seminars/Resources/Public/Icons/' . $fileName]
        );
    }

    ExtensionManagementUtility::addPageTSConfig(
        '
    <INCLUDE_TYPOSCRIPT: source="FILE:EXT:seminars/Configuration/TsConfig/ContentElementWizard.txt">
'
    );

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['seminars_generateEventSlugs']
        = GenerateEventSlugsUpgradeWizard::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['seminars_removeDuplicateEventVenueRelations']
        = RemoveDuplicateEventVenueRelationsUpgradeWizard::class;

    // This makes the plugin available for front-end rendering.
    ExtensionUtility::configurePlugin(
        'Seminars', // extension name, matching the PHP namespaces (but without the vendor)
        'EventRegistration', // arbitrary, but unique plugin name (not visible in the BE)
        // all actions
        [
            EventRegistrationController::class => 'checkPrerequisites, deny, new, confirm, create, thankYou',
            EventUnregistrationController::class => 'checkPrerequisites, deny, confirm, unregister, thankYou',
        ],
        // non-cacheable actions
        [
            EventRegistrationController::class => 'checkPrerequisites, new, confirm, create, thankYou',
            EventUnregistrationController::class => 'checkPrerequisites, confirm, unregister',
        ]
    );
})();

// Ensure human-readable URLs as canonicals even if the original page does not have them.
if (ExtensionManagementUtility::isLoaded('seo')) {
    $GLOBALS['TYPO3_CONF_VARS']['FE']['additionalCanonicalizedUrlParameters'] = \array_merge(
        $GLOBALS['TYPO3_CONF_VARS']['FE']['additionalCanonicalizedUrlParameters'] ?? [],
        [
            'tx_seminars_pi1[showUid]',
        ]
    );
}
