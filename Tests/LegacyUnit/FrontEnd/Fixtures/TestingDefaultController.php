<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd\Fixtures;

/**
 * Proxy class to make some things public.
 */
class TestingDefaultController extends \Tx_Seminars_FrontEnd_DefaultController
{
    public function setSeminar(?\Tx_Seminars_OldModel_Event $event = null): void
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

    public function processEventEditorActions(): void
    {
        parent::processEventEditorActions();
    }

    public function hideEvent(\Tx_Seminars_Model_Event $event): void
    {
        parent::hideEvent($event);
    }

    public function unhideEvent(\Tx_Seminars_Model_Event $event): void
    {
        parent::unhideEvent($event);
    }

    public function copyEvent(\Tx_Seminars_Model_Event $event): void
    {
        parent::copyEvent($event);
    }
}
