<?php
defined('TYPO3_MODE') or die('Access denied.');

$boot = static function () {
    $tables = [
        'seminars',
        'speakers',
        'attendances',
        'sites',
        'organizers',
        'payment_method',
        'event_types',
        'checkboxes',
        'lodging',
        'foods',
        'target_groups',
        'categories',
        'skills',
    ];
    foreach ($tables as $table) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
            \sprintf("\noptions.saveDocNew.tx_seminars_%s=1\n", $table)
        );
    }

    // Adds our custom function to a hook in \TYPO3\CMS\Core\DataHandling\DataHandler
    // Used for post-validation of fields in back-end forms.
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['seminars']
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
    <INCLUDE_TYPOSCRIPT: source="FILE:EXT:seminars/Configuration/TSconfig/ContentElementWizard.txt">
'
    );

    // register the time slot wizard
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1558632705] = [
        'nodeName' => 'time_slot_wizard',
        'priority' => 70,
        'class' => \OliverKlee\Seminars\BackEnd\TimeSlotWizard::class,
    ];

    // FAL upgrade wizards
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['seminars_migrateCategoryIconsToFal']
        = \OliverKlee\Seminars\UpgradeWizards\CategoryIconToFalUpgradeWizard::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['seminars_migrateSeminarImagesToFal']
        = \OliverKlee\Seminars\UpgradeWizards\SeminarImageToFalUpgradeWizard::class;
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['seminars_migrateSeminarAttachmentsToFal']
        = \OliverKlee\Seminars\UpgradeWizards\SeminarAttachmentsToFalUpgradeWizard::class;
};

$boot();
unset($boot);
