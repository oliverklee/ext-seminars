<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\BagBuilder\RegistrationBagBuilder;
use OliverKlee\Seminars\Hooks\HookProvider;
use OliverKlee\Seminars\Hooks\Interfaces\BackendRegistrationListView;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Mapper\RegistrationMapper;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates a registration list in the back end.
 */
class RegistrationsList extends AbstractList
{
    /**
     * @var int parameter for setRegistrationTableMarkers to show the list of registrations on the queue
     */
    public const REGISTRATIONS_ON_QUEUE = 1;

    /**
     * @var int parameter for setRegistrationTableMarkers to show the list of regular registrations
     */
    public const REGULAR_REGISTRATIONS = 2;

    /**
     * @var string the name of the table we're working on
     */
    protected $tableName = 'tx_seminars_attendances';

    /**
     * @var string warnings from the registration bag configcheck
     */
    private $configCheckWarnings = '';

    /**
     * @var string the path to the template file of this list
     */
    protected $templateFile = 'EXT:seminars/Resources/Private/Templates/BackEnd/RegistrationsList.html';

    /**
     * @var int the UID of the event to show the registrations for
     */
    private $eventUid = 0;

    /**
     * @var HookProvider|null
     */
    protected $listViewHookProvider;

    /**
     * Generates and prints out a registrations list.
     *
     * @return string the HTML source code to display
     */
    public function show(): string
    {
        $content = '';

        $pageData = $this->page->getPageData();

        $languageService = $this->getLanguageService();
        $this->template->setMarker('label_attendee_full_name', $languageService->getLL('registrationlist.feuser.name'));
        $this->template->setMarker(
            'label_event_accreditation_number',
            $languageService->getLL('registrationlist.seminar.accreditation_number')
        );
        $this->template->setMarker('label_event_title', $languageService->getLL('registrationlist.seminar.title'));
        $this->template->setMarker('label_event_date', $languageService->getLL('registrationlist.seminar.date'));

        $eventUid = (int)GeneralUtility::_GP('eventUid');
        $mapper = MapperRegistry::get(EventMapper::class);
        if (($eventUid > 0) && $mapper->existsModel($eventUid)) {
            $this->eventUid = $eventUid;
            $event = $mapper->find($eventUid);
            $registrationsHeading = sprintf(
                $languageService->getLL('registrationlist.label_registrationsHeading'),
                \htmlspecialchars($event->getTitle(), ENT_QUOTES | ENT_HTML5),
                $event->getUid()
            );
            $newButton = '';
        } else {
            $registrationsHeading = '';
            $newButton = $this->getNewIcon((int)$pageData['uid']);
        }

        $areAnyRegularRegistrationsVisible = $this->setRegistrationTableMarkers(
            self::REGULAR_REGISTRATIONS
        );
        $registrationTables = $this->template->getSubpart('REGISTRATION_TABLE');
        $this->setRegistrationTableMarkers(self::REGISTRATIONS_ON_QUEUE);
        $registrationTables .= $this->template->getSubpart('REGISTRATION_TABLE');

        $this->template->setOrDeleteMarkerIfNotEmpty(
            'registrations_heading',
            $registrationsHeading,
            '',
            'wrapper'
        );
        $this->template->setMarker('new_record_button', $newButton);
        $this->template->setMarker(
            'csv_export_button',
            ($areAnyRegularRegistrationsVisible ? $this->getCsvIcon() : '')
        );
        $this->template->setMarker('complete_table', $registrationTables);

        $content .= $this->template->getSubpart('SEMINARS_REGISTRATION_LIST');
        $content .= $this->configCheckWarnings;

        return $content;
    }

