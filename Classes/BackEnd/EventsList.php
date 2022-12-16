<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Seminars\Bag\EventBag;
use OliverKlee\Seminars\BagBuilder\EventBagBuilder;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
                'number_of_attendees',
                ($event->needsRegistration() ? $event->getAttendances() : '')
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

            $this->setEmailButtonMarkers($event);

            $tableRows .= $this->template->getSubpart('EVENT_ROW');
        }

        $this->template->setSubpart('EVENT_ROW', $tableRows);
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
}
