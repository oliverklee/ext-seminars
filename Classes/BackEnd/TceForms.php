<?php
namespace OliverKlee\Seminars\BackEnd;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * This class is provides some helper functions to create parts of the TCA/TCE dynamically.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Stefano Kowalke <blueduck@mailbox.org>
 */
class TceForms
{
    /**
     * Creates the values for a language selector in the TCA, using the alpha 2 codes as array keys.
     *
     * @param array[] $parameters
     *
     * @return void
     */
    public function createLanguageSelector(array &$parameters)
    {
        $parameters['items'] = array(array('', ''));

        $table = 'static_languages';
        $titleField = 'lg_name_local';
        $keyField = 'lg_iso_2';
        $allFields = $keyField . ', ' . $titleField;

        $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($allFields, $table, '1 = 1' . BackendUtility::deleteClause($table), '', $titleField);
        /** @var string[] $row */
        foreach ($rows as $row) {
            $parameters['items'][] = array($row[$titleField], $row[$keyField]);
        }
    }

    /**
     * Creates the values for a country selector in the TCA, using the alpha 2 codes as array keys.
     *
     * @param array[] $parameters
     *
     * @return void
     */
    public function createCountrySelector(array &$parameters)
    {
        $parameters['items'] = array(array('', ''));

        $table = 'static_countries';
        $titleField = 'cn_short_local';
        $keyField = 'cn_iso_2';
        $allFields = $keyField . ', ' . $titleField;

        $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($allFields, $table, '1 = 1' . BackendUtility::deleteClause($table), '', $titleField);
        /** @var string[] $row */
        foreach ($rows as $row) {
            $parameters['items'][] = array($row[$titleField], $row[$keyField]);
        }
    }

    /**
     * Replaces the tables markers for the add and list wizard with the given
     * table name. It's mainly used to simplify the maintaining of the wizard
     * code (equals in more than 90%) and to get some flexibility.
     *
     * @param mixed[][] $array wizards array with the table markers
     * @param string $table name of the real database table (e.g. "tx_seminars_seminars")
     *
     * @return mixed[][] wizards array with replaced table markers
     */
    public static function replaceTables(array $array, $table)
    {
        $array['add']['params']['table'] =
            str_replace('###TABLE###', $table, $array['add']['params']['table']);
        $array['list']['params']['table'] =
            str_replace('###TABLE###', $table, $array['list']['params']['table']);

        return $array;
    }

    /**
     * Returns the path to the DB local lang file.
     *
     * @return string
     */
    public static function getPathToDbLL()
    {
        return 'LLL:EXT:seminars/Resources/Private/Language/locallang_db.xml:';
    }

    /**
     * Gets the wizard configuration.
     *
     * @return mixed[]
     */
    public static function getWizardConfiguration()
    {
        $wizard = [
            '_PADDING' => 5,
            '_VERTICAL' => 5,
            'edit' => [
                'type' => 'popup',
                'title' => 'Edit entry',
                'module' => [
                    'name' => 'wizard_edit',
                ],
                'popup_onlyOpenIfSelected' => 1,
                'icon' => 'edit2.gif',
                'JSopenParams' => 'height=480,width=640,status=0,menubar=0,scrollbars=1',
            ],
            'add' => [
                'type' => 'script',
                'title' => 'Create new entry',
                'icon' => 'add.gif',
                'params' => [
                    'table'=>'###TABLE###',
                    'pid' => '###CURRENT_PID###',
                    'setValue' => 'prepend',
                ],
                'module' => [
                    'name' => 'wizard_add',
                ],
            ],
        ];

        if (self::getSelectType() === 'select') {
            $wizard['list'] = [
                'type' => 'popup',
                'title' => 'List entries',
                'icon' => 'list.gif',
                'params' => [
                    'table'=>'###TABLE###',
                    'pid' => '###CURRENT_PID###',
                ],
                'module' => [
                    'name' => 'wizard_list',
                ],
                'JSopenParams' => 'height=480,width=640,status=0,menubar=0,scrollbars=1',
            ];
        }

        return $wizard;
    }


    /**
     * Gets the select type.
     *
     * @return string
     */
    public static function getSelectType()
    {
        $globalConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['seminars']);
        $usePageBrowser = (bool)$globalConfiguration['usePageBrowser'];

        return $usePageBrowser ? 'group' : 'select';
    }
}
