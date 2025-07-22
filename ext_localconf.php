<?php

defined('TYPO3') or die('Access denied.');

(static function (): void {
    // Adds our custom function to a hook in \TYPO3\CMS\Core\DataHandling\DataHandler
    // Used for post-validation of fields in back-end forms.
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['seminars']
        = \OliverKlee\Seminars\Hooks\DataHandlerHook::class;
    // Used for keeping registrations from getting duplicated when copying event records.
    // @deprecated #1324 will be removed in seminars 6.0
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['seminars']
        = \OliverKlee\Seminars\Hooks\DataHandlerHook::class;

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43(
        'seminars',
        'Classes/FrontEnd/DefaultController.php',
        '_pi1'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        'seminars',
        'setup',
        '
        plugin.' . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getCN('seminars')
        . '_pi1.userFunc = ' . \OliverKlee\Seminars\FrontEnd\DefaultController::class . '->main
        ',
        43
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
        'seminars',
        'setup',
        '
        tt_content.shortcut.20.conf.tx_seminars_seminars = < plugin.'
        . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getCN('seminars') . '_pi1
        tt_content.shortcut.20.conf.tx_seminars_seminars.CMD = singleView
    ',
        43
    );

    $languagePrefix = 'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:schedulerTasks.';
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']
    [\OliverKlee\Seminars\SchedulerTasks\MailNotifier::class] = [
        'extension' => 'seminars',
        'title' => $languagePrefix . 'mailNotifier.name',
        'description' => $languagePrefix . 'mailNotifier.description',
        'additionalFields' => \OliverKlee\Seminars\SchedulerTasks\MailNotifierConfiguration::class,
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

    /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach ($icons as $key => $fileName) {
        $iconRegistry->registerIcon(
            $key,
            \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
            ['source' => 'EXT:seminars/Resources/Public/Icons/' . $fileName]
        );
    }

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '
    <INCLUDE_TYPOSCRIPT: source="FILE:EXT:seminars/Configuration/TsConfig/ContentElementWizard.txt">
'
    );

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['seminars_migrateSeparateBillingAddress']
        = \OliverKlee\Seminars\UpgradeWizards\SeparateBillingAddressUpgradeWizard::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['seminars_generateEventSlugs']
        = \OliverKlee\Seminars\UpgradeWizards\GenerateEventSlugsUpgradeWizard::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['seminars_removeDuplicateEventVenueRelations']
        = \OliverKlee\Seminars\UpgradeWizards\RemoveDuplicateEventVenueRelationsUpgradeWizard::class;

    // This makes the plugin available for front-end rendering.
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Seminars', // extension name, matching the PHP namespaces (but without the vendor)
        'FrontEndEditor', // arbitrary, but unique plugin name (not visible in the BE)
        // all actions
        [
            \OliverKlee\Seminars\Controller\FrontEndEditorController::class => 'index, edit, update, new, create',
        ],
        // non-cacheable actions
        [
            \OliverKlee\Seminars\Controller\FrontEndEditorController::class => 'index, edit, update, new, create',
        ]
    );

    // This makes the plugin available for front-end rendering.
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Seminars', // extension name, matching the PHP namespaces (but without the vendor)
        'EventRegistration', // arbitrary, but unique plugin name (not visible in the BE)
        // all actions
        [
            \OliverKlee\Seminars\Controller\EventRegistrationController::class
            => 'checkPrerequisites, deny, new, confirm, create, thankYou',
            \OliverKlee\Seminars\Controller\EventUnregistrationController::class
            => 'checkPrerequisites, deny, confirm, unregister, thankYou',
        ],
        // non-cacheable actions
        [
            \OliverKlee\Seminars\Controller\EventRegistrationController::class
            => 'checkPrerequisites, new, confirm, create, thankYou',
            \OliverKlee\Seminars\Controller\EventUnregistrationController::class
            => 'checkPrerequisites, confirm, unregister',
        ]
    );

    // Register the custom render types.
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1749486974] = [
        'nodeName' => 'eventDetails',
        'priority' => 30,
        'class' => \OliverKlee\Seminars\Form\Element\EventDetailsElement::class,
    ];
})();

// Ensure human-readable URLs as canonicals even if the original page does not have them.
if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('seo')) {
    $GLOBALS['TYPO3_CONF_VARS']['FE']['additionalCanonicalizedUrlParameters'] = \array_merge(
        $GLOBALS['TYPO3_CONF_VARS']['FE']['additionalCanonicalizedUrlParameters'] ?? [],
        [
            'tx_seminars_pi1[showUid]',
        ]
    );
}
