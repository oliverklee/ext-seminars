<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Seminars\Bag\EventBag;
use OliverKlee\Seminars\BagBuilder\EventBagBuilder;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * This class creates an events list in the back end.
 */
class EventsList extends AbstractList
{
    /**
     * @var string the name of the table we're working on
     */
    protected $tableName = 'tx_seminars_seminars';

    /**
     * @var LegacyEvent the seminar which we want to list
     */
    protected $seminar;

    /**
     * @var string the path to the template file of this list
     */
    protected $templateFile = 'EXT:seminars/Resources/Private/Templates/BackEnd/EventsList.html';

    /**
     * Generates and prints out an event list.
     *
     * @return string the HTML source code of the event list
     */
    public function show(): string
    {
        $content = '';

        $this->createTableHeading();

        $builder = GeneralUtility::makeInstance(EventBagBuilder::class);
        $builder->setBackEndMode();

        $pageData = $this->page->getPageData();
        $builder->setSourcePages((string)$pageData['uid'], self::RECURSION_DEPTH);

        $seminarBag = $builder->build();
        $this->createListBody($seminarBag);

        $this->template->setMarker(
            'new_record_button',
            $this->getNewIcon((int)$pageData['uid'])
        );

        $content .= $this->template->getSubpart('SEMINARS_EVENT_LIST');

        return $content;
    }

    /**
     * Sets the labels for the heading for the events table.
     *
     * The labels are set directly in the template, so nothing is returned.
     */
    private function createTableHeading(): void
    {
        $languageService = $this->getLanguageService();

        $this->template->setMarker(
            'label_accreditation_number',
            $languageService->getLL('eventlist.accreditation_number')
        );
        $this->template->setMarker(
            'label_title',
            $languageService->getLL('eventlist.title')
        );
        $this->template->setMarker(
            'label_date',
            $languageService->getLL('eventlist.date')
        );
        $this->template->setMarker(
            'label_attendees',
            $languageService->getLL('eventlist.attendees')
        );
        $this->template->setMarker(
            'label_number_of_attendees_on_queue',
            $languageService->getLL('eventlist.attendeesOnRegistrationQueue')
        );
        $this->template->setMarker(
            'label_minimum_number_of_attendees',
            $languageService->getLL('eventlist.attendees_min')
        );
        $this->template->setMarker(
            'label_maximum_number_of_attendees',
            $languageService->getLL('eventlist.attendees_max')
        );
        $this->template->setMarker(
            'label_has_enough_attendees',
            $languageService->getLL('eventlist.enough_attendees')
        );
        $this->template->setMarker(
            'label_is_fully_booked',
            $languageService->getLL('eventlist.is_full')
        );
        $this->template->setMarker(
            'label_status',
            $languageService->getLL('eventlist_status')
        );
    }

