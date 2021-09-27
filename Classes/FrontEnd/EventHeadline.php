<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class displays an event headline consisting of the event title and date.
 */
class Tx_Seminars_FrontEnd_EventHeadline extends \Tx_Seminars_FrontEnd_AbstractView
{
    /**
     * @var \Tx_Seminars_Mapper_Event
     */
    protected $mapper = null;

    /**
     * Injects an Event Mapper for this View.
     *
     * @param \Tx_Seminars_Mapper_Event $mapper
     *
     * @return void
     */
    public function injectEventMapper(\Tx_Seminars_Mapper_Event $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Creates the event headline, consisting of the event title and date.
     *
     * @return string HTML code of the event headline, will be empty if an invalid or no event ID was set in piVar 'showUid'
     */
    public function render(): string
    {
        if ($this->mapper === null) {
            throw new \BadMethodCallException('The method injectEventMapper() needs to be called first.', 1333614794);
        }

        $eventId = (int)$this->piVars['showUid'];
        if ($eventId <= 0) {
            return '';
        }

        $event = $this->mapper->find($eventId);

        if (!$this->mapper->existsModel($eventId)) {
            return '';
        }

        $this->setMarker('title_and_date', $this->getTitleAndDate($event));
        $result = $this->getSubpart('VIEW_HEADLINE');

        return $result;
    }

    /**
     * Gets the unique event title, consisting of the event title and the date (comma-separated).
     *
     * If the event has no date, just the title is returned.
     *
     * @param \Tx_Seminars_Model_Event $event the event to get the unique event title for
     *
     * @return string the unique event title (or '' if there is an error)
     */
    protected function getTitleAndDate(\Tx_Seminars_Model_Event $event): string
    {
        $result = \htmlspecialchars($event->getTitle(), ENT_QUOTES | ENT_HTML5);
        if (!$event->hasBeginDate()) {
            return $result;
        }

        /** @var \Tx_Seminars_ViewHelper_DateRange $dateRangeViewHelper */
        $dateRangeViewHelper = GeneralUtility::makeInstance(\Tx_Seminars_ViewHelper_DateRange::class);

        return $result . ', ' . $dateRangeViewHelper->render($event);
    }
}
