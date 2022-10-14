<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Seminars\Bag\SpeakerBag;
use OliverKlee\Seminars\BagBuilder\SpeakerBagBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates a speaker list in the back end.
 */
class SpeakersList extends AbstractList
{
    /**
     * @var string the name of the table we're working on
     */
    protected $tableName = 'tx_seminars_speakers';

    /**
     * @var string the path to the template file of this list
     */
    protected $templateFile = 'EXT:seminars/Resources/Private/Templates/BackEnd/SpeakersList.html';

    /**
     * Generates and prints out a speakers list.
     *
     * @return string the HTML source code to display
     */
    public function show(): string
    {
        $content = '';

        $pageData = $this->page->getPageData();

        $this->template->setMarker(
            'new_record_button',
            $this->getNewIcon((int)$pageData['uid'])
        );

        $languageService = $this->getLanguageService();
        $this->template->setMarker('label_full_name', $languageService->getLL('speakerlist.title'));
        $this->template->setMarker('label_skills', $languageService->getLL('speakerlist.skills'));

        $builder = GeneralUtility::makeInstance(SpeakerBagBuilder::class);
        $builder->showHiddenRecords();
        $builder->setSourcePages((string)$pageData['uid'], self::RECURSION_DEPTH);
        $speakerBag = $builder->build();

        $tableRows = '';

        /** @var SpeakerBag $speakerBag */
        foreach ($speakerBag as $speaker) {
            $this->template->setMarker('icon', $speaker->getRecordIcon());
            $this->template->setMarker('full_name', \htmlspecialchars($speaker->getTitle(), ENT_QUOTES | ENT_HTML5));
            $this->template->setMarker(
                'edit_button',
                $this->getEditIcon($speaker->getUid(), $speaker->getPageUid())
            );
            $this->template->setMarker(
                'delete_button',
                $this->getDeleteIcon($speaker->getUid(), $speaker->getPageUid())
            );
            $this->template->setMarker(
                'hide_unhide_button',
                $this->getHideUnhideIcon($speaker->getUid(), $speaker->getPageUid(), $speaker->isHidden())
            );
            $this->template->setMarker('skills', \htmlspecialchars($speaker->getSkillsShort(), ENT_QUOTES | ENT_HTML5));

            $tableRows .= $this->template->getSubpart('SPEAKER_ROW');
        }

        $this->template->setSubpart('SPEAKER_ROW', $tableRows);
        $content .= $this->template->getSubpart('SEMINARS_SPEAKER_LIST');

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
    protected function getNewRecordPid(): int
    {
        return $this->getLoggedInUser()->getAuxiliaryRecordsFolder();
    }
}
