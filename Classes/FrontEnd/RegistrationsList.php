<?php

declare(strict_types=1);

use OliverKlee\Oelib\Http\HeaderProxyFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This class represents a list of registrations for the front end.
 */
class Tx_Seminars_FrontEnd_RegistrationsList extends \Tx_Seminars_FrontEnd_AbstractView
{
    /**
     * @var \Tx_Seminars_OldModel_Event the seminar of which we want to list the
     *                          registrations
     */
    private $seminar = null;

    /**
     * The constructor.
     *
     * @param array $configuration
     *        TypoScript configuration for the plugin, may be empty
     * @param string $whatToDisplay
     *        a string selecting the flavor of the list view, either "list_registrations" or "list_vip_registrations"
     * @param int $seminarUid
     *        UID of the seminar of which we want to list the registrations, invalid UIDs will be handled later
     * @param ContentObjectRenderer $contentObjectRenderer
     *        the parent cObj, needed for the flexforms
     */
    public function __construct(
        array $configuration,
        string $whatToDisplay,
        int $seminarUid,
        ContentObjectRenderer $contentObjectRenderer
    ) {
        if (
            ($whatToDisplay != 'list_registrations')
            && ($whatToDisplay != 'list_vip_registrations')
        ) {
            throw new \InvalidArgumentException(
                'The value "' . $whatToDisplay . '" of the first parameter $whatToDisplay is not valid.',
                1333293210
            );
        }

        $this->whatToDisplay = $whatToDisplay;

        parent::__construct($configuration, $contentObjectRenderer);

        $this->createSeminar($seminarUid);
    }

    /**
     * Creates a seminar in $this->seminar.
     *
     * @param int $seminarUid an event UID, invalid UIDs will be handled later
     *
     * @return void
     */
    private function createSeminar(int $seminarUid)
    {
        $this->seminar = GeneralUtility::makeInstance(\Tx_Seminars_OldModel_Event::class, $seminarUid);
    }

    /**
     * Creates a list of registered participants for an event.
     * If there are no registrations yet, a localized message is displayed instead.
     *
     * @return string HTML code for the list, will not be empty
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

        $result = $this->getSubpart('REGISTRATIONS_LIST_VIEW');

        $this->checkConfiguration();
        $result .= $this->getWrappedConfigCheckMessage();

        return $result;
    }

    /**
     * Creates the registration list (sorted by creation date) and fills in the
     * corresponding subparts.
     * If there are no registrations, a localized message is filled in instead.
     *
     * Before this function can be called, it must be ensured that $this->seminar
     * is a valid seminar object.
     *
     * @return void
     */
    private function createRegistrationsList()
    {
        $builder = $this->createRegistrationBagBuilder();
        $builder->limitToRegular();

        /** @var \Tx_Seminars_Bag_Registration $regularRegistrations */
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

            /** @var \Tx_Seminars_Bag_Registration $waitingListRegistrations */
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
     *
     * @return \Tx_Seminars_BagBuilder_Registration the bag builder
     */
    private function createRegistrationBagBuilder(): \Tx_Seminars_BagBuilder_Registration
    {
        /** @var \Tx_Seminars_BagBuilder_Registration $builder */
        $builder = GeneralUtility::makeInstance(\Tx_Seminars_BagBuilder_Registration::class);
        $builder->limitToEvent($this->seminar->getUid());
        $builder->limitToExistingUsers();
        $builder->setOrderBy('crdate');

        return $builder;
    }

    /**
     * Creates the table header.
     *
     * @return string the table header HTML, will not be empty
     */
    private function createTableHeader(): string
    {
        /** @var string[] $labelKeys */
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
            $this->setMarker(
                'registrations_list_header',
                $this->translate($labelKey)
            );
            $tableHeader .= $this->getSubpart(
                'WRAPPER_REGISTRATIONS_LIST_TABLE_HEAD_CELL'
            );
        }

        return $tableHeader;
    }

    /**
     * Creates the table body for a list of registrations and sets the subpart
     * in the template.
     *
     * @param \Tx_Seminars_Bag_Registration $registrations
     *        the registrations to list, must not be empty
     *
     * @return void
     */
    private function createTableBody(\Tx_Seminars_Bag_Registration $registrations)
    {
        $tableBody = '';

        /** @var \Tx_Seminars_OldModel_Registration $registration */
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
     * @return string[] keys of the front-end user fields to display, might be empty
     */
    private function getFrontEndUserFields(): array
    {
        return GeneralUtility::trimExplode(
            ',',
            $this->getConfValueString(
                'showFeUserFieldsInRegistrationsList',
                's_template_special'
            ),
            true
        );
    }

    /**
     * Gets the keys of the registration fields that should be displayed in
     * the list.
     *
     * @return string[] keys of the registration fields to display, might be empty
     */
    private function getRegistrationFields(): array
    {
        return GeneralUtility::trimExplode(
            ',',
            $this->getConfValueString(
                'showRegistrationFieldsInRegistrationList',
                's_template_special'
            ),
            true
        );
    }
}
