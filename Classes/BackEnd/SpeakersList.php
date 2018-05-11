<?php
namespace OliverKlee\Seminars\BackEnd;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates a speaker list in the back end.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class SpeakersList extends AbstractList
{
    /**
     * @var string the name of the table we're working on
     */
    protected $tableName = 'tx_seminars_speakers';

    /**
     * @var \Tx_Seminars_OldModel_Speaker the speaker which we want to list
     */
    private $speaker = null;

    /**
     * @var string the path to the template file of this list
     */
    protected $templateFile = 'EXT:seminars/Resources/Private/Templates/BackEnd/SpeakersList.html';

    /**
     * Generates and prints out a speakers list.
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
            $GLOBALS['LANG']->getLL('speakerlist.title')
        );
        $this->template->setMarker(
            'label_skills',
            $GLOBALS['LANG']->getLL('speakerlist.skills')
        );

        /** @var \Tx_Seminars_BagBuilder_Speaker $builder */
        $builder = GeneralUtility::makeInstance(\Tx_Seminars_BagBuilder_Speaker::class);
        $builder->showHiddenRecords();

        $builder->setSourcePages($pageData['uid'], self::RECURSION_DEPTH);

        $speakerBag = $builder->build();

        $tableRows = '';

        /** @var \Tx_Seminars_Bag_Speaker $speakerBag */
        foreach ($speakerBag as $this->speaker) {
            $this->template->setMarker(
                'icon',
                $this->speaker->getRecordIcon()
            );
            $this->template->setMarker(
                'full_name',
                htmlspecialchars($this->speaker->getTitle())
            );
            $this->template->setMarker(
                'edit_button',
                $this->getEditIcon(
                    $this->speaker->getUid(),
                    $this->speaker->getPageUid()
                )
            );
            $this->template->setMarker(
                'delete_button',
                $this->getDeleteIcon(
                    $this->speaker->getUid(),
                    $this->speaker->getPageUid()
                )
            );
            $this->template->setMarker(
                'hide_unhide_button',
                $this->getHideUnhideIcon(
                    $this->speaker->getUid(),
                    $this->speaker->getPageUid(),
                    $this->speaker->isHidden()
                )
            );
            $this->template->setMarker(
                'skills',
                htmlspecialchars($this->speaker->getSkillsShort())
            );

            $tableRows .= $this->template->getSubpart('SPEAKER_ROW');
        }

        $this->template->setSubpart('SPEAKER_ROW', $tableRows);
        $content .= $this->template->getSubpart('SEMINARS_SPEAKER_LIST');

        $content .= $speakerBag->checkConfiguration();

        return $content;
    }

    /**
     * Returns the storage folder for new speaker records.
     *
     * This will be determined by the auxiliary folder storage setting of the
     * currently logged-in BE-user.
     *
     * @return int the PID for new speaker records, will be >= 0
     */
    protected function getNewRecordPid()
    {
        return $this->getLoggedInUser()->getAuxiliaryRecordsFolder();
    }
}
