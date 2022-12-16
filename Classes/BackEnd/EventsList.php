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

            $tableRows .= $this->template->getSubpart('EVENT_ROW');
        }

        $this->template->setSubpart('EVENT_ROW', $tableRows);
    }
}
