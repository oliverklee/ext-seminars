<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates an organizer list in the back end.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_BackEnd_OrganizersList extends Tx_Seminars_BackEnd_AbstractList
{
    /**
     * @var string the name of the table we're working on
     */
    protected $tableName = 'tx_seminars_organizers';

    /**
     * @var Tx_Seminars_OldModel_Organizer the organizer which we want to list
     */
    private $organizer = null;

    /**
     * @var string the path to the template file of this list
     */
    protected $templateFile = 'EXT:seminars/Resources/Private/Templates/BackEnd/OrganizersList.html';

    /**
     * Frees as much memory that has been used by this object as possible.
     */
    public function __destruct()
    {
        unset($this->organizer);

        parent::__destruct();
    }

    /**
     * Generates and prints out a organizers list.
     *
     * @return string the HTML source code to display
     */
    public function show()
    {
        $content = '';

        $pageData = $this->page->getPageData();

        $this->template->setMarker(
            'new_record_button',
            $this->getNewIcon($pageData['uid'])
        );

        $this->template->setMarker(
            'label_full_name',
            $this->getLanguageService()->getLL('organizerlist.title')
        );

        /** @var Tx_Seminars_BagBuilder_Organizer $builder */
        $builder = GeneralUtility::makeInstance(Tx_Seminars_BagBuilder_Organizer::class);

        $builder->setSourcePages($pageData['uid'], self::RECURSION_DEPTH);

        $organizerBag = $builder->build();

        $tableRows = '';

        /** @var Tx_Seminars_OldModel_Organizer $organizerBag */
        foreach ($organizerBag as $this->organizer) {
            $this->template->setMarker(
                'icon',
                $this->organizer->getRecordIcon()
            );
            $this->template->setMarker(
                'full_name',
                htmlspecialchars($this->organizer->getTitle())
            );
            $this->template->setMarker(
                'edit_button',
                $this->getEditIcon(
                    $this->organizer->getUid(),
                    $this->organizer->getPageUid()
                )
            );
            $this->template->setMarker(
                'delete_button',
                $this->getDeleteIcon(
                    $this->organizer->getUid(),
                    $this->organizer->getPageUid()
                )
            );

            $tableRows .= $this->template->getSubpart('ORGANIZER_ROW');
        }
        $this->template->setSubpart('ORGANIZER_ROW', $tableRows);
        $this->template->setMarker('label_print_button', $this->getLanguageService()->getLL('print'));

        $content .= $this->template->getSubpart('SEMINARS_ORGANIZER_LIST');

        $content .= $organizerBag->checkConfiguration();

        return $content;
    }

    /**
     * Returns the storage folder for new organizer records.
     *
     * This will be determined by the auxiliary folder storage setting of the
     * currently logged-in BE-user.
     *
     * @return int the PID for new organizer records, will be >= 0
     */
    protected function getNewRecordPid()
    {
        return $this->getLoggedInUser()->getAuxiliaryRecordsFolder();
    }
}
