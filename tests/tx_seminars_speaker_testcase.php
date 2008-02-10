<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Oliver Klee (typo3-coding@oliverklee.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Testcase for the speaker class in the 'seminars' extensions.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('seminars').'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars').'tests/fixtures/class.tx_seminars_speakerchild.php');

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_testingFramework.php');

class tx_seminars_speaker_testcase extends tx_phpunit_testcase {
	private $fixture;
	private $testingFramework;

	/** a maximal filled speaker */
	private $maximalFixture;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$fixtureUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
			array(
				'title' => 'Test speaker',
				'email' => 'foo@test.com'
			)
		);
		$this->fixture = new tx_seminars_speakerchild($fixtureUid);

		$maximalFixtureUid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SPEAKERS,
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
		$this->maximalFixture = new tx_seminars_speakerchild($maximalFixtureUid);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();
		unset($this->fixture);
		unset($this->testingFramework);
	}


	///////////////////////
	// Utility functions.
	///////////////////////

	/**
	 * Inserts a skill record into the database and creates a relation to it
	 * from the fixture.
	 *
	 * @param	array		data of the skill to add, may be empty
	 *
	 * @return	integer		the UID of the created record, will always be > 0
	 */
	private function addSkillRelation(array $skillData) {
		$uid = $this->testingFramework->createRecord(
			SEMINARS_TABLE_SKILLS, $skillData
		);

		$this->testingFramework->createRelation(
			SEMINARS_TABLE_SPEAKERS_SKILLS_MM,
			$this->fixture->getUid(), $uid
		);
		$this->fixture->setNumberOfSkills(
			$this->fixture->getNumberOfSkills() + 1
		);

		return $uid;
	}




	/////////////////////////////////////
	// Tests for the utility functions.
	/////////////////////////////////////

	public function testAddSkillRelationReturnsUid() {
		$this->assertTrue(
			$this->addSkillRelation(array()) > 0
		);
	}

	public function testAddSkillRelationCreatesNewUids() {
		$this->assertNotEquals(
			$this->addSkillRelation(array()),
			$this->addSkillRelation(array())
		);
	}

	public function testAddSkillRelationIncreasesTheNumberOfSkills() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfSkills()
		);

		$this->addSkillRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfSkills()
		);

		$this->addSkillRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfSkills()
		);
	}

	public function testAddSkillRelationCreatesRelations() {
		$this->assertEquals(
			0,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SPEAKERS_SKILLS_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addSkillRelation(array());
		$this->assertEquals(
			1,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SPEAKERS_SKILLS_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);

		$this->addSkillRelation(array());
		$this->assertEquals(
			2,
			$this->testingFramework->countRecords(
				SEMINARS_TABLE_SPEAKERS_SKILLS_MM,
				'uid_local='.$this->fixture->getUid()
			)
		);
	}


	////////////////////////////////////////
	// Tests for creating speaker objects.
	////////////////////////////////////////

	public function testCreateFromUid() {
		$this->assertTrue(
			$this->fixture->isOk()
		);
	}


	/////////////////////////////////////////////
	// Tests for getting the speaker attributes.
	/////////////////////////////////////////////

	public function testGetOrganization() {
		$this->assertEquals(
			'',
			$this->fixture->getOrganization()
		);
		$this->assertEquals(
			'Foo inc.',
			$this->maximalFixture->getOrganization()
		);
	}

	public function testGetHomepage() {
		$this->assertEquals(
			'',
			$this->fixture->getHomepage()
		);
		$this->assertEquals(
			'http://www.test.com/',
			$this->maximalFixture->getHomepage()
		);
	}

	/*
	 * TODO: For this test to work properly, we need a more-or-less working
	 * front-end environment so that the RTE transformation functions work.
	 *
	 * @see		https://bugs.oliverklee.com/show_bug.cgi?id=1425
	 *

	public function testDescription() {
		$plugin = new tx_seminars_pi1();
		$plugin->init(array());

		$this->assertEquals(
			'',
			$this->fixture->getDescription($plugin)
		);
		$this->assertEquals(
			'<p>foo</p><p>bar</p>',
			$this->maximalFixture->getDescription($plugin)
		);
	}

	*/

	public function testHasSkillsInitiallyIsFalse() {
		$this->assertFalse(
			$this->fixture->hasSkills()
		);
	}

	public function testCanHaveOneSkill() {
		$this->addSkillRelation(array());
		$this->assertTrue(
			$this->fixture->hasSkills()
		);
	}

	public function testGetSkillsShortWithNoSkillReturnsAnEmptyString() {
		$this->assertEquals(
			'',
			$this->fixture->getSkillsShort()
		);
	}

	public function testGetSkillsShortWithSingleSkillReturnsASingleSkill() {
		$title = 'Test title';
		$this->addSkillRelation(array('title' => $title));

		$this->assertContains(
			$title,
			$this->fixture->getSkillsShort()
		);
	}

	public function testGetSkillsShortWithMultipleSkillsReturnsMultipleSkills() {
		$firstTitle = 'Skill 1';
		$secondTitle = 'Skill 2';
		$this->addSkillRelation(array('title' => $firstTitle));
		$this->addSkillRelation(array('title' => $secondTitle));

		$this->assertEquals(
			$firstTitle.', '.$secondTitle,
			$this->fixture->getSkillsShort()
		);
	}

	public function testGetNumberOfSkillsWithNoSkillReturnsZero() {
		$this->assertEquals(
			0,
			$this->fixture->getNumberOfSkills()
		);
	}

	public function testGetNumberOfSkillsWithSingleSkillReturnsOne() {
		$this->addSkillRelation(array());
		$this->assertEquals(
			1,
			$this->fixture->getNumberOfSkills()
		);
	}

	public function testGetNumberOfSkillsWithTwoSkillsReturnsTwo() {
		$this->addSkillRelation(array());
		$this->addSkillRelation(array());
		$this->assertEquals(
			2,
			$this->fixture->getNumberOfSkills()
		);
	}

	public function testGetNotes() {
		$this->assertEquals(
			'',
			$this->fixture->getNotes()
		);
		$this->assertEquals(
			'test notes',
			$this->maximalFixture->getNotes()
		);
	}

	public function testGetAddress() {
		$this->assertEquals(
			'',
			$this->fixture->getAddress()
		);
		$this->assertEquals(
			'test address',
			$this->maximalFixture->getAddress()
		);
	}

	public function testGetPhoneWork() {
		$this->assertEquals(
			'',
			$this->fixture->getPhoneWork()
		);
		$this->assertEquals(
			'123',
			$this->maximalFixture->getPhoneWork()
		);
	}

	public function testGetPhoneHome() {
		$this->assertEquals(
			'',
			$this->fixture->getPhoneHome()
		);
		$this->assertEquals(
			'456',
			$this->maximalFixture->getPhoneHome()
		);
	}

	public function testGetPhoneMobile() {
		$this->assertEquals(
			'',
			$this->fixture->getPhoneMobile()
		);
		$this->assertEquals(
			'789',
			$this->maximalFixture->getPhoneMobile()
		);
	}

	public function testGetFax() {
		$this->assertEquals(
			'',
			$this->fixture->getFax()
		);
		$this->assertEquals(
			'000',
			$this->maximalFixture->getFax()
		);
	}

	public function testGetEmail() {
		$this->assertEquals(
			'foo@test.com',
			$this->fixture->getEmail()
		);
		$this->assertEquals(
			'maximal-foo@test.com',
			$this->maximalFixture->getEmail()
		);
	}
}

?>
