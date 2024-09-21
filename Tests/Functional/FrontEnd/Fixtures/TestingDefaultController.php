<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd\Fixtures;

use OliverKlee\Seminars\FrontEnd\DefaultController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Proxy class to make some things public.
 */
class TestingDefaultController extends DefaultController
{
    public function setContentObjectRenderer(ContentObjectRenderer $contentObjectRenderer): void
    {
        $this->cObj = $contentObjectRenderer;
    }
}
