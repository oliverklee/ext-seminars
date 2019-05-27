<?php
defined('TYPO3_MODE') or die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '
    options.saveDocNew.tx_seminars_seminars=1
'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '
    options.saveDocNew.tx_seminars_speakers=1
'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '
    options.saveDocNew.tx_seminars_attendances=1
'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '
    options.saveDocNew.tx_seminars_sites=1
'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '
    options.saveDocNew.tx_seminars_organizers=1
'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '
    options.saveDocNew.tx_seminars_payment_methods=1
'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '
    options.saveDocNew.tx_seminars_event_types=1
'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '
    options.saveDocNew.tx_seminars_checkboxes=1
'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '
    options.saveDocNew.tx_seminars_lodgings=1
'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '
    options.saveDocNew.tx_seminars_foods=1
'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '
    options.saveDocNew.tx_seminars_target_groups=1
'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '
    options.saveDocNew.tx_seminars_categories=1
'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
    '
    options.saveDocNew.tx_seminars_skills=1
'
);

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
    plugin.' . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getCN('seminars') . '_pi1.userFunc = Tx_Seminars_FrontEnd_DefaultController->main
',
    43
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
    'seminars',
    'setup',
    '
    tt_content.shortcut.20.conf.tx_seminars_seminars = < plugin.' . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getCN(
        'seminars'
    ) . '_pi1
    tt_content.shortcut.20.conf.tx_seminars_seminars.CMD = singleView
',
    43
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\OliverKlee\Seminars\SchedulerTasks\MailNotifier::class] = [
    'extension' => 'seminars',
    'title' => 'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:schedulerTasks.mailNotifier.name',
    'description' => 'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:schedulerTasks.mailNotifier.description',
    'additionalFields' => \OliverKlee\Seminars\SchedulerTasks\MailNotifierConfiguration::class,
];

// RealURL auto-configuration
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration']['seminars']
    = 'OliverKlee\\Seminars\\RealUrl\\Configuration->addConfiguration';

/** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconProviderClass = \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class;

$iconRegistry->registerIcon(
    'tx-seminars-canceled',
    $iconProviderClass,
    ['source' => 'EXT:seminars/Resources/Public/Icons/Canceled.png']
);

$iconRegistry->registerIcon(
    'tx-seminars-category',
    $iconProviderClass,
    ['source' => 'EXT:seminars/Resources/Public/Icons/Category.gif']
);

$iconRegistry->registerIcon(
    'tx-seminars-registration',
    $iconProviderClass,
    ['source' => 'EXT:seminars/Resources/Public/Icons/Registration.gif']
);

$iconRegistry->registerIcon(
    'tx-seminars-event-complete',
    $iconProviderClass,
    ['source' => 'EXT:seminars/Resources/Public/Icons/EventComplete.gif']
);

$iconRegistry->registerIcon(
    'tx-seminars-event-topic',
    $iconProviderClass,
    ['source' => 'EXT:seminars/Resources/Public/Icons/EventTopic.gif']
);

$iconRegistry->registerIcon(
    'tx-seminars-event-date',
    $iconProviderClass,
    ['source' => 'EXT:seminars/Resources/Public/Icons/EventDate.gif']
);

$iconRegistry->registerIcon(
    'tcarecords-tx_seminars_speakers-default',
    $iconProviderClass,
    ['source' => 'EXT:seminars/Resources/Public/Icons/Speaker.gif']
);

$iconRegistry->registerIcon(
    'tcarecords-tx_seminars_organizers-default',
    $iconProviderClass,
    ['source' => 'EXT:seminars/Resources/Public/Icons/Organizer.gif']
);

$iconRegistry->registerIcon(
    'tcarecords-tx_seminars_registrations-default',
    $iconProviderClass,
    ['source' => 'EXT:seminars/Resources/Public/Icons/Registration.gif']
);

$iconRegistry->registerIcon(
    'ext-seminars-wizard-icon',
    $iconProviderClass,
    ['source' => 'EXT:seminars/ext_icon.svg']
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
    '
    <INCLUDE_TYPOSCRIPT: source="FILE:EXT:seminars/Configuration/TSconfig/ContentElementWizard.txt">
'
);
