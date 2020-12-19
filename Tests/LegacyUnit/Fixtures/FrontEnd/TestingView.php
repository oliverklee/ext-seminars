<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\FrontEnd;

/**
 * This class represents a view for testing purposes.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class TestingView extends \Tx_Seminars_FrontEnd_AbstractView
{
    /**
     * Renders the view and returns its content.
     *
     * @return string the view's content
     */
    public function render(): string
    {
        return 'Hi, I am the testingFrontEndView!';
    }
}
