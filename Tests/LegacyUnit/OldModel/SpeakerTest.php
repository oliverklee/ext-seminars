<?php

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_OldModel_SpeakerTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * @var \Tx_Seminars_OldModel_Speaker
     */
    private $subject;

    /**
     * @var \Tx_Seminars_OldModel_Speaker a maximal filled speaker
     */
    private $maximalFixture;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');
        $subjectUid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            [
                'title' => 'Test speaker',
                'email' => 'foo@test.com',
            ]
        );
        $this->subject = new \Tx_Seminars_OldModel_Speaker($subjectUid);

        $maximalFixtureUid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            [
                'title' => 'Test speaker',
                'organization' => 'Foo inc.',
                'homepage' => 'http://www.test.com/',
                'description' => 'foo' . LF . 'bar',
                'notes' => 'test notes',
                'address' => 'test address',
                'phone_work' => '123',
                'phone_home' => '456',
                'phone_mobile' => '789',
                'fax' => '000',
                'email' => 'maximal-foo@test.com',
            ]
        );
        $this->maximalFixture = new \Tx_Seminars_OldModel_Speaker($maximalFixtureUid);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    ///////////////////////
    // Utility functions.
    ///////////////////////

    /**
     * Inserts a skill record into the database and creates a relation to it
     * from the fixture.
     *
     * @param array $skillData data of the skill to add, may be empty
     *
     * @return int the UID of the created record, will always be > 0
     */
    private function addSkillRelation(array $skillData)
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_skills',
            $skillData
        );

        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_seminars_speakers',
            $this->subject->getUid(),
            $uid,
            'skills'
        );

        $this->subject = new \Tx_Seminars_OldModel_Speaker($this->subject->getUid());

        return $uid;
    }

    /////////////////////////////////////
    // Tests for the utility functions.
    /////////////////////////////////////

    public function testAddSkillRelationReturnsUid()
    {
        self::assertTrue(
            $this->addSkillRelation([]) > 0
        );
    }

    public function testAddSkillRelationCreatesNewUids()
    {
        self::assertNotEquals(
            $this->addSkillRelation([]),
            $this->addSkillRelation([])
        );
    }

    public function testAddSkillRelationIncreasesTheNumberOfSkills()
    {
        self::assertEquals(
            0,
            $this->subject->getNumberOfSkills()
        );

        $this->addSkillRelation([]);
        self::assertEquals(
            1,
            $this->subject->getNumberOfSkills()
        );

        $this->addSkillRelation([]);
        self::assertEquals(
            2,
            $this->subject->getNumberOfSkills()
        );
    }

    public function testAddSkillRelationCreatesRelations()
    {
        self::assertEquals(
            0,
            $this->testingFramework->countRecords(
                'tx_seminars_speakers_skills_mm',
                'uid_local=' . $this->subject->getUid()
            )
        );

        $this->addSkillRelation([]);
        self::assertEquals(
            1,
            $this->testingFramework->countRecords(
                'tx_seminars_speakers_skills_mm',
                'uid_local=' . $this->subject->getUid()
            )
        );

        $this->addSkillRelation([]);
        self::assertEquals(
            2,
            $this->testingFramework->countRecords(
                'tx_seminars_speakers_skills_mm',
                'uid_local=' . $this->subject->getUid()
            )
        );
    }

    ////////////////////////////////////////
    // Tests for creating speaker objects.
    ////////////////////////////////////////

    public function testCreateFromUid()
    {
        self::assertTrue(
            $this->subject->isOk()
        );
    }

    /////////////////////////////////////////////
    // Tests for getting the speaker attributes.
    /////////////////////////////////////////////

    public function testGetOrganization()
    {
        self::assertEquals(
            '',
            $this->subject->getOrganization()
        );
        self::assertEquals(
            'Foo inc.',
            $this->maximalFixture->getOrganization()
        );
    }

    public function testHasOrganizationWithNoOrganizationReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasOrganization()
        );
    }

    public function testHasOrganizationWithOrganizationReturnsTrue()
    {
        self::assertTrue(
            $this->maximalFixture->hasOrganization()
        );
    }

    public function testGetHomepage()
    {
        self::assertEquals(
            '',
            $this->subject->getHomepage()
        );
        self::assertEquals(
            'http://www.test.com/',
            $this->maximalFixture->getHomepage()
        );
    }

    public function testHasHomepageWithNoHomepageReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasHomepage()
        );
    }

    public function testHasHomepageWithHomepageReturnsTrue()
    {
        self::assertTrue(
            $this->maximalFixture->hasHomepage()
        );
    }

    /*
     * TODO: For this test to work properly, we need a more-or-less working
     * front-end environment so that the RTE transformation functions work.
     *
     * @see https://bugs.oliverklee.com/show_bug.cgi?id=1425
     *

    public function testGetDescription() {
        $plugin = new \Tx_Seminars_FrontEnd_DefaultController();
        $plugin->init(array());

        self::assertEquals(
            '',
            $this->subject->getDescription($plugin)
        );
        self::assertEquals(
            '<p>foo</p><p>bar</p>',
            $this->maximalFixture->getDescription($plugin)
        );
    }

    */

    public function testHasDescriptionWithNoDescriptionReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasDescription()
        );
    }

    public function testHasDescriptionWithDescriptionReturnsTrue()
    {
        self::assertTrue(
            $this->maximalFixture->hasDescription()
        );
    }

    public function testHasSkillsInitiallyIsFalse()
    {
        self::assertFalse(
            $this->subject->hasSkills()
        );
    }

    /**
     * @test
     */
    public function canHaveOneSkill()
    {
        $this->addSkillRelation([]);
        self::assertTrue(
            $this->subject->hasSkills()
        );
    }

    public function testGetSkillsShortWithNoSkillReturnsAnEmptyString()
    {
        self::assertEquals(
            '',
            $this->subject->getSkillsShort()
        );
    }

    public function testGetSkillsShortWithSingleSkillReturnsASingleSkill()
    {
        $title = 'Test title';
        $this->addSkillRelation(['title' => $title]);

        self::assertContains(
            $title,
            $this->subject->getSkillsShort()
        );
    }

    public function testGetSkillsShortWithMultipleSkillsReturnsMultipleSkills()
    {
        $firstTitle = 'Skill 1';
        $secondTitle = 'Skill 2';
        $this->addSkillRelation(['title' => $firstTitle]);
        $this->addSkillRelation(['title' => $secondTitle]);

        self::assertEquals(
            $firstTitle . ', ' . $secondTitle,
            $this->subject->getSkillsShort()
        );
    }

    public function testGetNumberOfSkillsWithNoSkillReturnsZero()
    {
        self::assertEquals(
            0,
            $this->subject->getNumberOfSkills()
        );
    }

    public function testGetNumberOfSkillsWithSingleSkillReturnsOne()
    {
        $this->addSkillRelation([]);
        self::assertEquals(
            1,
            $this->subject->getNumberOfSkills()
        );
    }

    public function testGetNumberOfSkillsWithTwoSkillsReturnsTwo()
    {
        $this->addSkillRelation([]);
        $this->addSkillRelation([]);
        self::assertEquals(
            2,
            $this->subject->getNumberOfSkills()
        );
    }

    public function testGetNotes()
    {
        self::assertEquals(
            '',
            $this->subject->getNotes()
        );
        self::assertEquals(
            'test notes',
            $this->maximalFixture->getNotes()
        );
    }

    public function testGetAddress()
    {
        self::assertEquals(
            '',
            $this->subject->getAddress()
        );
        self::assertEquals(
            'test address',
            $this->maximalFixture->getAddress()
        );
    }

    public function testGetPhoneWork()
    {
        self::assertEquals(
            '',
            $this->subject->getPhoneWork()
        );
        self::assertEquals(
            '123',
            $this->maximalFixture->getPhoneWork()
        );
    }

    public function testGetPhoneHome()
    {
        self::assertEquals(
            '',
            $this->subject->getPhoneHome()
        );
        self::assertEquals(
            '456',
            $this->maximalFixture->getPhoneHome()
        );
    }

    public function testGetPhoneMobile()
    {
        self::assertEquals(
            '',
            $this->subject->getPhoneMobile()
        );
        self::assertEquals(
            '789',
            $this->maximalFixture->getPhoneMobile()
        );
    }

    public function testGetFax()
    {
        self::assertEquals(
            '',
            $this->subject->getFax()
        );
        self::assertEquals(
            '000',
            $this->maximalFixture->getFax()
        );
    }

    public function testGetEmail()
    {
        self::assertEquals(
            'foo@test.com',
            $this->subject->getEmail()
        );
        self::assertEquals(
            'maximal-foo@test.com',
            $this->maximalFixture->getEmail()
        );
    }

    ////////////////////////
    // Tests for getGender
    ////////////////////////

    public function testGetGenderForNoGenderSetReturnsUnknownGenderValue()
    {
        self::assertEquals(
            \Tx_Seminars_OldModel_Speaker::GENDER_UNKNOWN,
            $this->subject->getGender()
        );
    }

    public function testGetGenderForKnownGenderReturnsGender()
    {
        $this->subject->setGender(\Tx_Seminars_OldModel_Speaker::GENDER_MALE);

        self::assertEquals(
            \Tx_Seminars_OldModel_Speaker::GENDER_MALE,
            $this->subject->getGender()
        );
    }

    //////////////////////////////////////////
    // Tests concerning hasCancelationPeriod
    //////////////////////////////////////////

    public function testHasCancelationPeriodForSpeakerWithoutCancelationPeriodReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasCancelationPeriod()
        );
    }

    public function testHasCancelationPeriodForSpeakerWithCancelationPeriodReturnsTrue()
    {
        $this->subject->setCancelationPeriod(42);

        self::assertTrue(
            $this->subject->hasCancelationPeriod()
        );
    }

    ////////////////////////////////////////////////
    // Tests concerning getCancelationPeriodInDays
    ////////////////////////////////////////////////

    public function testGetCancelationPeriodInDaysForSpeakerWithoutCancelationPeriodReturnsZero()
    {
        self::assertEquals(
            0,
            $this->subject->getCancelationPeriodInDays()
        );
    }

    public function testGetCancelationPeriodInDaysForSpeakerWithCancelationPeriodOfOneDayReturnsOne()
    {
        $this->subject->setCancelationPeriod(1);

        self::assertEquals(
            1,
            $this->subject->getCancelationPeriodInDays()
        );
    }

    public function testGetCancelationPeriodInDaysForSpeakerWithCancelationPeriodOfTwoDaysReturnsTwo()
    {
        $this->subject->setCancelationPeriod(2);

        self::assertEquals(
            2,
            $this->subject->getCancelationPeriodInDays()
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
        self::assertNull(
            $this->subject->getOwner()
        );
    }

    /**
     * @test
     */
    public function getOwnerWithOwnerReturnsOwner()
    {
        $frontEndUser = \Tx_Oelib_MapperRegistry::get(
            \Tx_Seminars_Mapper_FrontEndUser::class
        )->getNewGhost();
        $this->subject->setOwner($frontEndUser);

        self::assertSame(
            $frontEndUser,
            $this->subject->getOwner()
        );
    }
}
