<?php

declare(strict_types=1);

use OliverKlee\Oelib\Templating\Template;

/**
 * This interface needs to be used for hooks concerning the event single view.
 *
 * @deprecated will be removed in seminars 4; use `Hooks\Interfaces\Hook\SeminarSingleView` instead
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Seminars_Interfaces_Hook_EventSingleView
{
    /**
     * Modifies the event single view.
     *
     * @param \Tx_Seminars_Model_Event $event
     *        the event to display in the single view
     * @param Template $template the template that will be used to create the single view output
     *
     * @return void
     *
     * @deprecated will be removed in seminars 4; use `SeminarSingleView::modifySingleView` instead
     */
    public function modifyEventSingleView(\Tx_Seminars_Model_Event $event, Template $template);

    /**
     * Modifies a list row in the time slots list (which is part of the event
     * single view).
     *
     * @param \Tx_Seminars_Model_TimeSlot $timeSlot
     *        the time slot to display in the current row
     * @param Template $template the template that will be used to create the list row output
     *
     * @return void
     *
     * @deprecated will be removed in seminars 4; no replacement
     */
    public function modifyTimeSlotListRow(\Tx_Seminars_Model_TimeSlot $timeSlot, Template $template);
}
