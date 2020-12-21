<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class is needed to dynamically create the list of selectable database
 * columns for the pi1 flex forms.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class FlexForms
{
    /**
     * Returns the configuration for the flex forms field
     * "showFeUserFieldsInRegistrationsList" with the selectable database columns.
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
     * "showRegistrationFieldsInRegistrationList" with the selectable database columns.
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
     * Returns the column names of the given table.
     *
     * @param string $table the table name to get the columns for, must not be empty
     *
     * @return string[] the column names of the given table name, will not be empty
     *
     * @throws \InvalidArgumentException
     */
    private function getColumnsOfTable(string $table): array
    {
        if ($table === '') {
            throw new \InvalidArgumentException('$table must not be empty.', 1333291708);
        }

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        $statement = $connection->query('SHOW FULL COLUMNS FROM `' . $table . '`');
        $columns = [];
        foreach ($statement->fetchAll() as $row) {
            $columns[] = $row['Field'];
        }

        return $columns;
    }
}
