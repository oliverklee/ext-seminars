<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Mapper_SpeakerTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * @var Tx_Seminars_Mapper_Speaker
     */
    private $fixture;

    protected function setUp()
    {
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

        $this->fixture = new Tx_Seminars_Mapper_Speaker();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    //////////////////////////
    // Tests concerning find
    //////////////////////////

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsOrganizerInstance()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_speakers');

        self::assertInstanceOf(
            Tx_Seminars_Model_Speaker::class,
            $this->fixture->find($uid)
        );
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            ['title' => 'John Doe']
        );

        /** @var Tx_Seminars_Model_Speaker $model */
        $model = $this->fixture->find($uid);
        self::assertEquals(
            'John Doe',
            $model->getName()
        );
    }

    ////////////////////////////////
    // Tests regarding the skills.
    ////////////////////////////////

    /**
     * @test
     */
    public function getSkillsReturnsListInstance()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_speakers');

        /** @var Tx_Seminars_Model_Speaker $model */
        $model = $this->fixture->find($uid);
        self::assertInstanceOf(Tx_Oelib_List::class, $model->getSkills());
    }

    /**
     * @test
     */
    public function getSkillsWithoutSkillsReturnsEmptyList()
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_speakers');

        /** @var Tx_Seminars_Model_Speaker $model */
        $model = $this->fixture->find($uid);
        self::assertTrue(
            $model->getSkills()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getSkillsWithOneSkillReturnsNonEmptyList()
    {
        $speakerUid = $this->testingFramework->createRecord('tx_seminars_speakers');
        $skill = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Skill::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_speakers',
            $speakerUid,
            $skill->getUid(),
            'skills'
        );

        /** @var Tx_Seminars_Model_Speaker $model */
        $model = $this->fixture->find($speakerUid);
        self::assertFalse(
            $model->getSkills()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getSkillsWithOneSkillReturnsOneSkill()
    {
        $speakerUid = $this->testingFramework->createRecord('tx_seminars_speakers');
        $skill = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Skill::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_speakers',
            $speakerUid,
            $skill->getUid(),
            'skills'
        );

        /** @var Tx_Seminars_Model_Speaker $model */
        $model = $this->fixture->find($speakerUid);
        self::assertEquals(
            $skill->getUid(),
            $model->getSkills()->getUids()
        );
    }

    ///////////////////////////////
    // Tests regarding the owner.
    ///////////////////////////////

    /**
     * @test
     */
    public function getOwnerWithoutOwnerReturnsNull()
    {
        /** @var \Tx_Seminars_Model_Speaker $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel([]);

        self::assertNull($testingModel->getOwner());
    }

    /**
     * @test
     */
    public function getOwnerWithOwnerReturnsOwnerInstance()
    {
        $frontEndUser = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class)
            ->getLoadedTestingModel([]);
        /** @var \Tx_Seminars_Model_Speaker $testingModel */
        $testingModel = $this->fixture->getLoadedTestingModel(
            ['owner' => $frontEndUser->getUid()]
        );

        self::assertInstanceOf(\Tx_Seminars_Model_FrontEndUser::class, $testingModel->getOwner());
    }
}
