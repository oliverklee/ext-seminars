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

    /**
     * @return void
     */
    public function processEventEditorActions()
    {
        parent::processEventEditorActions();
    }

    /**
     * @param \Tx_Seminars_Model_Event $event
     *
     * @return void
     */
    public function hideEvent(\Tx_Seminars_Model_Event $event)
    {
        parent::hideEvent($event);
    }

    /**
     * @param \Tx_Seminars_Model_Event $event
     *
     * @return void
     */
    public function unhideEvent(\Tx_Seminars_Model_Event $event)
    {
        parent::unhideEvent($event);
    }

    /**
     * @param \Tx_Seminars_Model_Event $event
     *
     * @return void
     */
    public function copyEvent(\Tx_Seminars_Model_Event $event)
    {
        parent::copyEvent($event);
    }
}