    /**
     * Creates all table rows for the list view.
     *
     * The table rows are set directly in the template, so nothing is returned.
     */
    private function createListBody(EventBag $events): void
    {
        $tableRows = '';

        /** @var LegacyEvent $event */
        foreach ($events as $event) {
            $this->template->setMarker('uid', $event->getUid());
            $this->template->setMarker('icon', $event->getRecordIcon());
            $this->template->setMarker(
                'accreditation_number',
                \htmlspecialchars($event->getAccreditationNumber(), ENT_QUOTES | ENT_HTML5)
            );
            $this->template->setMarker(
                'title',
                \htmlspecialchars(
                    GeneralUtility::fixed_lgd_cs(
                        $event->getRealTitle(),
                        $this->getBackEndUser()->uc['titleLen']
                    ),
                    ENT_QUOTES | ENT_HTML5
                )
            );
            $this->template->setMarker(
                'date',
                ($event->hasDate() ? $event->getDate() : '')
            );
            $this->template->setMarker(
                'edit_button',
                $this->getEditIcon($event->getUid(), $event->getPageUid())
            );
            $this->template->setMarker(
                'delete_button',
                $this->getDeleteIcon($event->getUid(), $event->getPageUid())
            );
            $this->template->setMarker(
                'hide_unhide_button',
                $this->getHideUnhideIcon(
                    $event->getUid(),
                    $event->getPageUid(),
                    $event->isHidden()
                )
            );
            $this->template->setMarker(
                'number_of_attendees',
                ($event->needsRegistration() ? $event->getAttendances() : '')
            );
            $this->template->setMarker(
                'show_registrations',
                !$event->isHidden() && $event->needsRegistration() && $event->hasAttendances()
                    ? $this->createEventRegistrationsLink($event) : ''
            );
            $this->template->setMarker(
                'number_of_attendees_on_queue',
                ($event->hasRegistrationQueue()
                    ? $event->getAttendancesOnRegistrationQueue() : '')
            );
            $this->template->setMarker(
                'minimum_number_of_attendees',
                ($event->needsRegistration() ? $event->getAttendancesMin() : '')
            );
            $this->template->setMarker(
                'maximum_number_of_attendees',
                ($event->needsRegistration() ? $event->getAttendancesMax() : '')
            );
            if ($event->needsRegistration()) {
                $this->template->setMarker(
                    'has_enough_attendees',
                    $event->hasEnoughAttendances() ? $this->getLanguageService()->getLL(
                        'yes'
                    ) : $this->getLanguageService()->getLL('no')
                );
                $this->template->setMarker(
                    'is_fully_booked',
                    $event->isFull() ? $this->getLanguageService()->getLL('yes') : $this->getLanguageService(
                    )->getLL('no')
                );
            } else {
                $this->template->setMarker('has_enough_attendees', '');
                $this->template->setMarker('is_fully_booked', '');
            }
            $this->template->setMarker(
                'status',
                $this->getStatusIcon($event)
            );

            $this->setEmailButtonMarkers($event);
            $this->setCancelButtonMarkers($event);
            $this->setConfirmButtonMarkers($event);

            $tableRows .= $this->template->getSubpart('EVENT_ROW');
        }

        $this->template->setSubpart('EVENT_ROW', $tableRows);
    }

    /**
     * Returns an HTML image tag for an icon that represents the status "canceled"
     * or "confirmed". If the event's status is "planned", an empty string will be
     * returned.
     *
     * @param LegacyEvent $event the event to get the status icon for
     *
     * @return string HTML image tag, may be empty
     */
    private function getStatusIcon(LegacyEvent $event): string
    {
        if (!$event->isCanceled() && !$event->isConfirmed()) {
            return '';
        }

        if ($event->isConfirmed()) {
            $icon = 'Confirmed.png';
            $labelKey = 'eventlist_status_confirmed';
        } elseif ($event->isCanceled()) {
            $icon = 'Canceled.png';
            $labelKey = 'eventlist_status_canceled';
        } else {
            $icon = '';
            $labelKey = '';
        }
        $label = $this->getLanguageService()->getLL($labelKey);

        return '<img src="/' . PathUtility::stripPathSitePrefix(
            ExtensionManagementUtility::extPath('seminars')
        ) . 'Resources/Public/Icons/' . $icon .
            '" title="' . $label . '" alt="' . $label . '"/>';
    }

    /**
     * Sets the markers of a button for sending an e-mail to the attendees of an
     * event.
     *
     * The button will only be visible if the event has at least one registration.
     */
    private function setEmailButtonMarkers(LegacyEvent $event): void
    {
        if (!$event->hasAttendances()) {
            $this->template->hideSubpartsArray(['EMAIL_BUTTON']);
            return;
        }

        $this->template->unhideSubpartsArray(['EMAIL_BUTTON']);
        $pageData = $this->page->getPageData();

        $this->template->setMarker('uid', $event->getUid());
        $urlParameters = ['id' => (int)$pageData['uid']];
        $buttonUrl = $this->getRouteUrl(self::MODULE_NAME, $urlParameters);
        $this->template->setMarker('email_button_url', \htmlspecialchars($buttonUrl, ENT_QUOTES | ENT_HTML5));
        $this->template->setMarker(
            'label_email_button',
            $this->getLanguageService()->getLL('eventlist_button_email')
        );
    }

