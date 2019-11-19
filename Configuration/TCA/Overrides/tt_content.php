<?php
defined('TYPO3_MODE') or die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    [
        'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tt_content.list_type_pi1',
        'seminars_pi1',
        'EXT:seminars/Resources/Public/Icons/Extension.svg',
    ],
    'list_type',
    'seminars'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['seminars_pi1']
    = 'layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['seminars_pi1'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'seminars_pi1',
    'FILE:EXT:seminars/Configuration/FlexForms/flexforms_pi1.xml'
);
