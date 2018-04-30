<?php
namespace OliverKlee\Seminars\BackEnd;

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
        $parameters['items'] = [['', '']];

        $table = 'static_languages';
        $titleField = 'lg_name_local';
        $keyField = 'lg_iso_2';
        $allFields = $keyField . ', ' . $titleField;

        $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            $allFields,
            $table,
            '1 = 1' . BackendUtility::deleteClause($table),
            '',
            $titleField
        );
        /** @var string[] $row */
        foreach ($rows as $row) {
            $parameters['items'][] = [$row[$titleField], $row[$keyField]];
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
        $parameters['items'] = [['', '']];

        $table = 'static_countries';
        $titleField = 'cn_short_local';
        $keyField = 'cn_iso_2';
        $allFields = $keyField . ', ' . $titleField;

        $rows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            $allFields,
            $table,
            '1 = 1' . BackendUtility::deleteClause($table),
            '',
            $titleField
        );
        /** @var string[] $row */
        foreach ($rows as $row) {
            $parameters['items'][] = [$row[$titleField], $row[$keyField]];
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
                'icon' => 'actions-open',
                'JSopenParams' => 'height=480,width=640,status=0,menubar=0,scrollbars=1',
            ],
            'add' => [
                'type' => 'script',
                'title' => 'Create new entry',
                'icon' => 'actions-add',
                'params' => [
                    'table' => '###TABLE###',
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
                'icon' => 'actions-system-list-open',
                'params' => [
                    'table' => '###TABLE###',
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
