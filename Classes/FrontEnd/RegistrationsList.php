<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\FrontEnd;

use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Seminars\Bag\RegistrationBag;
use OliverKlee\Seminars\BagBuilder\RegistrationBagBuilder;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This class represents a list of registrations for the front end.
 */
class RegistrationsList extends AbstractView
{
    /**
     * @var LegacyEvent the seminar of which we want to list the
     *                          registrations
     */
    private $seminar;

    /**
     * The constructor.
     *
     * @param array $configuration TypoScript configuration for the plugin, may be empty
     * @param string $whatToDisplay a string selecting the flavor of the list view, either "list_registrations" or
     *        "list_vip_registrations"
     * @param int $seminarUid UID of the seminar of which we want to list the registrations, invalid UIDs will be
     *        handled later
     * @param ContentObjectRenderer $contentObjectRenderer the parent cObj, needed for the flexforms
     */
    public function __construct(
        array $configuration,
        string $whatToDisplay,
        int $seminarUid,
        ContentObjectRenderer $contentObjectRenderer
    ) {
        parent::__construct($configuration, $contentObjectRenderer);

        if (!\in_array($whatToDisplay, ['list_registrations', 'list_vip_registrations'], true)) {
            throw new \InvalidArgumentException(
                'The value "' . $whatToDisplay . '" of the first parameter $whatToDisplay is not valid.',
                1333293210
            );
        }
        $this->whatToDisplay = $whatToDisplay;

        $this->createSeminar($seminarUid);
    }

    /**
     * Creates a seminar in `$this->seminar`.
     *
     * @param int $seminarUid an event UID, invalid UIDs will be handled later
     */
    private function createSeminar(int $seminarUid): void
    {
        $this->seminar = GeneralUtility::makeInstance(LegacyEvent::class, $seminarUid);
    }

    /**
     * Creates a list of registered participants for an event.
     * If there are no registrations yet, a localized message is displayed instead.
     *
     * @return string HTML for the list, will not be empty
     */
    public function render(): string
    {
        $errorMessage = '';
        $isOkay = false;

        if ($this->seminar->comesFromDatabase()) {
            // Okay, at least the seminar UID is valid so we can show the
            // seminar title and date.
            $this->setMarker('title', \htmlspecialchars($this->seminar->getTitleAndDate(), ENT_QUOTES | ENT_HTML5));

            if (
                $this->seminar->canViewRegistrationsList(
                    $this->whatToDisplay,
                    0,
                    0,
                    $this->getConfValueInteger(
                        'defaultEventVipsFeGroupID',
                        's_template_special'
                    )
                )
            ) {
                $isOkay = true;
            } else {
                $errorMessage = $this->seminar->canViewRegistrationsListMessage(
                    $this->whatToDisplay
                );
                HeaderProxyFactory::getInstance()->getHeaderProxy()->addHeader('Status: 403 Forbidden');
            }
        } else {
            $errorMessage = $this->translate('message_wrongSeminarNumber');
            HeaderProxyFactory::getInstance()->getHeaderProxy()->addHeader('Status: 404 Not Found');
            $this->setMarker('title', '');
        }

        if ($isOkay) {
            $this->hideSubparts('error', 'wrapper');
            $this->createRegistrationsList();
        } else {
            $this->setMarker('error_text', $errorMessage);
            $this->setMarker('registrations_list_view_content', '');
        }

        $this->setMarker(
            'backlink',
            $this->cObj->getTypoLink($this->translate('label_back'), (string)$this->getConfValueInteger('listPID'))
        );

        return $this->getSubpart('REGISTRATIONS_LIST_VIEW');
    }

