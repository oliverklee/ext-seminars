<?php
defined('TYPO3_MODE') or die('Access denied.');

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['seminars_pi1'] = 'layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['seminars_pi1'] = 'pi_flexform';

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'seminars',
    'Pi3',
    'Seminars (Extbase)'
);


/************** Flexform: use same Flexform for piBased and Extbase **********************/
$pluginSignature = 'seminars_pi3';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:seminars/Configuration/FlexForms/flexforms_pi3.xml');
