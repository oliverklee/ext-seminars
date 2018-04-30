<?php

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Mapper_FrontEndUserGroupTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Seminars_Mapper_FrontEndUserGroup the object to test
     */
    private $fixture;

    /**
     * @var \Tx_Oelib_TestingFramework the testing framework
     */
    private $testingFramework;

    protected function setUp()
    {
        $this->fixture = new \Tx_Seminars_Mapper_FrontEndUserGroup();
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');
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
        self::assertInstanceOf(\Tx_Seminars_Model_FrontEndUserGroup::class, $this->fixture->getNewGhost());
    }

    //////////////////////////////////
    // Tests concerning the reviewer
    //////////////////////////////////

    /**
     * @test
     */
    public function frontEndUserGroupCanReturnBackEndUserModel()
    {
        $backEndUser = \Tx_Oelib_MapperRegistry::get(
            \Tx_Oelib_Mapper_BackEndUser::class
        )->getNewGhost();
        $frontEndUserGroup = $this->fixture->getLoadedTestingModel(
            ['tx_seminars_reviewer' => $backEndUser->getUid()]
        );

        /** @var \Tx_Seminars_Model_FrontEndUserGroup $model */
        $model = $this->fixture->find($frontEndUserGroup->getUid());

        self::assertInstanceOf(\Tx_Oelib_Model_BackEndUser::class, $model->getReviewer());
    }

    ////////////////////////////////////////////
    // Tests concerning the default categories
    ////////////////////////////////////////////

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

        /** @var \Tx_Seminars_Model_FrontEndUserGroup $model */
        $model = $this->fixture->find($frontEndUserGroupUid);
        self::assertInstanceOf(\Tx_Seminars_Model_Category::class, $model->getDefaultCategories()->first());
    }

    ///////////////////////////////////////////
    // Tests concerning the default organizer
    ///////////////////////////////////////////

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

        /** @var \Tx_Seminars_Model_FrontEndUserGroup $model */
        $model = $this->fixture->find($groupUid);
        self::assertInstanceOf(\Tx_Seminars_Model_Organizer::class, $model->getDefaultOrganizer());
    }
}
