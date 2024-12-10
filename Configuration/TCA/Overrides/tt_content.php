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
    // Event archive
    //

    ExtensionUtility::registerPlugin(
        'Seminars',
        'EventArchive', // arbitrary, but unique plugin name (not visible in the BE)
        // plugin title, as visible in the drop-down in the BE
        'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:plugin.eventArchive',
        'EXT:seminars/Resources/Public/Icons/Extension.svg' // the icon visible in the drop-down in the BE
    );

    // This removes the default controls from the plugin.
    // @phpstan-ignore-next-line We know that this array key exists and is an array.
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['seminars_eventarchive'] = 'recursive,pages';

    //
    // Event outlook
    //

    ExtensionUtility::registerPlugin(
        'Seminars',
        'EventOutlook', // arbitrary, but unique plugin name (not visible in the BE)
        // plugin title, as visible in the drop-down in the BE
        'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:plugin.eventOutlook',
        'EXT:seminars/Resources/Public/Icons/Extension.svg' // the icon visible in the drop-down in the BE
    );

    // This removes the default controls from the plugin.
    // @phpstan-ignore-next-line We know that this array key exists and is an array.
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['seminars_eventoutlook'] = 'recursive,pages';

    //
    // Event single view
    //

    ExtensionUtility::registerPlugin(
        'Seminars',
        'EventSingleView', // arbitrary, but unique plugin name (not visible in the BE)
        // plugin title, as visible in the drop-down in the BE
        'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:plugin.eventSingleView',
        'EXT:seminars/Resources/Public/Icons/Extension.svg' // the icon visible in the drop-down in the BE
    );

    // This removes the default controls from the plugin.
    // @phpstan-ignore-next-line We know that this array key exists and is an array.
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['seminars_eventsingleview']
        = 'recursive,pages';

    //
    // Registration form
    //

    ExtensionUtility::registerPlugin(
        'Seminars',
        'EventRegistration', // arbitrary, but unique plugin name (not visible in the BE)
        // plugin title, as visible in the drop-down in the BE
        'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:plugin.eventRegistration',
        'EXT:seminars/Resources/Public/Icons/Extension.svg' // the icon visible in the drop-down in the BE
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

    //
    // FE editor
    //

    ExtensionUtility::registerPlugin(
        'Seminars',
        'FrontEndEditor', // arbitrary, but unique plugin name (not visible in the BE)
        // plugin title, as visible in the drop-down in the BE
        'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:plugin.frontEndEditor',
        'EXT:seminars/Resources/Public/Icons/Extension.svg' // the icon visible in the drop-down in the BE
    );

    // This removes the default controls from the plugin.
    // @phpstan-ignore-next-line We know that this array key exists and is an array.
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['seminars_frontendeditor']
        = 'recursive,pages';

    // These two commands add the flexform configuration for the plugin.
    // @phpstan-ignore-next-line We know that this array key exists and is an array.
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['seminars_frontendeditor'] = 'pi_flexform';
    ExtensionManagementUtility::addPiFlexFormValue(
        'seminars_frontendeditor',
        'FILE:EXT:seminars/Configuration/FlexForms/FrontEndEditor.xml'
    );
})();
