<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * This interface needs to be used for hooks concerning the event list view.
 *
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
     * @param Tx_Seminars_Model_Event $event
     *        the event to display in the current row
     * @param Tx_Oelib_Template $template
     *        the template that will be used to create the list row output
     *
     * @return void
     */
    public function modifyListRow(Tx_Seminars_Model_Event $event, Tx_Oelib_Template $template);

    /**
     * Modifies a list view row in the "my events" list.
     *
     * @param Tx_Seminars_Model_Registration $registration
     *        the registration to display in the current row
     * @param Tx_Oelib_Template $template
     *        the template that will be used to create the list row output
     *
     * @return void
     */
    public function modifyMyEventsListRow(
        Tx_Seminars_Model_Registration $registration, Tx_Oelib_Template $template
    );
}
