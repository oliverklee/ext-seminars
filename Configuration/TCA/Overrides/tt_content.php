<?php

defined('TYPO3_MODE') or die('Access denied.');

(static function (): void {
    // This is the `AbstractPlugin`-based legacy plugin.
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

    $typo3Version = new \TYPO3\CMS\Core\Information\Typo3Version();
    if ($typo3Version->getMajorVersion() >= 10) {
        // This makes the plugin selectable in the BE.
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'Seminars',
            // arbitrary, but unique plugin name (not visible in the BE)
            'FrontEndEditor',
            // plugin title, as visible in the drop-down in the BE
            'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:plugin.frontEndEditor',
            // the icon visible in the drop-down in the BE
            'EXT:seminars/Resources/Public/Icons/Extension.svg'
        );

        // This removes the default controls from the plugin.
        // @phpstan-ignore-next-line We know that this array key exists and is an array.
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['seminars_frontendeditor']
            = 'recursive,select_key,pages';
    }
})();
