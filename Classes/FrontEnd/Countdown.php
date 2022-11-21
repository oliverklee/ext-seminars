<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\FrontEnd;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\ViewHelpers\CountdownViewHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a countdown to the next upcoming event.
 *
 * @deprecated #1809 will be removed in seminars 5.0
 */
class Countdown extends AbstractView
{
    /**
     * @var EventMapper
     */
    protected $mapper;

    /**
     * @var CountdownViewHelper
     */
    protected $viewHelper;

    public function injectEventMapper(EventMapper $mapper): void
    {
        $this->mapper = $mapper;
    }

    public function injectCountDownViewHelper(CountdownViewHelper $viewHelper): void
    {
        $this->viewHelper = $viewHelper;
    }

    /**
     * Creates a countdown to the next upcoming event.
     *
     * @return string HTML code of the countdown or a message if no upcoming event has been found
     */
    public function render(): string
    {
        if ($this->mapper === null) {
            throw new \BadMethodCallException('The method injectEventMapper() needs to be called first.', 1333617194);
        }
        if ($this->viewHelper === null) {
            $this->injectCountDownViewHelper(GeneralUtility::makeInstance(CountdownViewHelper::class));
        }

        try {
            $event = $this->mapper->findNextUpcoming();
            $message = $this->viewHelper->render($event->getBeginDateAsUnixTimeStamp());
        } catch (NotFoundException $exception) {
            $message = $this->translate('message_countdown_noEventFound');
        }

        $this->setMarker('count_down_message', $message);

        return $this->getSubpart('COUNTDOWN');
    }
}
