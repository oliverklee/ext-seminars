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
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_OldModel_SpeakerTest extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	/**
	 * @var tx_seminars_speaker
	 */
	private $fixture;

	/**
	 * @var tx_seminars_speaker a maximal filled speaker
	 */
	private $maximalFixture;

	protected function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$fixtureUid = $this->testingFramework->createRecord(
			'tx_seminars_speakers',
			array(
				'title' => 'Test speaker',
				'email' => 'foo@test.com'
			)
		);
		$this->fixture = new tx_seminars_speaker($fixtureUid);

		$maximalFixtureUid = $this->testingFramework->createRecord(
			'tx_seminars_speakers',
			array(
				'title' => 'Test speaker',
				'organization' => 'Foo inc.',
				'homepage' => 'http://www.test.com/',
				'description' => 'foo'.LF.'bar',
				'notes' => 'test notes',
				'address' => 'test address',
				'phone_work' => '123',
				'phone_home' => '456',
				'phone_mobile' => '789',
				'fax' => '000',
				'email' => 'maximal-foo@test.com'
			)
		);
		$this->maximalFixture = new tx_seminars_speaker($maximalFixtureUid);
	}

	protected function tearDown() {
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
	private function addSkillRelation(array $skillData) {
		$uid = $this->testingFramework->createRecord(
			'tx_seminars_skills', $skillData
		);

		$this->testingFramework->createRelationAndUpdateCounter(
			'tx_seminars_speakers',
			$this->fixture->getUid(), $uid, 'skills'
		);

		$this->fixture = new tx_seminars_speaker($this->fixture->getUid());

		return $uid;
	}




	/////////////////////////////////////
	// Tests for the utility functions.
	/////////////////////////////////////

	public function testAddSkillRelationReturnsUid() {
		self::assertTrue(
			$this->addSkillRelation(array()) > 0
		);
	}

	public function testAddSkillRelationCreatesNewUids() {
		self::assertNotEquals(
			$this->addSkillRelation(array()),
			$this->addSkillRelation(array())
		);
	}

	public function testAddSkillRelationIncreasesTheNumberOfSkills() {
		self::assertEquals(
			0,
			$this->fixture->getNumberOfSkills()
		);

		$this->addSkillRelation(array());
		self::assertEquals(
			1,
			$this->fixture->getNumberOfSkills()
		);

		$this->addSkillRelation(array());
		self::assertEquals(
			2,
			$this->fixture->getNumberOfSkills()
		);
	}

	public function testAddSkillRelationCreatesRelations() {
		self::assertEquals(
			0,
			$this->testingFramework->countRecords(
				'tx_seminars_speakers_skills_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addSkillRelation(array());
		self::assertEquals(
			1,
			$this->testingFramework->countRecords(
				'tx_seminars_speakers_skills_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addSkillRelation(array());
		self::assertEquals(
			2,
			$this->testingFramework->countRecords(
				'tx_seminars_speakers_skills_mm',
				'uid_local='.$this->fixture->getUid()
			)
		);
	}


	////////////////////////////////////////
	// Tests for creating speaker objects.
	////////////////////////////////////////

	public function testCreateFromUid() {
		self::assertTrue(
			$this->fixture->isOk()
		);
	}


	/////////////////////////////////////////////
	// Tests for getting the speaker attributes.
	/////////////////////////////////////////////

	public function testGetOrganization() {
		self::assertEquals(
			'',
			$this->fixture->getOrganization()
		);
		self::assertEquals(
			'Foo inc.',
			$this->maximalFixture->getOrganization()
		);
	}

	public function testHasOrganizationWithNoOrganizationReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasOrganization()
		);
	}

	public function testHasOrganizationWithOrganizationReturnsTrue() {
		self::assertTrue(
			$this->maximalFixture->hasOrganization()
		);
	}

	public function testGetHomepage() {
		self::assertEquals(
			'',
			$this->fixture->getHomepage()
		);
		self::assertEquals(
			'http://www.test.com/',
			$this->maximalFixture->getHomepage()
		);
	}

	public function testHasHomepageWithNoHomepageReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasHomepage()
		);
	}

	public function testHasHomepageWithHomepageReturnsTrue() {
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
		$plugin = new tx_seminars_FrontEnd_DefaultController();
		$plugin->init(array());

		self::assertEquals(
			'',
			$this->fixture->getDescription($plugin)
		);
		self::assertEquals(
			'<p>foo</p><p>bar</p>',
			$this->maximalFixture->getDescription($plugin)
		);
	}

	*/

	public function testHasDescriptionWithNoDescriptionReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasDescription()
		);
	}

	public function testHasDescriptionWithDescriptionReturnsTrue() {
		self::assertTrue(
			$this->maximalFixture->hasDescription()
		);
	}

	public function testHasSkillsInitiallyIsFalse() {
		self::assertFalse(
			$this->fixture->hasSkills()
		);
	}

	/**
	 * @test
	 */
	public function canHaveOneSkill() {
		$this->addSkillRelation(array());
		self::assertTrue(
			$this->fixture->hasSkills()
		);
	}

	public function testGetSkillsShortWithNoSkillReturnsAnEmptyString() {
		self::assertEquals(
			'',
			$this->fixture->getSkillsShort()
		);
	}

	public function testGetSkillsShortWithSingleSkillReturnsASingleSkill() {
		$title = 'Test title';
		$this->addSkillRelation(array('title' => $title));

		self::assertContains(
			$title,
			$this->fixture->getSkillsShort()
		);
	}

	public function testGetSkillsShortWithMultipleSkillsReturnsMultipleSkills() {
		$firstTitle = 'Skill 1';
		$secondTitle = 'Skill 2';
		$this->addSkillRelation(array('title' => $firstTitle));
		$this->addSkillRelation(array('title' => $secondTitle));

		self::assertEquals(
			$firstTitle.', '.$secondTitle,
			$this->fixture->getSkillsShort()
		);
	}

	public function testGetNumberOfSkillsWithNoSkillReturnsZero() {
		self::assertEquals(
			0,
			$this->fixture->getNumberOfSkills()
		);
	}

	public function testGetNumberOfSkillsWithSingleSkillReturnsOne() {
		$this->addSkillRelation(array());
		self::assertEquals(
			1,
			$this->fixture->getNumberOfSkills()
		);
	}

	public function testGetNumberOfSkillsWithTwoSkillsReturnsTwo() {
		$this->addSkillRelation(array());
		$this->addSkillRelation(array());
		self::assertEquals(
			2,
			$this->fixture->getNumberOfSkills()
		);
	}

	public function testGetNotes() {
		self::assertEquals(
			'',
			$this->fixture->getNotes()
		);
		self::assertEquals(
			'test notes',
			$this->maximalFixture->getNotes()
		);
	}

	public function testGetAddress() {
		self::assertEquals(
			'',
			$this->fixture->getAddress()
		);
		self::assertEquals(
			'test address',
			$this->maximalFixture->getAddress()
		);
	}

	public function testGetPhoneWork() {
		self::assertEquals(
			'',
			$this->fixture->getPhoneWork()
		);
		self::assertEquals(
			'123',
			$this->maximalFixture->getPhoneWork()
		);
	}

	public function testGetPhoneHome() {
		self::assertEquals(
			'',
			$this->fixture->getPhoneHome()
		);
		self::assertEquals(
			'456',
			$this->maximalFixture->getPhoneHome()
		);
	}

	public function testGetPhoneMobile() {
		self::assertEquals(
			'',
			$this->fixture->getPhoneMobile()
		);
		self::assertEquals(
			'789',
			$this->maximalFixture->getPhoneMobile()
		);
	}

	public function testGetFax() {
		self::assertEquals(
			'',
			$this->fixture->getFax()
		);
		self::assertEquals(
			'000',
			$this->maximalFixture->getFax()
		);
	}

	public function testGetEmail() {
		self::assertEquals(
			'foo@test.com',
			$this->fixture->getEmail()
		);
		self::assertEquals(
			'maximal-foo@test.com',
			$this->maximalFixture->getEmail()
		);
	}


	////////////////////////
	// Tests for getGender
	////////////////////////

	public function testGetGenderForNoGenderSetReturnsUnknownGenderValue() {
		self::assertEquals(
			tx_seminars_speaker::GENDER_UNKNOWN,
			$this->fixture->getGender()
		);
	}

	public function testGetGenderForKnownGenderReturnsGender() {
		$this->fixture->setGender(tx_seminars_speaker::GENDER_MALE);

		self::assertEquals(
			tx_seminars_speaker::GENDER_MALE,
			$this->fixture->getGender()
		);
	}


	//////////////////////////////////////////
	// Tests concerning hasCancelationPeriod
	//////////////////////////////////////////

	public function testHasCancelationPeriodForSpeakerWithoutCancelationPeriodReturnsFalse() {
		self::assertFalse(
			$this->fixture->hasCancelationPeriod()
		);
	}

	public function testHasCancelationPeriodForSpeakerWithCancelationPeriodReturnsTrue() {
		$this->fixture->setCancelationPeriod(42);

		self::assertTrue(
			$this->fixture->hasCancelationPeriod()
		);
	}


	////////////////////////////////////////////////
	// Tests concerning getCancelationPeriodInDays
	////////////////////////////////////////////////

	public function testGetCancelationPeriodInDaysForSpeakerWithoutCancelationPeriodReturnsZero() {
		self::assertEquals(
			0,
			$this->fixture->getCancelationPeriodInDays()
		);
	}

	public function testGetCancelationPeriodInDaysForSpeakerWithCancelationPeriodOfOneDayReturnsOne() {
		$this->fixture->setCancelationPeriod(1);

		self::assertEquals(
			1,
			$this->fixture->getCancelationPeriodInDays()
		);
	}

	public function testGetCancelationPeriodInDaysForSpeakerWithCancelationPeriodOfTwoDaysReturnsTwo() {
		$this->fixture->setCancelationPeriod(2);

		self::assertEquals(
			2,
			$this->fixture->getCancelationPeriodInDays()
		);
	}


	///////////////////////////////
	// Tests regarding the owner.
	///////////////////////////////

	/**
	 * @test
	 */
	public function getOwnerWithoutOwnerReturnsNull() {
		self::assertNull(
			$this->fixture->getOwner()
		);
	}

	/**
	 * @test
	 */
	public function getOwnerWithOwnerReturnsOwner() {
		$frontEndUser = tx_oelib_MapperRegistry::get(
			'tx_seminars_Mapper_FrontEndUser'
		)->getNewGhost();
		$this->fixture->setOwner($frontEndUser);

		self::assertSame(
			$frontEndUser,
			$this->fixture->getOwner()
		);
	}
}