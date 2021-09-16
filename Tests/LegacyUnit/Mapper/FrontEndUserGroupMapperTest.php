<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\Mapper\BackEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\BackEndUser;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;

final class FrontEndUserGroupMapperTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Mapper_FrontEndUserGroup the object to test
     */
    private $subject = null;

    /**
     * @var TestingFramework the testing framework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Mapper_FrontEndUserGroup();
        $this->testingFramework = new TestingFramework('tx_seminars');
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    //////////////////////////////////////
    // Tests for the basic functionality
    //////////////////////////////////////

    /**
     * @test
     */
    public function mapperForGhostReturnsSeminarsFrontEndUserGroupInstance()
    {
        self::assertInstanceOf(\Tx_Seminars_Model_FrontEndUserGroup::class, $this->subject->getNewGhost());
    }

    // Tests concerning the reviewer

    /**
     * @test
     */
    public function frontEndUserGroupCanReturnBackEndUserModel()
    {
        $backEndUser = MapperRegistry::get(BackEndUserMapper::class)->getNewGhost();
        $frontEndUserGroup = $this->subject->getLoadedTestingModel(
            ['tx_seminars_reviewer' => $backEndUser->getUid()]
        );

        $model = $this->subject->find($frontEndUserGroup->getUid());

        self::assertInstanceOf(BackEndUser::class, $model->getReviewer());
    }

    // Tests concerning the default categories

    /**
     * @test
     */
    public function frontEndUserGroupReturnsListOfCategories()
    {
        $categoryUid = $this->testingFramework->createRecord(
            'tx_seminars_categories'
        );
        $frontEndUserGroupUid = $this->testingFramework->createFrontEndUserGroup();

        $this->testingFramework->createRelationAndUpdateCounter(
            'fe_groups',
            $frontEndUserGroupUid,
            $categoryUid,
            'tx_seminars_default_categories'
        );

        $model = $this->subject->find($frontEndUserGroupUid);
        self::assertInstanceOf(\Tx_Seminars_Model_Category::class, $model->getDefaultCategories()->first());
    }

    // Tests concerning the default organizer

    /**
     * @test
     */
    public function getDefaultOrganizerForExistingOrganizerReturnsOrganizer()
    {
        $organizerUid = $this->testingFramework->createRecord(
            'tx_seminars_organizers'
        );
        $groupUid = $this->testingFramework->createFrontEndUserGroup(
            ['tx_seminars_default_organizer' => $organizerUid]
        );

        $model = $this->subject->find($groupUid);
        self::assertInstanceOf(\Tx_Seminars_Model_Organizer::class, $model->getDefaultOrganizer());
    }
}
