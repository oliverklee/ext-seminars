<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Csv;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Interfaces\Configuration;
use OliverKlee\Seminars\Localization\TranslateTrait;
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
    use TranslateTrait;

    /**
     * @var Configuration
     */
    protected $configuration;

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
        $table = \is_string(GeneralUtility::_GET('table')) ? GeneralUtility::_GET('table') : '';
        $pageUid = \max(0, (int)GeneralUtility::_GET('pid'));
        switch ($table) {
            case 'tx_seminars_seminars':
                // @deprecated will be removed in version 6.0.0 in #3134
                $result = $this->createAndOutputListOfEvents($pageUid);
                break;
            case 'tx_seminars_attendances':
                $eventUid = \max(0, (int)GeneralUtility::_GET('eventUid'));
                $result = $this->createAndOutputListOfRegistrations($eventUid, $pageUid);
                break;
            default:
                throw new \InvalidArgumentException(
                    // @deprecated "tx_seminars_seminars" will be removed in version 6.0.0 in #3134
                    'The parameter "table" must be set to either "tx_seminars_seminars" or "tx_seminars_attendances".',
                    1671155057
                );
        }

        return $result;
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
     * @param int $eventUid UID of the event for which the registration list should be created, must be > 0
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
     * Creates a CSV list of events for the page given in $pid.
     *
     * If the page does not exist, an error message is returned, and an error 404 is set.
     *
     * If access is denied, an error message is returned, and an error 403 is set.
     *
     * @param int $pageUid PID of the page with events for which to create the CSV list, must be > 0
     *
     * @return string CSV list of events for the given page or an error message in case of an error
     *
     * @deprecated will be removed in version 6.0.0 in #3134
     */
    public function createAndOutputListOfEvents(int $pageUid): string
    {
        if ($pageUid <= 0) {
            throw new \InvalidArgumentException('The parameter $pageUid must be > 0.', 1671155090);
        }

        $this->setContentTypeForEventLists();

        return $this->createListOfEvents($pageUid);
    }

    /**
     * Retrieves a list of events as CSV, including the header line.
     *
     * This function does not do any access checks.
     *
     * @param int $pageUid PID of the system folder from which the event records should be exported, must be > 0
     *
     * @return string CSV export of the event records on that page
     *
     * @deprecated will be removed in version 6.0.0 in #3134
     */
    public function createListOfEvents(int $pageUid): string
    {
        $eventListView = GeneralUtility::makeInstance(EventListView::class);
        $eventListView->setPageUid($pageUid);

        return $eventListView->render();
    }

    /**
     * Sets the HTTP header: the content type and filename (content disposition) for registration lists.
     */
    private function setContentTypeForRegistrationLists(): void
    {
        $this->setPageTypeAndDisposition($this->configuration->getAsString('filenameForRegistrationsCsv'));
    }

    /**
     * Sets the HTTP header: the content type and filename (content disposition) for event lists.
     *
     * @deprecated will be removed in version 6.0.0 in #3134
     */
    private function setContentTypeForEventLists(): void
    {
        $this->setPageTypeAndDisposition($this->configuration->getAsString('filenameForEventsCsv'));
    }

    /**
     * Sets the page's content type to CSV and the page's content disposition to the given filename.
     *
     * Adds the data directly to the page header.
     *
     * @param string $csvFileName the name for the page which is used as storage name, must not be empty
     */
    private function setPageTypeAndDisposition(string $csvFileName): void
    {
        $responseHeaderModifier = GeneralUtility::makeInstance(ResponseHeadersModifier::class);
        $responseHeaderModifier->addOverrideHeader('Content-type', 'text/csv; header=present; charset=utf-8');
        $responseHeaderModifier->addOverrideHeader('Content-disposition', 'attachment; filename=' . $csvFileName);
    }
}
