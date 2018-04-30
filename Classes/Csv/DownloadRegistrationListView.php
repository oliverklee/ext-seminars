<?php

/**
 * This class creates a CSV export of registrations for download.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Csv_DownloadRegistrationListView extends \Tx_Seminars_Csv_AbstractRegistrationListView
{
    /**
     * Returns the keys of the front-end user fields to export.
     *
     * @return string[]
     */
    protected function getFrontEndUserFieldKeys()
    {
        return $this->configuration->getAsTrimmedArray('fieldsFromFeUserForCsv');
    }

    /**
     * Returns the keys of the registration fields to export.
     *
     * @return string[]
     */
    protected function getRegistrationFieldKeys()
    {
        return $this->configuration->getAsTrimmedArray('fieldsFromAttendanceForCsv');
    }

    /**
     * Checks whether the export should also contain registrations that are on the queue.
     *
     * @return bool
     */
    protected function shouldAlsoContainRegistrationsOnQueue()
    {
        return $this->configuration->getAsBoolean('showAttendancesOnRegistrationQueueInCSV');
    }
}
