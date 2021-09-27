<?php

declare(strict_types=1);

use OliverKlee\Oelib\Configuration\ConfigurationCheck;

/**
 * This class checks the Seminar Manager configuration for basic sanity.
 *
 * The correct functioning of this class does not rely on any HTML templates or
 * language files so it works even under the worst of circumstances.
 *
 * phpcs:disable PSR1.Methods.CamelCapsMethodName
 */
class Tx_Seminars_ConfigCheck extends ConfigurationCheck
{
    /**
     * Checks the configuration for: tx_seminars_test/.
     *
     * @return void
     */
    protected function check_tx_seminars_test()
    {
        $this->checkStaticIncluded();
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_RegistrationsList/.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_RegistrationsList()
    {
    }

    /**
     * Does nothing.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController()
    {
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/seminar_registration.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_seminar_registration()
    {
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/single_view.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_single_view()
    {
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/seminar_list.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_seminar_list()
    {
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_Countdown.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_Countdown()
    {
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/my_vip_events.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_my_vip_events()
    {
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/topic_list.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_topic_list()
    {
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/my_events.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_my_events()
    {
    }

    /**
     * Checks the configuration for: check_Tx_Seminars_FrontEnd_DefaultController/edit_event.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_edit_event()
    {
    }

    /**
     * Checks the configuration for: check_Tx_Seminars_FrontEnd_DefaultController/my_entered_events.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_my_entered_events()
    {
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_CategoryList.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_CategoryList()
    {
    }

    /**
     * Checks the configuration for: \Tx_Seminars_FrontEnd_DefaultController/favorites_list
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_favorites_list()
    {
    }

    /**
     * This check isn't actually used. It is merely needed for the unit tests.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_events_next_day()
    {
    }

    /**
     * Checks if the common frontend settings are set.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_EventHeadline()
    {
    }

    /**
     * This check isn't actually used. It is merely needed for the unit tests.
     *
     * @return void
     */
    protected function check_Tx_Seminars_FrontEnd_DefaultController_event_headline()
    {
    }

    /**
     * Checks the CSV-related settings.
     *
     * @return void
     */
    protected function check_tx_seminars_Bag_Event_csv()
    {
    }
}
