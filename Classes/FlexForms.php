<?php
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
 * This class is needed to dynamically create the list of selectable database
 * columns for the pi1 flex forms.
 *
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_FlexForms
{
    /**
     * Returns the configuration for the flex forms field
     * "showFeUserFieldsInRegistrationsList" with the selectable database
     * columns.
     *
     * @param array[] $configuration the flex forms configuration
     *
     * @return array[] the modified flex forms configuration including the selectable database columns
     */
    public function getShowFeUserFieldsInRegistrationsList(array $configuration)
    {
        foreach ($this->getColumnsOfTable('fe_users') as $column) {
            $configuration['items'][] = array(0 => $column, 1 => $column);
        }

        return $configuration;
    }

    /**
     * Returns the configuration for the flex forms field
     * "showRegistrationFieldsInRegistrationList" with the selectable database
     * columns.
     *
     * @param array[] $configuration the flex forms configuration
     *
     * @return array[] the modified flex forms configuration including the selectable database columns
     */
    public function getShowRegistrationFieldsInRegistrationList(array $configuration)
    {
        foreach ($this->getColumnsOfTable('tx_seminars_attendances') as $column) {
            $configuration['items'][] = array(0 => $column, 1 => $column);
        }

        return $configuration;
    }

    /**
     * Returns the column names of the table given in the first parameter
     * $tableName.
     *
     * @param string $tableName the table name to get the columns for, must not be empty
     *
     * @return string[] the column names of the given table name, may not be empty
     */
    private function getColumnsOfTable($tableName)
    {
        if ($tableName == '') {
            throw new InvalidArgumentException('The first parameter $tableName must not be empty.', 1333291708);
        }

        $columns = $GLOBALS['TYPO3_DB']->admin_get_fields($tableName);

        return array_keys($columns);
    }
}
