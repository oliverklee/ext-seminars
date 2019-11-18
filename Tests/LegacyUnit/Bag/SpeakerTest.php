<?php

declare(strict_types=1);

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Bag_SpeakerTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Bag_Speaker
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');
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

        $this->subject = new \Tx_Seminars_Bag_Speaker('is_dummy_record=1');

        self::assertEquals(
            1,
            $this->subject->count()
        );
    }

    /**
     * @test
     */
    public function bagContainsVisibleSpeakers()
    {
        $this->testingFramework->createRecord('tx_seminars_speakers');

        $this->subject = new \Tx_Seminars_Bag_Speaker('is_dummy_record=1');

        /** @var \Tx_Seminars_OldModel_Speaker $currentModel */
        $currentModel = $this->subject->current();

        self::assertFalse($currentModel->isHidden());
    }

    /**
     * @test
     */
    public function bagIgnoresHiddenSpeakersByDefault()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            ['hidden' => 1]
        );

        $this->subject = new \Tx_Seminars_Bag_Speaker('is_dummy_record=1');

        self::assertTrue(
            $this->subject->isEmpty()
        );
    }

    /**
     * @test
     */
    public function bagIgnoresHiddenSpeakersWithShowHiddenRecordsSetToMinusOne()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            ['hidden' => 1]
        );

        $this->subject = new \Tx_Seminars_Bag_Speaker(
            'is_dummy_record=1',
            '',
            '',
            'uid',
            '',
            -1
        );

        self::assertTrue(
            $this->subject->isEmpty()
        );
    }

    /**
     * @test
     */
    public function bagContainsHiddenSpeakersWithShowHiddenRecordsSetToOne()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            ['hidden' => 1]
        );

        $this->subject = new \Tx_Seminars_Bag_Speaker(
            'is_dummy_record=1',
            '',
            '',
            'uid',
            '',
            1
        );

        /** @var \Tx_Seminars_OldModel_Speaker $currentModel */
        $currentModel = $this->subject->current();

        self::assertTrue($currentModel->isHidden());
    }
}
