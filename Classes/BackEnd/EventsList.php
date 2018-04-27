<?php

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates an events list in the back end.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_BackEnd_EventsList extends Tx_Seminars_BackEnd_AbstractList
{
    /**
     * @var string the name of the table we're working on
     */
    protected $tableName = 'tx_seminars_seminars';

    /**
     * @var Tx_Seminars_OldModel_Event the seminar which we want to list
     */
    protected $seminar = null;

    /**
     * @var string the path to the template file of this list
     */
    protected $templateFile = 'EXT:seminars/Resources/Private/Templates/BackEnd/EventsList.html';

    /**
     * @var Tx_Seminars_Csv_BackEndRegistrationAccessCheck
     */
    protected $accessCheck = null;

    /**
     * Frees as much memory that has been used by this object as possible.
     */
    public function __destruct()
    {
        unset($this->seminar, $this->accessCheck);

        parent::__destruct();
    }

    /**
     * Generates and prints out an event list.
     *
     * @return string the HTML source code of the event list
     */
    public function show()
    {
        $content = '';

        $this->createTableHeading();

        /** @var Tx_Seminars_BagBuilder_Event $builder */
        $builder = GeneralUtility::makeInstance(Tx_Seminars_BagBuilder_Event::class);
        $builder->setBackEndMode();

        $pageData = $this->page->getPageData();
        $builder->setSourcePages($pageData['uid'], self::RECURSION_DEPTH);

        $seminarBag = $builder->build();
        $this->createListBody($seminarBag);

        $this->template->setMarker(
            'new_record_button',
            $this->getNewIcon($pageData['uid'])
        );

        $this->template->setMarker(
            'csv_event_export_button',
            (!$seminarBag->isEmpty() ? $this->getCsvIcon() : '')
        );
        $this->template->setMarker(
            'label_print_button',
            $this->getLanguageService()->getLL('print')
        );

        $content .= $this->template->getSubpart('SEMINARS_EVENT_LIST');

        // Checks the BE configuration and the CSV export configuration.
        $content .= $seminarBag->checkConfiguration();

        return $content;
    }

    /**
     * Sets the labels for the heading for the events table.
     *
     * The labels are set directly in the template, so nothing is returned.
     *
     * @return void
     */
    private function createTableHeading()
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
     *
     * @param Tx_Seminars_Bag_Event $events the events to list
     *
     * @return void
     */
    private function createListBody(Tx_Seminars_Bag_Event $events)
    {
        $tableRows = '';

        /** @var Tx_Seminars_OldModel_Event $event */
        foreach ($events as $event) {
            $this->template->setMarker('uid', $event->getUid());
            $this->template->setMarker('icon', $event->getRecordIcon());
            $this->template->setMarker(
                'accreditation_number',
                htmlspecialchars($event->getAccreditationNumber())
            );
            $this->template->setMarker(
                'title',
                htmlspecialchars(
                    GeneralUtility::fixed_lgd_cs(
                        $event->getRealTitle(),
                        $GLOBALS['BE_USER']->uc['titleLen']
                    )
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
                'csv_registration_export_button',
                (($event->needsRegistration() && !$event->isHidden())
                    ? $this->getRegistrationsCsvIcon($event) : '')
            );
            $this->template->setMarker(
                'number_of_attendees',
                ($event->needsRegistration() ? $event->getAttendances() : '')
            );
            $this->template->setMarker(
                'show_registrations',
                (
                    (!$event->isHidden()
                    && $event->needsRegistration()
                    && $event->hasAttendances())
                    ? $this->createEventRegistrationsLink($event) : ''
                )
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
            $this->template->setMarker(
                'has_enough_attendees',
                ($event->needsRegistration()
                    ? (!$event->hasEnoughAttendances() ? $this->getLanguageService()->getLL('no') : $GLOBALS['LANG']->getLL('yes'))
                    : '')
            );
            $this->template->setMarker(
                'is_fully_booked',
                ($event->needsRegistration()
                    ? (!$event->isFull() ? $this->getLanguageService()->getLL('no') : $GLOBALS['LANG']->getLL('yes'))
                    : '')
            );
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
     * @param Tx_Seminars_OldModel_Event $event the event to get the status icon for
     *
     * @return string HTML image tag, may be empty
     */
    private function getStatusIcon(Tx_Seminars_OldModel_Event $event)
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

        return '<img src="/' . ExtensionManagementUtility::siteRelPath('seminars') . 'Resources/Public/Icons/' . $icon .
            '" title="' . $label . '" alt="' . $label . '"/>';
    }

    /**
     * Generates a linked CSV export icon for registrations from $event if that event has at least one registration and access to
     * the registration records is granted.
     *
     * @param Tx_Seminars_OldModel_Event $event the event to get the registrations CSV icon for
     *
     * @return string the HTML for the linked image (followed by a non-breaking space) or an empty string
     */
    public function getRegistrationsCsvIcon(Tx_Seminars_OldModel_Event $event)
    {
        if (!$this->getAccessCheck()->hasAccess() || !$event->hasAttendances()) {
            return '';
        }

        $pageData = $this->page->getPageData();
        $langCsv = $this->getLanguageService()->sL('LLL:EXT:lang/locallang_core.xlf:labels.csv', 1);

        $imageTag = '<img src="/' . ExtensionManagementUtility::siteRelPath('seminars') . 'Resources/Public/Icons/Csv.gif" title="' .
            $langCsv . '" alt="' . $langCsv . '" class="icon" />';

        $csvUrl = BackendUtility::getModuleUrl(
            self::MODULE_NAME,
            [
                'id' => $pageData['uid'],
                'csv' => '1',
                'tx_seminars_pi2[table]' => 'tx_seminars_attendances',
                'tx_seminars_pi2[eventUid]' => $event->getUid(),
            ]
        );

        return '<a class="btn btn-default" href="' . htmlspecialchars($csvUrl) . '">' . $imageTag . '</a>&nbsp;';
    }

    /**
     * Gets the access check instance (and creates it if needed).
     *
     * @return Tx_Seminars_Csv_BackEndRegistrationAccessCheck
     */
    protected function getAccessCheck()
    {
        if ($this->accessCheck === null) {
            $this->accessCheck = GeneralUtility::makeInstance(\Tx_Seminars_Csv_BackEndRegistrationAccessCheck::class);
        }

        return $this->accessCheck;
    }

    /**
     * Sets the markers of a button for sending an e-mail to the attendees of an
     * event.
     *
     * The button will only be visible if the event has at least one registration.
     *
     * @param Tx_Seminars_OldModel_Event $event the event to get the e-mail button for
     *
     * @return void
     */
    private function setEmailButtonMarkers(Tx_Seminars_OldModel_Event $event)
    {
        if (!$event->hasAttendances()) {
            $this->template->hideSubpartsArray(['EMAIL_BUTTON']);
            return;
        }

        $this->template->unhideSubpartsArray(['EMAIL_BUTTON']);
        $pageData = $this->page->getPageData();

        $this->template->setMarker('uid', $event->getUid());
        $buttonUrl = BackendUtility::getModuleUrl(self::MODULE_NAME, ['id' => $pageData['uid']]);
        $this->template->setMarker('email_button_url', htmlspecialchars($buttonUrl));
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
     * @param Tx_Seminars_OldModel_Event $event the event to get the cancel button for
     *
     * @return void
     */
    private function setCancelButtonMarkers(Tx_Seminars_OldModel_Event $event)
    {
        $this->template->unhideSubpartsArray(['CANCEL_BUTTON']);
        $pageData = $this->page->getPageData();

        if (($event->getRecordType() != Tx_Seminars_Model_Event::TYPE_TOPIC)
            && !$event->isHidden() && !$event->isCanceled()
            && !$event->hasStarted()
            && $GLOBALS['BE_USER']->check('tables_modify', $this->tableName)
            && $this->doesUserHaveAccess($event->getPageUid())
        ) {
            $this->template->setMarker('uid', $event->getUid());
            $buttonUrl = BackendUtility::getModuleUrl(self::MODULE_NAME, ['id' => $pageData['uid']]);
            $this->template->setMarker('cancel_button_url', htmlspecialchars($buttonUrl));
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
     *
     * @param Tx_Seminars_OldModel_Event $event the event to get the confirm button for
     *
     * @return void
     */
    private function setConfirmButtonMarkers(Tx_Seminars_OldModel_Event $event)
    {
        $this->template->unhideSubpartsArray(['CONFIRM_BUTTON']);
        $pageData = $this->page->getPageData();

        if (($event->getRecordType() != Tx_Seminars_Model_Event::TYPE_TOPIC)
            && !$event->isHidden() && !$event->isConfirmed()
            && !$event->hasStarted()
            && $GLOBALS['BE_USER']->check('tables_modify', $this->tableName)
            && $this->doesUserHaveAccess($event->getPageUid())
        ) {
            $this->template->setMarker('uid', $event->getUid());
            $buttonUrl = BackendUtility::getModuleUrl(self::MODULE_NAME, ['id' => $pageData['uid']]);
            $this->template->setMarker('confirm_button_url', htmlspecialchars($buttonUrl));
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
    protected function getNewRecordPid()
    {
        return $this->getLoggedInUser()->getEventFolderFromGroup();
    }

    /**
     * Creates a link to the registrations page, showing the attendees for the
     * given event UID.
     *
     * @param Tx_Seminars_OldModel_Event $event
     *        the event to show the registrations for, must be >= 0
     *
     * @return string the URL to the registrations tab with the registration for
     *                the current event, will not be empty
     */
    private function createEventRegistrationsLink(Tx_Seminars_OldModel_Event $event)
    {
        $pageData = $this->page->getPageData();

        $url = BackendUtility::getModuleUrl(
            self::MODULE_NAME,
            ['id' => $pageData['uid'], 'subModule' => '2', 'eventUid' => $event->getUid()]
        );
        return '<a class="btn btn-default" href="' . htmlspecialchars($url) . '">' .
            $this->getLanguageService()->getLL('label_show_event_registrations') . '</a>';
    }
}
