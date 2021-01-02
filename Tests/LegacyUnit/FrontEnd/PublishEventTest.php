<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;

/**
 * Test case.
 *
 * @author 2009 Bernd Schönbach <bernd@oliverklee.de>
 */
class PublishEventTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var \Tx_Seminars_FrontEnd_PublishEvent
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();
        $this->subject = new \Tx_Seminars_FrontEnd_PublishEvent();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    ///////////////////////////////////
    // Tests concerning the rendering
    ///////////////////////////////////

    /**
     * @test
     */
    public function renderForNoPublicationHashSetInPiVarsReturnsPublishFailedMessage()
    {
        self::assertEquals(
            $this->getLanguageService()->getLL('message_publishingFailed'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForEmptyPublicationHashSetInPiVarsReturnsPublishFailedMessage()
    {
        $this->subject->piVars['hash'] = '';

        self::assertEquals(
            $this->getLanguageService()->getLL('message_publishingFailed'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForInvalidPublicationHashSetInPiVarsReturnsPublishFailedMessage()
    {
        $this->subject->piVars['hash'] = 'foo';

        self::assertEquals(
            $this->getLanguageService()->getLL('message_publishingFailed'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForValidPublicationHashAndVisibleEventReturnsPublishFailedMessage()
    {
        $this->subject->init([]);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['hidden' => 0, 'publication_hash' => '123456ABC']
        );

        $this->subject->piVars['hash'] = '123456ABC';

        self::assertEquals(
            $this->getLanguageService()->getLL('message_publishingFailed'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForValidPublicationHashAndHiddenEventReturnsPublishSuccessfulMessage()
    {
        $this->subject->init([]);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['hidden' => 1, 'publication_hash' => '123456ABC']
        );

        $this->subject->piVars['hash'] = '123456ABC';

        self::assertEquals(
            $this->getLanguageService()->getLL('message_publishingSuccessful'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForValidPublicationHashUnhidesEventWithPublicationHash()
    {
        $this->subject->init([]);
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['hidden' => 1, 'publication_hash' => '123456ABC']
        );
        $this->subject->piVars['hash'] = '123456ABC';

        $this->subject->render();

        self::assertTrue(
            $this->testingFramework->existsRecord(
                'tx_seminars_seminars',
                'uid = ' . $eventUid . ' AND hidden = 0'
            )
        );
    }

    /**
     * @test
     */
    public function renderForValidPublicationHashRemovesPublicationHashFromEvent()
    {
        $this->subject->init([]);
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['hidden' => 1, 'publication_hash' => '123456ABC']
        );
        $this->subject->piVars['hash'] = '123456ABC';

        $this->subject->render();

        self::assertTrue(
            $this->testingFramework->existsRecord(
                'tx_seminars_seminars',
                'uid = ' . $eventUid .
                ' AND publication_hash = ""'
            )
        );
    }
}
