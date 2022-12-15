<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd\Fixtures;

use OliverKlee\Seminars\FrontEnd\DefaultController;
use OliverKlee\Seminars\OldModel\LegacyEvent;

/**
 * Proxy class to make some things public.
 */
class TestingDefaultController extends DefaultController
{
    public function setSeminar(?LegacyEvent $event = null): void
    {
        parent::setSeminar($event);
    }

    public function createAllEditorLinks(): string
    {
        return parent::createAllEditorLinks();
    }

    public function mayCurrentUserEditCurrentEvent(): bool
    {
        return parent::mayCurrentUserEditCurrentEvent();
    }
}
