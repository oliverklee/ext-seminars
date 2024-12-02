<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die('Access denied.');

(static function (): void {
    // This is the `AbstractPlugin`-based legacy plugin.
    ExtensionManagementUtility::addPlugin(
        [
            'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xlf:tt_content.list_type_pi1',
            'seminars_pi1',
            'EXT:seminars/Resources/Public/Icons/Extension.svg',
        ],
        'list_type',
        'seminars'
    );

    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['seminars_pi1'] = 'recursive,pages';
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['seminars_pi1'] = 'pi_flexform';

    ExtensionManagementUtility::addPiFlexFormValue(
        'seminars_pi1',
        'FILE:EXT:seminars/Configuration/FlexForms/flexforms_pi1.xml'
    );

    //
    // FE editor
    //

    // This makes the plugin selectable in the BE.
    ExtensionUtility::registerPlugin(
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
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['seminars_frontendeditor'] = 'recursive,pages';

    // These two commands add the flexform configuration for the plugin.
    // @phpstan-ignore-next-line We know that this array key exists and is an array.
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['seminars_frontendeditor'] = 'pi_flexform';
    ExtensionManagementUtility::addPiFlexFormValue(
        'seminars_frontendeditor',
        'FILE:EXT:seminars/Configuration/FlexForms/FrontEndEditor.xml'
    );

    //
    // Registration form
    //

    ExtensionUtility::registerPlugin(
        'Seminars',
        // arbitrary, but unique plugin name (not visible in the BE)
        'EventRegistration',
        // plugin title, as visible in the drop-down in the BE
        'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:plugin.eventRegistration',
        // the icon visible in the drop-down in the BE
        'EXT:seminars/Resources/Public/Icons/Extension.svg'
    );

    // This removes the default controls from the plugin.
    // @phpstan-ignore-next-line We know that this array key exists and is an array.
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['seminars_eventregistration']
        = 'recursive,pages';

    // These two commands add the flexform configuration for the plugin.
    // @phpstan-ignore-next-line We know that this array key exists and is an array.
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['seminars_eventregistration'] = 'pi_flexform';
    ExtensionManagementUtility::addPiFlexFormValue(
        'seminars_eventregistration',
        'FILE:EXT:seminars/Configuration/FlexForms/EventRegistration.xml'
    );
})();