    /**
     * Creates the registration list (sorted by creation date) and fills in the
     * corresponding subparts.
     * If there are no registrations, a localized message is filled in instead.
     *
     * Before this function can be called, it must be ensured that $this->seminar
     * is a valid seminar object.
     */
    private function createRegistrationsList(): void
    {
        $builder = $this->createRegistrationBagBuilder();
        $builder->limitToRegular();

        $regularRegistrations = $builder->build();
        if ($regularRegistrations->isEmpty()) {
            $this->setMarker(
                'message_no_registrations',
                $this->translate('message_noRegistrations')
            );
            $content = $this->getSubpart('WRAPPER_REGISTRATIONS_LIST_MESSAGE');
        } else {
            $this->setSubpart(
                'registrations_list_table_head',
                $this->createTableHeader(),
                'wrapper'
            );

            $this->createTableBody($regularRegistrations);
            $content = $this->getSubpart('WRAPPER_REGISTRATIONS_LIST_TABLE');

            $builder = $this->createRegistrationBagBuilder();
            $builder->limitToOnQueue();

            $waitingListRegistrations = $builder->build();
            if (!$waitingListRegistrations->isEmpty()) {
                $this->createTableBody($waitingListRegistrations);
                $content .= $this->getSubpart(
                    'WRAPPER_REGISTRATIONS_LIST_WAITING_LIST'
                );
            }
        }

        $this->setMarker('registrations_list_view_content', $content);

        unset($regularRegistrations, $builder);
    }

    /**
     * Creates a registration bag builder that will find all registrations
     * (regular and on the queue) for the event in $this->seminar, ordered by
     * creation date.
     */
    private function createRegistrationBagBuilder(): RegistrationBagBuilder
    {
        $builder = GeneralUtility::makeInstance(RegistrationBagBuilder::class);
        $builder->limitToEvent($this->seminar->getUid());
        $builder->limitToExistingUsers();
        $builder->setOrderBy('crdate');

        return $builder;
    }

    /**
     * @return string the table header HTML, will not be empty
     */
    private function createTableHeader(): string
    {
        /** @var array<int, non-empty-string> $labelKeys */
        $labelKeys = [];
        foreach ($this->getFrontEndUserFields() as $field) {
            $labelKeys[] = 'label_' . $field;
        }
        foreach ($this->getRegistrationFields() as $field) {
            if ($field == 'uid') {
                $field = 'registration_' . $field;
            }
            $labelKeys[] = 'label_' . $field;
        }

        $tableHeader = '';
        foreach ($labelKeys as $labelKey) {
            $this->setMarker('registrations_list_header', $this->translate($labelKey));
            $tableHeader .= $this->getSubpart('WRAPPER_REGISTRATIONS_LIST_TABLE_HEAD_CELL');
        }

        return $tableHeader;
    }

    /**
     * Creates the table body for a list of registrations and sets the subpart in the template.
     *
     * @param RegistrationBag $registrations the registrations to list, must not be empty
     */
    private function createTableBody(RegistrationBag $registrations): void
    {
        $tableBody = '';

        /** @var LegacyRegistration $registration */
        foreach ($registrations as $registration) {
            /** @var string[] $cellContents */
            $cellContents = [];
            foreach ($this->getFrontEndUserFields() as $field) {
                $cellContents[] = $registration->getUserData($field);
            }
            foreach ($this->getRegistrationFields() as $field) {
                $cellContents[] = $registration->getRegistrationData($field);
            }

            $tableCells = '';
            foreach ($cellContents as $cellContent) {
                $this->setMarker(
                    'registrations_list_cell',
                    \htmlspecialchars($cellContent, ENT_QUOTES | ENT_HTML5)
                );
                $tableCells .= $this->getSubpart(
                    'WRAPPER_REGISTRATIONS_LIST_CELL'
                );
            }
            $this->setMarker('registrations_list_cells', $tableCells);
            $tableBody .= $this->getSubpart(
                'WRAPPER_REGISTRATIONS_LIST_ROW'
            );
        }

        $this->setMarker('registrations_list_rows', $tableBody);
    }

    /**
     * Gets the keys of the front-end user fields that should be displayed in
     * the list.
     *
     * @return array<int, non-empty-string> keys of the front-end user fields to display, might be empty
     */
    private function getFrontEndUserFields(): array
    {
        /** @var array<int, non-empty-string> $keys */
        $keys = GeneralUtility::trimExplode(
            ',',
            $this->getConfValueString('showFeUserFieldsInRegistrationsList', 's_template_special'),
            true
        );

        return $keys;
    }

    /**
     * Gets the keys of the registration fields that should be displayed in the list.
     *
     * @return array<int, non-empty-string> keys of the registration fields to display, might be empty
     */
    private function getRegistrationFields(): array
    {
        /** @var array<int, non-empty-string> $keys */
        $keys = GeneralUtility::trimExplode(
            ',',
            $this->getConfValueString('showRegistrationFieldsInRegistrationList', 's_template_special'),
            true
        );

        return $keys;
    }
}
