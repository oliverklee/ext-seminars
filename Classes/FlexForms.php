<?php

declare(strict_types=1);

/**
 * This class is needed to dynamically create the list of selectable database
 * columns for the pi1 flex forms.
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
    public function getShowFeUserFieldsInRegistrationsList(array $configuration): array
    {
        foreach ($this->getColumnsOfTable('fe_users') as $column) {
            $configuration['items'][] = [0 => $column, 1 => $column];
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
    public function getShowRegistrationFieldsInRegistrationList(array $configuration): array
    {
        foreach ($this->getColumnsOfTable('tx_seminars_attendances') as $column) {
            $configuration['items'][] = [0 => $column, 1 => $column];
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
    private function getColumnsOfTable($tableName): array
    {
        if ($tableName == '') {
            throw new \InvalidArgumentException('The first parameter $tableName must not be empty.', 1333291708);
        }

        $columns = $GLOBALS['TYPO3_DB']->admin_get_fields($tableName);

        return array_keys($columns);
    }
}
