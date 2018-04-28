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

$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('seminars');

if (TYPO3_MODE === 'BE'
    && \TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) <= 8000000) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModulePath('web_txseminarsM2', $extPath . 'Classes/BackEnd/');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('web', 'txseminarsM2', '', $extPath . 'Classes/BackEnd/');
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
    ],
    'list_type'
);

if (TYPO3_MODE === 'BE') {
    $TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses'][Tx_Seminars_FrontEnd_WizardIcon::class]
        = TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('seminars') . 'Classes/FrontEnd/WizardIcon.php';
}
