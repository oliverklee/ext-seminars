<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Csv;

/**
 * This class creates a CSV export of registrations for download.
 */
class EmailRegistrationListView extends AbstractRegistrationListView
{
    /**
     * Returns the keys of the front-end user fields to export.
     *
     * @return list<non-empty-string>
     */
    protected function getFrontEndUserFieldKeys(): array
    {
        return $this->configuration->getAsTrimmedArray('fieldsFromFeUserForEmailCsv');
    }

    /**
     * Returns the keys of the registration fields to export.
     *
     * @return list<non-empty-string>
     */
    protected function getRegistrationFieldKeys(): array
    {
        return $this->configuration->getAsTrimmedArray('fieldsFromAttendanceForEmailCsv');
    }
}
