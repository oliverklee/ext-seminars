<?php
defined('TYPO3_MODE') or die('Access denied.');

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_seminars_seminars',
    'EXT:seminars/Resources/Private/Language/locallang_csh_seminars.xlf'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'fe_groups',
    'EXT:seminars/Resources/Private/Language/locallang_csh_fe_groups.xlf'
);

if (TYPO3_MODE === 'BE') {
    $moduleConfiguration = [
        'routeTarget' => \OliverKlee\Seminars\BackEnd\Controller::class . '::mainAction',
        'access' => 'user,group',
        'name' => 'web_seminars',
        'labels' => [
            'll_ref' => 'LLL:EXT:seminars/Resources/Private/Language/BackEnd/locallang_mod.xlf',
        ],
        'icon' => 'EXT:seminars/Resources/Public/Icons/BackEndModule.gif',
    ];
    if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 8007000) {
        $moduleConfiguration['labels']['tabs_images'] = [
            'tab' => 'EXT:seminars/Resources/Public/Icons/BackEndModule.gif',
        ];
    }

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('web', 'seminars', '', '', $moduleConfiguration);
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'seminars_pi1',
    'FILE:EXT:seminars/Configuration/FlexForms/flexforms_pi1.xml'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('seminars', 'Configuration/TypoScript', 'Seminars');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    [
        'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tt_content.list_type_pi1',
        'seminars_pi1',
        'EXT:seminars/ext_icon.gif',
    ]
);
