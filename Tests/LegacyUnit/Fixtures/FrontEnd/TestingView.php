<?php
declare(strict_types = 1);

/**
 * This class represents a view for testing purposes.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Fixtures_FrontEnd_TestingView extends \Tx_Seminars_FrontEnd_AbstractView
{
    /**
     * Renders the view and returns its content.
     *
     * @return string the view's content
     */
    public function render()
    {
        return 'Hi, I am the testingFrontEndView!';
    }
}
