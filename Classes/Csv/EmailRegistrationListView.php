<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Csv;

/**
 * This class creates a CSV export of registrations for download.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class EmailRegistrationListView extends \Tx_Seminars_Csv_AbstractRegistrationListView
{
    /**
     * Returns the keys of the front-end user fields to export.
     *
     * @return string[]
     */
    protected function getFrontEndUserFieldKeys(): array
    {
        return $this->configuration->getAsTrimmedArray('fieldsFromFeUserForEmailCsv');
    }

    /**
     * Returns the keys of the registration fields to export.
     *
     * @return string[]
     */
    protected function getRegistrationFieldKeys(): array
    {
        return $this->configuration->getAsTrimmedArray('fieldsFromAttendanceForEmailCsv');
    }

    /**
     * Checks whether the export should also contain registrations that are on the queue.
     *
     * @return bool
     */
    protected function shouldAlsoContainRegistrationsOnQueue(): bool
    {
        return $this->configuration->getAsBoolean('showAttendancesOnRegistrationQueueInEmailCsv');
    }
}
