<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Seminars\Bag\OrganizerBag;
use OliverKlee\Seminars\BagBuilder\OrganizerBagBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates an organizer list in the back end.
 */
class OrganizersList extends AbstractList
{
    /**
     * @var string the name of the table we're working on
     */
    protected $tableName = 'tx_seminars_organizers';

    /**
     * @var string the path to the template file of this list
     */
    protected $templateFile = 'EXT:seminars/Resources/Private/Templates/BackEnd/OrganizersList.html';

    /**
     * Generates and prints out a organizers list.
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

        $this->template->setMarker(
            'label_full_name',
            $this->getLanguageService()->getLL('organizerlist.title')
        );

        $builder = GeneralUtility::makeInstance(OrganizerBagBuilder::class);
        $builder->setSourcePages((string)$pageData['uid'], self::RECURSION_DEPTH);
        $organizerBag = $builder->build();

        $tableRows = '';

        /** @var OrganizerBag $organizerBag */
        foreach ($organizerBag as $organizer) {
            $this->template->setMarker('icon', $organizer->getRecordIcon());
            $this->template->setMarker('full_name', \htmlspecialchars($organizer->getTitle(), ENT_QUOTES | ENT_HTML5));
            $this->template->setMarker(
                'edit_button',
                $this->getEditIcon($organizer->getUid(), $organizer->getPageUid())
            );
            $this->template->setMarker(
                'delete_button',
                $this->getDeleteIcon($organizer->getUid(), $organizer->getPageUid())
            );

            $tableRows .= $this->template->getSubpart('ORGANIZER_ROW');
        }
        $this->template->setSubpart('ORGANIZER_ROW', $tableRows);

        $content .= $this->template->getSubpart('SEMINARS_ORGANIZER_LIST');

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
    protected function getNewRecordPid(): int
    {
        return $this->getLoggedInUser()->getAuxiliaryRecordsFolder();
    }
}
