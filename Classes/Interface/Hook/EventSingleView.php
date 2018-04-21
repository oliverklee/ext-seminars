<?php

/**
 * This interface needs to be used for hooks concerning the event single view.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Seminars_Interface_Hook_EventSingleView
{
    /**
     * Modifies the event single view.
     *
     * @param Tx_Seminars_Model_Event $event
     *        the event to display in the single view
     * @param Tx_Oelib_Template $template
     *        the template that will be used to create the single view output
     *
     * @return void
     */
    public function modifyEventSingleView(Tx_Seminars_Model_Event $event, Tx_Oelib_Template $template);

    /**
     * Modifies a list row in the time slots list (which is part of the event
     * single view).
     *
     * @param Tx_Seminars_Model_TimeSlot $timeSlot
     *        the time slot to display in the current row
     * @param Tx_Oelib_Template $template
     *        the template that will be used to create the list row output
     *
     * @return void
     */
    public function modifyTimeSlotListRow(Tx_Seminars_Model_TimeSlot $timeSlot, Tx_Oelib_Template $template);
}
