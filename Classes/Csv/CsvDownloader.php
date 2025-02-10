<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Csv;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Interfaces\Configuration;
use OliverKlee\Seminars\Middleware\ResponseHeadersModifier;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This controller creates CSV data for registrations or events.
 *
 * @internal
 */
class CsvDownloader
{
    protected Configuration $configuration;

    public function __construct()
    {
        $this->configuration = ConfigurationRegistry::get('plugin.tx_seminars');
    }

    /**
     * Creates a CSV export.
     *
     * @return string HTML for the plugin, might be empty
     */
    public function main(): string
    {
        $pageUid = \max(0, (int)($_GET['pid'] ?? 0));
        $eventUid = \max(0, (int)($_GET['eventUid'] ?? 0));
        return $this->createAndOutputListOfRegistrations($eventUid, $pageUid);
    }

    /**
     * Creates a CSV list of registrations for the event given in $eventUid, including a heading line.
     *
     * If the seminar does not exist, an error message is returned, and an error 404 is set.
     *
     * If access is denied, an error message is returned, and an error 403 is set.
     *
     * @param int<0, max> $eventUid UID of the event for which to create the CSV list, must be >= 0
     * @param int<0, max> $pageUid
     *
     * @return string CSV list of registrations for the given seminar or an error message in case of an error
     */
    public function createAndOutputListOfRegistrations(int $eventUid = 0, int $pageUid = 0): string
    {
        $listView = GeneralUtility::makeInstance(DownloadRegistrationListView::class);

        $listView->setEventUid($eventUid);
        $listView->setPageUid($pageUid);

        $this->setContentTypeForRegistrationLists();

        return $listView->render();
    }

    /**
     * Creates a CSV list of registrations for the event with the UID given in
     * $eventUid, including a heading line.
     *
     * This function does not do any access checks.
     *
     * @param int<0, max> $eventUid UID of the event for which the registration list should be created, must be > 0
     *
     * @return string CSV list of registrations for the given seminar or an
     *                empty string if there is not event with the provided UID
     */
    public function createListOfRegistrations(int $eventUid): string
    {
        if (LegacyEvent::fromUid($eventUid, true) === null) {
            return '';
        }

        $listView = GeneralUtility::makeInstance(DownloadRegistrationListView::class);
        $listView->setEventUid($eventUid);

        return $listView->render();
    }

    /**
     * Sets the HTTP header: the content type and filename (content disposition) for registration lists.
     */
    private function setContentTypeForRegistrationLists(): void
    {
        $filename = $this->configuration->getAsString('filenameForRegistrationsCsv');

        $this->setPageTypeAndDisposition($filename);
    }

    /**
     * Sets the page's content type to CSV and the page's content disposition to the given filename.
     *
     * Adds the data directly to the page header.
     *
     * @param non-empty-string $csvFileName the name for the page which is used as storage name, must not be empty
     */
    private function setPageTypeAndDisposition(string $csvFileName): void
    {
        $responseHeaderModifier = GeneralUtility::makeInstance(ResponseHeadersModifier::class);
        $responseHeaderModifier->addOverrideHeader('Content-type', 'text/csv; header=present; charset=utf-8');
        $responseHeaderModifier->addOverrideHeader('Content-disposition', 'attachment; filename=' . $csvFileName);
    }
}