    /**
     * Sets the markers of a button for canceling an event. The button will only
     * be visible if
     * - the current record is either a date or single event record
     * - the event is not canceled yet
     * - the event has not started yet
     * In all other cases the corresponding subpart is hidden.
     *
     * @param LegacyEvent $event the event to get the cancel button for
     */
    private function setCancelButtonMarkers(LegacyEvent $event): void
    {
        $this->template->unhideSubpartsArray(['CANCEL_BUTTON']);
        $pageData = $this->page->getPageData();

        if (
            ($event->getRecordType() !== EventInterface::TYPE_EVENT_TOPIC)
            && !$event->isHidden() && !$event->isCanceled()
            && !$event->hasStarted()
            && $this->getBackEndUser()->check('tables_modify', $this->tableName)
            && $this->doesUserHaveAccess($event->getPageUid())
        ) {
            $this->template->setMarker('uid', $event->getUid());
            $urlParameters = ['id' => (int)$pageData['uid']];
            $buttonUrl = $this->getRouteUrl(self::MODULE_NAME, $urlParameters);
            $this->template->setMarker('cancel_button_url', \htmlspecialchars($buttonUrl, ENT_QUOTES | ENT_HTML5));
            $this->template->setMarker(
                'label_cancel_button',
                $this->getLanguageService()->getLL('eventlist_button_cancel')
            );
        } else {
            $this->template->hideSubpartsArray(['CANCEL_BUTTON']);
        }
    }

    /**
     * Sets the markers of a button for confirming an event. The button will
     * only be visible if
     * - the current record is either a date or single event record
     * - the event is not confirmed yet
     * - the event has not started yet
     * In all other cases the corresponding subpart is hidden.
     */
    private function setConfirmButtonMarkers(LegacyEvent $event): void
    {
        $this->template->unhideSubpartsArray(['CONFIRM_BUTTON']);
        $pageData = $this->page->getPageData();

        if (
            ($event->getRecordType() !== EventInterface::TYPE_EVENT_TOPIC)
            && !$event->isHidden() && !$event->isConfirmed()
            && !$event->hasStarted()
            && $this->getBackEndUser()->check('tables_modify', $this->tableName)
            && $this->doesUserHaveAccess($event->getPageUid())
        ) {
            $this->template->setMarker('uid', $event->getUid());
            $urlParameters = ['id' => (int)$pageData['uid']];
            $buttonUrl = $this->getRouteUrl(self::MODULE_NAME, $urlParameters);
            $this->template->setMarker('confirm_button_url', \htmlspecialchars($buttonUrl, ENT_QUOTES | ENT_HTML5));
            $this->template->setMarker(
                'label_confirm_button',
                $this->getLanguageService()->getLL('eventlist_button_confirm')
            );
        } else {
            $this->template->hideSubpartsArray(['CONFIRM_BUTTON']);
        }
    }

    /**
     * Returns the storage folder for new event records.
     *
     * This will be determined by the event folder storage setting of the
     * currently logged-in BE-user.
     *
     * @return int the PID for new event records, will be >= 0
     */
    protected function getNewRecordPid(): int
    {
        return $this->getLoggedInUser()->getEventFolderFromGroup();
    }

    /**
     * Creates a link to the registrations page, showing the attendees for the
     * given event UID.
     *
     * @param LegacyEvent $event
     *        the event to show the registrations for, must be >= 0
     *
     * @return string the URL to the registrations tab with the registration for
     *                the current event, will not be empty
     */
    private function createEventRegistrationsLink(LegacyEvent $event): string
    {
        $pageData = $this->page->getPageData();

        $urlParameters = ['id' => (int)$pageData['uid'], 'subModule' => '2', 'eventUid' => $event->getUid()];
        $url = $this->getRouteUrl(self::MODULE_NAME, $urlParameters);

        return '<a class="btn btn-default" href="' . \htmlspecialchars($url, ENT_QUOTES | ENT_HTML5) . '">' .
            $this->getLanguageService()->getLL('label_show_event_registrations') . '</a>';
    }
}
