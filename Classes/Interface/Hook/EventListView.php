<?php

declare(strict_types=1);

use OliverKlee\Oelib\Templating\Template;

/**
 * This interface needs to be used for hooks concerning the event list view.
 *
 * @deprecated will be removed in seminars 4; use `Hooks\Interfaces\Hook\SeminarListView` instead
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
     * @param Template $template the template that will be used to create the list row output
     *
     * @return void
     *
     * @deprecated will be removed in seminars 4; use `SeminarListView::modifyListRow` instead
     */
    public function modifyListRow(\Tx_Seminars_Model_Event $event, Template $template);

    /**
     * Modifies a list view row in the "my events" list.
     *
     * @param \Tx_Seminars_Model_Registration $registration
     *        the registration to display in the current row
     * @param Template $template the template that will be used to create the list row output
     *
     * @return void
     *
     * @deprecated will be removed in seminars 4; use `SeminarListView::modifyMyEventsListRow` instead
     */
    public function modifyMyEventsListRow(
        \Tx_Seminars_Model_Registration $registration,
        Template $template
    );
}
