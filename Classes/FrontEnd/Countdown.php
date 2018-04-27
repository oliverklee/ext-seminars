<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a countdown to the next upcoming event.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Mario Rimann <typo3-coding@rimann.org>
 */
class Tx_Seminars_FrontEnd_Countdown extends Tx_Seminars_FrontEnd_AbstractView
{
    /**
     * @var Tx_Seminars_Mapper_Event
     */
    protected $mapper = null;

    /**
     * @var Tx_Seminars_ViewHelper_Countdown
     */
    protected $viewHelper = null;

    /**
     * Injects an Event Mapper for this View.
     *
     * @param Tx_Seminars_Mapper_Event $mapper
     *
     * @return void
     */
    public function injectEventMapper(Tx_Seminars_Mapper_Event $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Injects an Countdown View Helper.
     *
     * @param Tx_Seminars_ViewHelper_Countdown $viewHelper
     *
     * @return void
     */
    public function injectCountDownViewHelper(Tx_Seminars_ViewHelper_Countdown $viewHelper)
    {
        $this->viewHelper = $viewHelper;
    }

    /**
     * Creates a countdown to the next upcoming event.
     *
     * @return string HTML code of the countdown or a message if no upcoming event has been found
     */
    public function render()
    {
        if ($this->mapper === null) {
            throw new BadMethodCallException('The method injectEventMapper() needs to be called first.', 1333617194);
        }
        if ($this->viewHelper === null) {
            /** @var Tx_Seminars_ViewHelper_Countdown $viewHelper */
            $viewHelper = GeneralUtility::makeInstance(Tx_Seminars_ViewHelper_Countdown::class);
            $this->injectCountDownViewHelper($viewHelper);
        }

        $this->setErrorMessage($this->checkConfiguration(true));

        try {
            /** @var Tx_Seminars_Model_Event $event */
            $event = $this->mapper->findNextUpcoming();

            $message = $this->viewHelper->render($event->getBeginDateAsUnixTimeStamp());
        } catch (Tx_Oelib_Exception_NotFound $exception) {
            $message = $this->translate('message_countdown_noEventFound');
        }

        $this->setMarker('count_down_message', $message);

        return $this->getSubpart('COUNTDOWN');
    }
}
