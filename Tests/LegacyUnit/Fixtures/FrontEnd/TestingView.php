<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\FrontEnd;

use OliverKlee\Seminars\FrontEnd\AbstractView;

/**
 * This class represents a view for testing purposes.
 */
final class TestingView extends AbstractView
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
