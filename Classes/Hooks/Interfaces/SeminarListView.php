<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

/**
 * Use this interface for hooks concerning the seminar list views.
 *
 * It supersedes the deprecated `EventListView` interface.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface SeminarListView extends Hook
{
    /**
     * Modifies the list view seminar bag builder (the item collection for a seminar list).
     *
     * Add or alter limitations for the selection of seminars to be shown in the
     * list.
     *
     * @see \OliverKlee\Seminars\BagBuilder\AbstractBagBuilder::getWhereClausePart()
     * @see \OliverKlee\Seminars\BagBuilder\AbstractBagBuilder::setWhereClausePart()
     *
     * This function will be called for these types of seminar lists: "topics", "seminars",
     * "my vip seminars", "my entered events", "events next day", "other dates".
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
     * @param \Tx_Seminars_BagBuilder_Event $builder the bag builder
     * @param string $whatToDisplay the flavor of list view: 'seminar_list', 'topic_list',
     *        'my_vip_events', 'my_entered_events', 'events_next_day' or 'other_dates'
     *
     * @return void
     */
    public function modifyEventBagBuilder(
        \Tx_Seminars_FrontEnd_DefaultController $controller,
        \Tx_Seminars_BagBuilder_Event $builder,
        string $whatToDisplay
    );

    /**
     * Modifies the list view registration bag builder (the item collection for a "my events" list).
     *
     * Add or alter limitations for the selection of seminars to be shown in the
     * list.
     *
     * @see \OliverKlee\Seminars\BagBuilder\AbstractBagBuilder::getWhereClausePart()
     * @see \OliverKlee\Seminars\BagBuilder\AbstractBagBuilder::setWhereClausePart()
     *
     * This function will be called for "my events" lists only.
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
     * @param \Tx_Seminars_BagBuilder_Registration $builder the bag builder
     * @param string $whatToDisplay the flavor of list view ('my_events' only?)
     *
     * @return void
     */
    public function modifyRegistrationBagBuilder(
        \Tx_Seminars_FrontEnd_DefaultController $controller,
        \Tx_Seminars_BagBuilder_Registration $builder,
        string $whatToDisplay
    );

    /**
     * Modifies the list view header row in a seminar list.
     *
     * This function will be called for all types of seminar lists ("topics",
     * "seminars", "my seminars", "my vip seminars", "my entered events",
     * "events next day", "other dates").
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
     *
     * @return void
     */
    public function modifyListHeader(\Tx_Seminars_FrontEnd_DefaultController $controller);

    /**
     * Modifies a list row in a seminar list.
     *
     * This function will be called for all types of seminar lists ("topics",
     * "seminars", "my seminars", "my vip seminars", "my entered events",
     * "events next day", "other dates").
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
     *
     * @return void
     */
    public function modifyListRow(\Tx_Seminars_FrontEnd_DefaultController $controller);

    /**
     * Modifies a list view row in a "my seminars" list.
     *
     * This function will be called for "my seminars" , "my vip seminars",
     * "my entered events" lists only.
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
     *
     * @return void
     */
    public function modifyMyEventsListRow(\Tx_Seminars_FrontEnd_DefaultController $controller);

    /**
     * Modifies the list view footer in a seminars list.
     *
     * This function will be called for all types of seminar lists ("topics",
     * "seminars", "my seminars", "my vip seminars", "my entered events",
     * "events next day", "other dates").
     *
     * @param \Tx_Seminars_FrontEnd_DefaultController $controller the calling controller
     *
     * @return void
     */
    public function modifyListFooter(\Tx_Seminars_FrontEnd_DefaultController $controller);
}
