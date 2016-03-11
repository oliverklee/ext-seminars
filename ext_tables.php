<?php
defined('TYPO3_MODE') or die('Access denied.');

include_once(TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/FlexForms.php');
include_once(TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'tx_seminars_modifiedSystemTables.php');

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_seminars_seminars',
    'EXT:seminars/Resources/Private/Language/locallang_csh_seminars.xml'
);

// Retrieve the path to the extension's directory.
$extRelPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY);
$extPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY);
$extIconRelPath = $extRelPath . 'Resources/Public/Icons/';
$tcaPath = $extPath . 'Configuration/TCA/tca.php';

if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModulePath(
        'web_txseminarsM2', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/BackEnd/'
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('web', 'txseminarsM2', '', $extPath . 'Classes/BackEnd/');
}

$ll = 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xml:';

$GLOBALS['TCA']['tx_seminars_test'] = array(
    'ctrl' => array(
        'title' => $ll . 'tx_seminars_test',
        'readOnly' => 1,
        'adminOnly' => 1,
        'rootLevel' => 1,
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY uid',
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime'
        ),
        'dynamicConfigFile' => $tcaPath,
        'iconfile' => $extIconRelPath . 'Test.gif',
        'searchFields' => 'title'
    )
);

$GLOBALS['TCA']['tx_seminars_seminars'] = array(
    'ctrl' => array(
        'title' => $ll . 'tx_seminars_seminars',
        'label' => 'title',
        'type' => 'object_type',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY begin_date DESC',
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime'
        ),
        'iconfile' => $extIconRelPath . 'EventComplete.gif',
        'typeicon_column' => 'object_type',
        'typeicons' => array(
            '0' => $extIconRelPath . 'EventComplete.gif',
            '1' => $extIconRelPath . 'EventTopic.gif',
            '2' => $extIconRelPath . 'EventDate.gif'
        ),
        'dynamicConfigFile' => $tcaPath,
        'dividers2tabs' => true,
        'hideAtCopy' => true,
        'requestUpdate' => 'needs_registration',
        'searchFields' => 'title,accreditation_number'
    )
);

$GLOBALS['TCA']['tx_seminars_speakers'] = array(
    'ctrl' => array(
        'title' => $ll . 'tx_seminars_speakers',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden',
        ),
        'dynamicConfigFile' => $tcaPath,
        'iconfile' => $extIconRelPath . 'Speaker.gif',
        'searchFields' => 'title'
    )
);

$GLOBALS['TCA']['tx_seminars_attendances'] = array(
    'ctrl' => array(
        'title' => $ll . 'tx_seminars_attendances',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY crdate DESC',
        'delete' => 'deleted',
        'enablecolumns' => array(
            'disabled' => 'hidden'
        ),
        'dynamicConfigFile' => $tcaPath,
        'iconfile' => $extIconRelPath . 'Registration.gif',
        'dividers2tabs' => true,
        'searchFields' => 'title'
    )
);

$GLOBALS['TCA']['tx_seminars_sites'] = array(
    'ctrl' => array(
        'title' => $ll . 'tx_seminars_sites',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'dynamicConfigFile' => $tcaPath,
        'iconfile' => $extIconRelPath . 'Place.gif',
        'searchFields' => 'title'
    )
);

$GLOBALS['TCA']['tx_seminars_organizers'] = array(
    'ctrl' => array(
        'title' => $ll . 'tx_seminars_organizers',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'dynamicConfigFile' => $tcaPath,
        'iconfile' => $extIconRelPath . 'Organizer.gif',
        'searchFields' => 'title'
    )
);

$GLOBALS['TCA']['tx_seminars_payment_methods'] = array(
    'ctrl' => array(
        'title' => $ll . 'tx_seminars_payment_methods',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'dynamicConfigFile' => $tcaPath,
        'iconfile' => $extIconRelPath . 'PaymentMethod.gif',
        'searchFields' => 'title'
    )
);

$GLOBALS['TCA']['tx_seminars_event_types'] = array(
    'ctrl' => array(
        'title' => $ll . 'tx_seminars_event_types',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'dynamicConfigFile' => $tcaPath,
        'iconfile' => $extIconRelPath . 'EventType.gif',
        'searchFields' => 'title'
    )
);

$GLOBALS['TCA']['tx_seminars_checkboxes'] = array(
    'ctrl' => array(
        'title' => $ll . 'tx_seminars_checkboxes',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'dynamicConfigFile' => $tcaPath,
        'iconfile' => $extIconRelPath . 'Checkbox.gif',
        'searchFields' => 'title'
    )
);

$GLOBALS['TCA']['tx_seminars_lodgings'] = array(
    'ctrl' => array(
        'title' => $ll . 'tx_seminars_lodgings',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'dynamicConfigFile' => $tcaPath,
        'iconfile' => $extIconRelPath . 'Lodging.gif',
        'searchFields' => 'title'
    )
);

$GLOBALS['TCA']['tx_seminars_foods'] = array(
    'ctrl' => array(
        'title' => $ll . 'tx_seminars_foods',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'dynamicConfigFile' => $tcaPath,
        'iconfile' => $extIconRelPath . 'Food.gif',
        'searchFields' => 'title'
    )
);

$GLOBALS['TCA']['tx_seminars_timeslots'] = array(
    'ctrl' => array(
        'title' => $ll . 'tx_seminars_timeslots',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'hideTable' => true,
        'iconfile' => $extIconRelPath . 'TimeSlot.gif',
        'dynamicConfigFile' => $tcaPath,
        'searchFields' => 'title'
    )
);

$GLOBALS['TCA']['tx_seminars_target_groups'] = array(
    'ctrl' => array(
        'title' => $ll . 'tx_seminars_target_groups',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'dynamicConfigFile' => $tcaPath,
        'iconfile' => $extIconRelPath . 'TargetGroup.gif',
        'searchFields' => 'title'
    )
);

$GLOBALS['TCA']['tx_seminars_categories'] = array(
    'ctrl' => array(
        'title' => $ll . 'tx_seminars_categories',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'dynamicConfigFile' => $tcaPath,
        'iconfile' => $extIconRelPath . 'Category.gif',
        'searchFields' => 'title'
    )
);

$GLOBALS['TCA']['tx_seminars_skills'] = array(
    'ctrl' => array(
        'title' => $ll . 'tx_seminars_skills',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'dynamicConfigFile' => $tcaPath,
        'iconfile' => $extIconRelPath . 'Skill.gif',
        'searchFields' => 'title'
    )
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_seminars_seminars');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToInsertRecords('tx_seminars_speakers');

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY . '_pi1']='layout,select_key,pages,recursive';

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi1'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    $_EXTKEY . '_pi1',
    'FILE:EXT:seminars/Configuration/FlexForms/flexforms_pi1.xml'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Seminars');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    array(
        $ll . 'tt_content.list_type_pi1',
        $_EXTKEY . '_pi1',
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'ext_icon.gif',
    ),
    'list_type'
);

if (TYPO3_MODE == 'BE') {
    $TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses'][Tx_Seminars_FrontEnd_WizardIcon::class]
        = TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Classes/FrontEnd/WizardIcon.php';
}
