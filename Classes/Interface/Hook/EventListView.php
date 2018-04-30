<?php

/**
 * This interface needs to be used for hooks concerning the event list view.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Seminars_Interface_Hook_EventListView
{
    /**
     * Modifies a list row in the events list.
     *
     * This function will be called for all types of event lists.
     *
     * @param \Tx_Seminars_Model_Event $event
     *        the event to display in the current row
     * @param \Tx_Oelib_Template $template
     *        the template that will be used to create the list row output
     *
     * @return void
     */
    public function modifyListRow(\Tx_Seminars_Model_Event $event, \Tx_Oelib_Template $template);

    /**
     * Modifies a list view row in the "my events" list.
     *
     * @param \Tx_Seminars_Model_Registration $registration
     *        the registration to display in the current row
     * @param \Tx_Oelib_Template $template
     *        the template that will be used to create the list row output
     *
     * @return void
     */
    public function modifyMyEventsListRow(
        \Tx_Seminars_Model_Registration $registration,
        \Tx_Oelib_Template $template
    );
}
