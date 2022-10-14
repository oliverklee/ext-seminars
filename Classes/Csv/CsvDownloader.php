<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Csv;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Oelib\Interfaces\Configuration;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * This controller creates CSV data for registrations or events.
 */
class CsvDownloader
{
    /**
     * @var int
     */
    public const CSV_TYPE_NUMBER = 736;

    /**
     * @var int HTTP status code for "page not found"
     */
    private const NOT_FOUND = 404;

    /**
     * @var int HTTP status code for "access denied"
     */
    private const ACCESS_DENIED = 403;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var string the TYPO3 mode set for testing purposes
     */
    private $typo3Mode = '';

    /**
     * @var int the HTTP status code of error
     */
    private $errorType = 0;

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
        try {
            switch ((string)GeneralUtility::_GET('table')) {
                case 'tx_seminars_seminars':
                    $result = $this->createAndOutputListOfEvents((int)GeneralUtility::_GET('pid'));
                    break;
                case 'tx_seminars_attendances':
                    $result = $this->createAndOutputListOfRegistrations((int)GeneralUtility::_GET('eventUid'));
                    break;
                default:
                    $result = $this->addErrorHeaderAndReturnMessage(self::NOT_FOUND);
            }

            $resultCharset = strtolower($this->configuration->getAsString('charsetForCsv'));
            if ($resultCharset !== 'utf-8') {
                $result = (new CharsetConverter())->conv($result, 'utf-8', $resultCharset);
            }
        } catch (\Exception $exception) {
            HeaderProxyFactory::getInstance()->getHeaderProxy()->addHeader('Status: 500 Internal Server Error');
            $result = $exception->getMessage() . "\n\n" . $exception->getTraceAsString() . "\n\n";
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
     * @param int $eventUid UID of the event for which to create the CSV list, must be >= 0
     *
     * @return string CSV list of registrations for the given seminar or an error message in case of an error
     */
    public function createAndOutputListOfRegistrations(int $eventUid = 0): string
    {
        $listView = GeneralUtility::makeInstance(DownloadRegistrationListView::class);

        $pageUid = (int)GeneralUtility::_GET('pid');
        if ($eventUid > 0) {
            if (!$this->hasAccessToEventAndItsRegistrations($eventUid)) {
                return $this->addErrorHeaderAndReturnMessage($this->errorType);
            }
            $listView->setEventUid($eventUid);
        } else {
            if (!$this->canAccessRegistrationsOnPage($pageUid)) {
                return $this->addErrorHeaderAndReturnMessage(self::ACCESS_DENIED);
            }
            $listView->setPageUid($pageUid);
        }

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
     */
    public function createAndOutputListOfEvents(int $pageUid): string
    {
        if ($pageUid <= 0) {
            return $this->addErrorHeaderAndReturnMessage(self::NOT_FOUND);
        }
        if (!$this->canAccessListOfEvents($pageUid)) {
            return $this->addErrorHeaderAndReturnMessage(self::ACCESS_DENIED);
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
     */
    public function createListOfEvents(int $pageUid): string
    {
        $eventListView = GeneralUtility::makeInstance(EventListView::class);
        $eventListView->setPageUid($pageUid);

        return $eventListView->render();
    }

    /**
     * Checks whether the list of registrations is accessible, ie.
     * 1. CSV access is allowed for testing purposes, or
     * 2. the logged-in BE user has read access to the registrations table and
     *    read access to *all* pages where the registration records of the
     *    selected event are stored.
     *
     * @param int $eventUid UID of the event record for which access should be checked, must be > 0
     *
     * @return bool true if the list of registrations may be exported as CSV
     */
    protected function canAccessListOfRegistrations(int $eventUid): bool
    {
        switch ($this->getTypo3Mode()) {
            case 'BE':
                $result = GeneralUtility::makeInstance(BackEndRegistrationAccessCheck::class)->hasAccess();
                break;
            case 'FE':
                $event = GeneralUtility::makeInstance(LegacyEvent::class, $eventUid, false, true);
                GeneralUtility::makeInstance(FrontEndRegistrationAccessCheck::class)->setEvent($event);

                $result = GeneralUtility::makeInstance(FrontEndRegistrationAccessCheck::class)->hasAccess();
                break;
            default:
                $result = false;
        }

        return $result;
    }

    /**
     * Checks whether the logged-in BE user has access to the event list.
     *
     * @param int $pageUid PID of the page with events for which to check access, must be >= 0
     *
     * @return bool TRUE if the list of events may be exported as CSV, FALSE otherwise
     */
    protected function canAccessListOfEvents(int $pageUid): bool
    {
        $accessCheck = GeneralUtility::makeInstance(BackEndEventAccessCheck::class);
        $accessCheck->setPageUid($pageUid);

        return $accessCheck->hasAccess();
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
        $headerProxy = HeaderProxyFactory::getInstance()->getHeaderProxy();
        $headerProxy->addHeader(
            'Content-type: text/csv; header=present; charset=' . $this->configuration->getAsString('charsetForCsv')
        );
        $headerProxy->addHeader('Content-disposition: attachment; filename=' . $csvFileName);
    }

    /**
     * Adds a status header and returns an error message.
     *
     * @param int $errorCode
     *        the type of error message, must be ACCESS_DENIED or NOT_FOUND
     *
     * @return string the error message belonging to the error code, will not be empty
     *
     * @throws \InvalidArgumentException
     */
    private function addErrorHeaderAndReturnMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case self::ACCESS_DENIED:
                HeaderProxyFactory::getInstance()->getHeaderProxy()->addHeader('Status: 403 Forbidden');
                $result = $this->translate('message_403');
                break;
            case self::NOT_FOUND:
                HeaderProxyFactory::getInstance()->getHeaderProxy()->addHeader('Status: 404 Not Found');
                $result = $this->translate('message_404');
                break;
            default:
                throw new \InvalidArgumentException('"' . $errorCode . '" is no legal error code.', 1333292523);
        }

        return $result;
    }

    /**
     * Checks whether the currently logged-in BE-User is allowed to access the registrations records on the given page.
     *
     * @param int $pageUid PID of the page to check the access for, must be >= 0
     *
     * @return bool
     *         TRUE if the currently logged-in BE-User is allowed to access the registrations records,
     *         FALSE if the user has no access or this function is called in FE mode
     */
    private function canAccessRegistrationsOnPage(int $pageUid): bool
    {
        switch ($this->getTypo3Mode()) {
            case 'BE':
                $accessCheck = GeneralUtility::makeInstance(BackEndRegistrationAccessCheck::class);
                $accessCheck->setPageUid($pageUid);
                $result = $accessCheck->hasAccess();
                break;
            case 'FE':
                // The fall-through is intentional.
            default:
                $result = false;
        }

        return $result;
    }

    /**
     * Returns the mode currently set in TYPO3_MODE.
     *
     * @return string either "FE" or "BE" representing the TYPO3 mode
     */
    private function getTypo3Mode(): string
    {
        if ($this->typo3Mode !== '') {
            return $this->typo3Mode;
        }

        return TYPO3_MODE;
    }

    /**
     * Sets the TYPO3_MODE.
     *
     * The value is stored in the member variable $this->typo3Mode
     *
     * This function is for testing purposes only!
     *
     * @param string $typo3Mode the TYPO3_MODE to set, must be "BE" or "FE"
     */
    public function setTypo3Mode(string $typo3Mode): void
    {
        $this->typo3Mode = $typo3Mode;
    }

    /**
     * Checks whether the currently logged in BE-User has access to the given
     * event and its registrations.
     *
     * Stores the type of the error in $this->errorType
     *
     * @param int $eventUid the event to check the access for, must be >= 0 but not necessarily point
     *        to an existing event
     *
     * @return bool true if the event record exists and the BE User has
     *                 access to the registrations belonging to the event,
     *                 false otherwise
     */
    private function hasAccessToEventAndItsRegistrations(int $eventUid): bool
    {
        $result = false;

        if (LegacyEvent::fromUid($eventUid, true) === null) {
            $this->errorType = self::NOT_FOUND;
        } elseif ($this->canAccessListOfRegistrations($eventUid)) {
            $result = true;
        } else {
            $this->errorType = self::ACCESS_DENIED;
        }

        return $result;
    }

    private function translate(string $key): string
    {
        $label = LocalizationUtility::translate($key, 'seminars');

        return \is_string($label) ? $label : $key;
    }
}