    /**
     * Gets the registration table for regular attendances and attendances on
     * the registration queue.
     *
     * If an event UID > 0 in $this->eventUid is set, the registrations of this
     * event will be listed, otherwise the registrations on the current page and
     * subpages will be listed.
     *
     * @param int $registrationsToShow
     *        the switch to decide which registrations should be shown, must
     *        be either
     *        RegistrationsList::REGISTRATIONS_ON_QUEUE or
     *        RegistrationsList::REGULAR_REGISTRATIONS
     *
     * @return bool whether the generated list is non-empty
     */
    private function setRegistrationTableMarkers(int $registrationsToShow): bool
    {
        $builder = GeneralUtility::makeInstance(RegistrationBagBuilder::class);
        $pageData = $this->page->getPageData();

        switch ($registrationsToShow) {
            case self::REGISTRATIONS_ON_QUEUE:
                $builder->limitToOnQueue();
                $tableLabel = 'registrationlist.label_queueRegistrations';
                break;
            case self::REGULAR_REGISTRATIONS:
                $builder->limitToRegular();
                $tableLabel = 'registrationlist.label_regularRegistrations';
                break;
            default:
                $tableLabel = '';
        }
        if ($this->eventUid > 0) {
            $builder->limitToEvent($this->eventUid);
        } else {
            $builder->setSourcePages((string)$pageData['uid'], self::RECURSION_DEPTH);
        }

        $registrationBag = $builder->build();
        $result = !$registrationBag->isEmpty();

        $tableRows = '';
        $languageService = $this->getLanguageService();

        $mapper = MapperRegistry::get(RegistrationMapper::class);

        /** @var LegacyRegistration $registration */
        foreach ($registrationBag as $registration) {
            $registrationNew = $mapper->find($registration->getUid());

            try {
                $userName = \htmlspecialchars($registration->getUserName(), ENT_QUOTES | ENT_HTML5);
            } catch (NotFoundException $exception) {
                $userName = $languageService->getLL('registrationlist.deleted');
            }
            $event = $registration->getSeminarObject();
            if ($event->comesFromDatabase()) {
                $eventTitle = \htmlspecialchars($event->getTitle(), ENT_QUOTES | ENT_HTML5);
                $eventDate = $event->getDate();
                $accreditationNumber = \htmlspecialchars($event->getAccreditationNumber(), ENT_QUOTES | ENT_HTML5);
            } else {
                $eventTitle = $languageService->getLL('registrationlist.deleted');
                $eventDate = '';
                $accreditationNumber = '';
            }

            $this->template->setMarker('icon', $registration->getRecordIcon());
            $this->template->setMarker('attendee_full_name', $userName);
            $this->template->setMarker('event_accreditation_number', $accreditationNumber);
            $this->template->setMarker('event_title', $eventTitle);
            $this->template->setMarker('event_date', $eventDate);
            $this->template->setMarker(
                'edit_button',
                $this->getEditIcon(
                    $registration->getUid(),
                    $registration->getPageUid()
                )
            );
            $this->template->setMarker(
                'delete_button',
                $this->getDeleteIcon(
                    $registration->getUid(),
                    $registration->getPageUid()
                )
            );

            $this->getListViewHookProvider()->executeHook(
                'modifyListRow',
                $registrationNew,
                $this->template,
                $registrationsToShow
            );

            $tableRows .= $this->template->getSubpart('REGISTRATION_TABLE_ROW');
        }

        $this->template->setMarker('label_registrations', $languageService->getLL($tableLabel));
        $this->template->setMarker('number_of_registrations', $registrationBag->count());
        $this->getListViewHookProvider()->executeHook(
            'modifyListHeader',
            $registrationBag,
            $this->template,
            $registrationsToShow
        );
        $this->template->setMarker('table_header', $this->template->getSubpart('REGISTRATION_TABLE_HEADING'));
        $this->template->setMarker('table_rows', $tableRows);
        $this->getListViewHookProvider()->executeHook(
            'modifyList',
            $registrationBag,
            $this->template,
            $registrationsToShow
        );

        return $result;
    }

    /**
     * Returns the storage folder for new registration records.
     *
     * This will be determined by the registration folder storage setting of the
     * currently logged-in BE-user.
     *
     * @return int the PID for new registration records, will be >= 0
     */
    protected function getNewRecordPid(): int
    {
        return $this->getLoggedInUser()->getRegistrationFolderFromGroup();
    }

    /**
     * Returns the parameters to add to the CSV icon link.
     *
     * @return string the additional link parameters for the CSV icon link, will
     *                always start with an &amp and be htmlspecialchared, may
     *                be empty
     */
    protected function getAdditionalCsvParameters(): string
    {
        if ($this->eventUid > 0) {
            $result = '&amp;eventUid=' . $this->eventUid;
        } else {
            $result = parent::getAdditionalCsvParameters();
        }

        return $result;
    }

    /**
     * Gets the hook provider for the list view.
     *
     * @return HookProvider
     */
    protected function getListViewHookProvider(): HookProvider
    {
        if (!$this->listViewHookProvider instanceof HookProvider) {
            $this->listViewHookProvider = GeneralUtility::makeInstance(
                HookProvider::class,
                BackendRegistrationListView::class
            );
        }

        return $this->listViewHookProvider;
    }
}
