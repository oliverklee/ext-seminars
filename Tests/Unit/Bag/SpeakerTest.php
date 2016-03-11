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
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Bag_SpeakerTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_Bag_Speaker
     */
    private $fixture;

    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    protected function setUp()
    {
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    ///////////////////////////////////////////
    // Tests for the basic bag functionality.
    ///////////////////////////////////////////

    public function testBagCanHaveAtLeastOneElement()
    {
        $this->testingFramework->createRecord('tx_seminars_speakers');

        $this->fixture = new Tx_Seminars_Bag_Speaker('is_dummy_record=1');

        self::assertEquals(
            1,
            $this->fixture->count()
        );
    }

    /**
     * @test
     */
    public function bagContainsVisibleSpeakers()
    {
        $this->testingFramework->createRecord('tx_seminars_speakers');

        $this->fixture = new Tx_Seminars_Bag_Speaker('is_dummy_record=1');

        self::assertFalse(
            $this->fixture->current()->isHidden()
        );
    }

    /**
     * @test
     */
    public function bagIgnoresHiddenSpeakersByDefault()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            array('hidden' => 1)
        );

        $this->fixture = new Tx_Seminars_Bag_Speaker('is_dummy_record=1');

        self::assertTrue(
            $this->fixture->isEmpty()
        );
    }

    /**
     * @test
     */
    public function bagIgnoresHiddenSpeakersWithShowHiddenRecordsSetToMinusOne()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            array('hidden' => 1)
        );

        $this->fixture = new Tx_Seminars_Bag_Speaker(
            'is_dummy_record=1',
            '',
            '',
            'uid',
            '',
            -1
        );

        self::assertTrue(
            $this->fixture->isEmpty()
        );
    }

    /**
     * @test
     */
    public function bagContainsHiddenSpeakersWithShowHiddenRecordsSetToOne()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            array('hidden' => 1)
        );

        $this->fixture = new Tx_Seminars_Bag_Speaker(
            'is_dummy_record=1',
            '',
            '',
            'uid',
            '',
            1
        );

        self::assertTrue(
            $this->fixture->current()->isHidden()
        );
    }
}
