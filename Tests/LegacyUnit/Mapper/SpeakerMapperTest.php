<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Model\FrontEndUser;

final class SpeakerMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Seminars_Mapper_Speaker
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new \Tx_Seminars_Mapper_Speaker();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    // Tests concerning find

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsOrganizerInstance(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_speakers');

        self::assertInstanceOf(
            \Tx_Seminars_Model_Speaker::class,
            $this->subject->find($uid)
        );
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            ['title' => 'John Doe']
        );

        $model = $this->subject->find($uid);
        self::assertEquals(
            'John Doe',
            $model->getName()
        );
    }

    // Tests regarding the skills.

    /**
     * @test
     */
    public function getSkillsReturnsListInstance(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_speakers');

        $model = $this->subject->find($uid);
        self::assertInstanceOf(Collection::class, $model->getSkills());
    }

    /**
     * @test
     */
    public function getSkillsWithoutSkillsReturnsEmptyList(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_speakers');

        $model = $this->subject->find($uid);
        self::assertTrue(
            $model->getSkills()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getSkillsWithOneSkillReturnsNonEmptyList(): void
    {
        $speakerUid = $this->testingFramework->createRecord('tx_seminars_speakers');
        $skill = MapperRegistry::get(\Tx_Seminars_Mapper_Skill::class)->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_speakers',
            $speakerUid,
            $skill->getUid(),
            'skills'
        );

        $model = $this->subject->find($speakerUid);
        self::assertFalse(
            $model->getSkills()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getSkillsWithOneSkillReturnsOneSkill(): void
    {
        $speakerUid = $this->testingFramework->createRecord('tx_seminars_speakers');
        $skill = MapperRegistry::get(\Tx_Seminars_Mapper_Skill::class)
            ->getNewGhost();
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_speakers',
            $speakerUid,
            $skill->getUid(),
            'skills'
        );

        $model = $this->subject->find($speakerUid);
        self::assertEquals(
            $skill->getUid(),
            $model->getSkills()->getUids()
        );
    }

    // Tests regarding the owner.

    /**
     * @test
     */
    public function getOwnerWithoutOwnerReturnsNull(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertNull($testingModel->getOwner());
    }

    /**
     * @test
     */
    public function getOwnerWithOwnerReturnsOwnerInstance(): void
    {
        $frontEndUser = MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class)
            ->getLoadedTestingModel([]);
        $testingModel = $this->subject->getLoadedTestingModel(
            ['owner' => $frontEndUser->getUid()]
        );

        self::assertInstanceOf(FrontEndUser::class, $testingModel->getOwner());
    }
}
