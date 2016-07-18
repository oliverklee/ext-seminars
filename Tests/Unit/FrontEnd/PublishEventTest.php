<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case.
 *
 *
 * @author 2009 Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_FrontEnd_PublishEventTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_FrontEnd_PublishEvent
     */
    private $fixture;

    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    protected function setUp()
    {
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();
        $this->fixture = new Tx_Seminars_FrontEnd_PublishEvent();
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
            $this->fixture->translate('message_publishingFailed'),
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderForEmptyPublicationHashSetInPiVarsReturnsPublishFailedMessage()
    {
        $this->fixture->piVars['hash'] = '';

        self::assertEquals(
            $this->fixture->translate('message_publishingFailed'),
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderForInvalidPublicationHashSetInPiVarsReturnsPublishFailedMessage()
    {
        $this->fixture->piVars['hash'] = 'foo';

        self::assertEquals(
            $this->fixture->translate('message_publishingFailed'),
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderForValidPublicationHashAndVisibleEventReturnsPublishFailedMessage()
    {
        $this->fixture->init([]);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['hidden' => 0, 'publication_hash' => '123456ABC']
        );

        $this->fixture->piVars['hash'] = '123456ABC';

        self::assertEquals(
            $this->fixture->translate('message_publishingFailed'),
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderForValidPublicationHashAndHiddenEventReturnsPublishSuccessfulMessage()
    {
        $this->fixture->init([]);
        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['hidden' => 1, 'publication_hash' => '123456ABC']
        );

        $this->fixture->piVars['hash'] = '123456ABC';

        self::assertEquals(
            $this->fixture->translate('message_publishingSuccessful'),
            $this->fixture->render()
        );
    }

    /**
     * @test
     */
    public function renderForValidPublicationHashUnhidesEventWithPublicationHash()
    {
        $this->fixture->init([]);
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['hidden' => 1, 'publication_hash' => '123456ABC']
        );
        $this->fixture->piVars['hash'] = '123456ABC';

        $this->fixture->render();

        self::assertTrue(
            $this->testingFramework->existsRecord(
                'tx_seminars_seminars', 'uid = ' . $eventUid . ' AND hidden = 0'
            )
        );
    }

    /**
     * @test
     */
    public function renderForValidPublicationHashRemovesPublicationHashFromEvent()
    {
        $this->fixture->init([]);
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['hidden' => 1, 'publication_hash' => '123456ABC']
        );
        $this->fixture->piVars['hash'] = '123456ABC';

        $this->fixture->render();

        self::assertTrue(
            $this->testingFramework->existsRecord(
                'tx_seminars_seminars', 'uid = ' . $eventUid .
                    ' AND publication_hash = ""'
            )
        );
    }
}
