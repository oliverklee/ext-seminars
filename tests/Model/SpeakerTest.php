<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2012 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

/**
 * Testcase for the 'speaker model' class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Model_SpeakerTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_Speaker
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_seminars_Model_Speaker();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	///////////////////////////////
	// Tests regarding the name.
	///////////////////////////////

	/**
	 * @test
	 */
	public function setNameWithEmptyNameThrowsException() {
		$this->setExpectedException(
			'InvalidArgumentException',
			'The parameter $name must not be empty.'
		);

		$this->fixture->setName('');
	}

	/**
	 * @test
	 */
	public function setNameSetsName() {
		$this->fixture->setName('John Doe');

		$this->assertEquals(
			'John Doe',
			$this->fixture->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameWithNonEmptyNameReturnsName() {
		$this->fixture->setData(array('title' => 'John Doe'));

		$this->assertEquals(
			'John Doe',
			$this->fixture->getName()
		);
	}


	//////////////////////////////////////
	// Tests regarding the organization.
	//////////////////////////////////////

	/**
	 * @test
	 */
	public function getOrganizationWithoutOrganizationReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getOrganization()
		);
	}

	/**
	 * @test
	 */
	public function getOrganizationWithNonEmptyOrganizationReturnsOrganization() {
		$this->fixture->setData(array('organization' => 'Happy organization'));

		$this->assertEquals(
			'Happy organization',
			$this->fixture->getOrganization()
		);
	}

	/**
	 * @test
	 */
	public function setOrganizationSetsOrganization() {
		$this->fixture->setOrganization('Happy organization');

		$this->assertEquals(
			'Happy organization',
			$this->fixture->getOrganization()
		);
	}

	/**
	 * @test
	 */
	public function hasOrganizationWithoutOrganizationReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasOrganization()
		);
	}

	/**
	 * @test
	 */
	public function hasOrganizationWithNonEmptyOrganizationReturnsTrue() {
		$this->fixture->setOrganization('Happy organization');

		$this->assertTrue(
			$this->fixture->hasOrganization()
		);
	}


	//////////////////////////////////
	// Tests regarding the homepage.
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getHomepageWithoutHomepageReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getHomepage()
		);
	}

	/**
	 * @test
	 */
	public function getHomepageWithNonEmptyHomepageReturnsHomepage() {
		$this->fixture->setData(array('homepage' => 'http://example.com'));

		$this->assertEquals(
			'http://example.com',
			$this->fixture->getHomepage()
		);
	}

	/**
	 * @test
	 */
	public function setHomepageSetsHomepage() {
		$this->fixture->setHomepage('http://example.com');

		$this->assertEquals(
			'http://example.com',
			$this->fixture->getHomepage()
		);
	}

	/**
	 * @test
	 */
	public function hasHomepageWithoutHomepageReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasHomepage()
		);
	}

	/**
	 * @test
	 */
	public function hasHomepageWithNonEmptyHomepageReturnsTrue() {
		$this->fixture->setHomepage('http://example.com');

		$this->assertTrue(
			$this->fixture->hasHomepage()
		);
	}


	/////////////////////////////////////
	// Tests regarding the description.
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function getDescriptionWithoutDescriptionReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function getDescriptionWithDescriptionReturnsDescription() {
		$this->fixture->setData(array('description' => 'This is a good speaker.'));

		$this->assertEquals(
			'This is a good speaker.',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function setDescriptionSetsDescription() {
		$this->fixture->setDescription('This is a good speaker.');

		$this->assertEquals(
			'This is a good speaker.',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function hasDescriptionWithoutDescriptionReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasDescription()
		);
	}

	/**
	 * @test
	 */
	public function hasDescriptionWithDescriptionReturnsTrue() {
		$this->fixture->setDescription('This is a good speaker.');

		$this->assertTrue(
			$this->fixture->hasDescription()
		);
	}


	//////////////////////////////////
	// Tests regarding the address.
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getAddressWithoutAddressReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getAddress()
		);
	}

	/**
	 * @test
	 */
	public function getAddressWithNonEmptyAddressReturnsAddress() {
		$this->fixture->setData(array('address' => 'Backstreet 42'));

		$this->assertEquals(
			'Backstreet 42',
			$this->fixture->getAddress()
		);
	}

	/**
	 * @test
	 */
	public function setAddressSetsAddress() {
		$this->fixture->setAddress('Backstreet 42');

		$this->assertEquals(
			'Backstreet 42',
			$this->fixture->getAddress()
		);
	}

	/**
	 * @test
	 */
	public function hasAddressWithoutAddressReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasAddress()
		);
	}

	/**
	 * @test
	 */
	public function hasAddressWithNonEmptyAddressReturnsTrue() {
		$this->fixture->setAddress('Backstreet 42');

		$this->assertTrue(
			$this->fixture->hasAddress()
		);
	}


	///////////////////////////////////////////////
	// Tests regarding the work telephone number.
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getPhoneWorkWithoutPhoneWorkReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getPhoneWork()
		);
	}

	/**
	 * @test
	 */
	public function getPhoneWorkWithPhoneWorkReturnsPhoneWork() {
		$this->fixture->setData(array('phone_work' => '12345'));

		$this->assertEquals(
			'12345',
			$this->fixture->getPhoneWork()
		);
	}

	/**
	 * @test
	 */
	public function setPhoneWorkSetsPhoneWork() {
		$this->fixture->setPhoneWork('12345');

		$this->assertEquals(
			'12345',
			$this->fixture->getPhoneWork()
		);
	}

	/**
	 * @test
	 */
	public function hasPhoneWorkWithoutPhoneWorkReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasPhoneWork()
		);
	}

	/**
	 * @test
	 */
	public function hasPhoneWorkWithPhoneWorkReturnsTrue() {
		$this->fixture->setPhoneWork('12345');

		$this->assertTrue(
			$this->fixture->hasPhoneWork()
		);
	}


	///////////////////////////////////////////////
	// Tests regarding the home telephone number.
	///////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getPhoneHomeWithoutPhoneHomeReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getPhoneHome()
		);
	}

	/**
	 * @test
	 */
	public function getPhoneHomeWithPhoneHomeReturnsPhoneHome() {
		$this->fixture->setData(array('phone_home' => '12345'));

		$this->assertEquals(
			'12345',
			$this->fixture->getPhoneHome()
		);
	}

	/**
	 * @test
	 */
	public function setPhoneHomeSetsPhoneHome() {
		$this->fixture->setPhoneHome('12345');

		$this->assertEquals(
			'12345',
			$this->fixture->getPhoneHome()
		);
	}

	/**
	 * @test
	 */
	public function hasPhoneHomeWithoutPhoneHomeReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasPhoneHome()
		);
	}

	/**
	 * @test
	 */
	public function hasPhoneHomeWithPhoneHomeReturnsTrue() {
		$this->fixture->setPhoneHome('12345');

		$this->assertTrue(
			$this->fixture->hasPhoneHome()
		);
	}


	/////////////////////////////////////////////////
	// Tests regarding the mobile telephone number.
	/////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getPhoneMobileWithoutPhoneMobileReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getPhoneMobile()
		);
	}

	/**
	 * @test
	 */
	public function getPhoneMobileWithPhoneMobileReturnsPhoneMobile() {
		$this->fixture->setData(array('phone_mobile' => '12345'));

		$this->assertEquals(
			'12345',
			$this->fixture->getPhoneMobile()
		);
	}

	/**
	 * @test
	 */
	public function setPhoneMobileSetsPhoneMobile() {
		$this->fixture->setPhoneMobile('12345');

		$this->assertEquals(
			'12345',
			$this->fixture->getPhoneMobile()
		);
	}

	/**
	 * @test
	 */
	public function hasPhoneMobileWithoutPhoneMobileReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasPhoneMobile()
		);
	}

	/**
	 * @test
	 */
	public function hasPhoneMobileWithPhoneMobileReturnsTrue() {
		$this->fixture->setPhoneMobile('12345');

		$this->assertTrue(
			$this->fixture->hasPhoneMobile()
		);
	}


	////////////////////////////////////
	// Tests regarding the fax number.
	////////////////////////////////////

	/**
	 * @test
	 */
	public function getFaxWithoutFaxReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getFax()
		);
	}

	/**
	 * @test
	 */
	public function getFaxWithFaxReturnsFax() {
		$this->fixture->setData(array('fax' => '12345'));

		$this->assertEquals(
			'12345',
			$this->fixture->getFax()
		);
	}

	/**
	 * @test
	 */
	public function setFaxSetsFax() {
		$this->fixture->setFax('12345');

		$this->assertEquals(
			'12345',
			$this->fixture->getFax()
		);
	}

	/**
	 * @test
	 */
	public function hasFaxWithoutFaxReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasFax()
		);
	}

	/**
	 * @test
	 */
	public function hasFaxWithFaxReturnsTrue() {
		$this->fixture->setFax('12345');

		$this->assertTrue(
			$this->fixture->hasFax()
		);
	}


	////////////////////////////////////////
	// Tests regarding the e-mail address.
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function getEMailAddressWithoutEMailAddressReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getEMailAddress()
		);
	}

	/**
	 * @test
	 */
	public function getEMailAddressWithNonEmptyEMailAddressReturnsEMailAddress() {
		$this->fixture->setData(array('email' => 'mail@example.com'));

		$this->assertEquals(
			'mail@example.com',
			$this->fixture->getEMailAddress()
		);
	}

	/**
	 * @test
	 */
	public function setEMailAddressSetsEMailAddress() {
		$this->fixture->setEMailAddress('mail@example.com');

		$this->assertEquals(
			'mail@example.com',
			$this->fixture->getEMailAddress()
		);
	}

	/**
	 * @test
	 */
	public function hasEMailAddressWithoutEMailAddressReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasEMailAddress()
		);
	}

	/**
	 * @test
	 */
	public function hasEMailAddressWithEMailAddressReturnsTrue() {
		$this->fixture->setEMailAddress('mail@example.com');

		$this->assertTrue(
			$this->fixture->hasEMailAddress()
		);
	}


	////////////////////////////////
	// Tests regarding the gender.
	////////////////////////////////

	/**
	 * @test
	 */
	public function getGenderWithoutGenderReturnsUnknownGender() {
		$this->fixture->setData(array());

		$this->assertEquals(
			tx_seminars_Model_Speaker::GENDER_UNKNOWN,
			$this->fixture->getGender()
		);
	}

	/**
	 * @test
	 */
	public function getGenderWithGenderMaleReturnsMaleGender() {
		$this->fixture->setData(
			array('gender' => tx_seminars_Model_Speaker::GENDER_MALE)
		);

		$this->assertEquals(
			tx_seminars_Model_Speaker::GENDER_MALE,
			$this->fixture->getGender()
		);
	}

	/**
	 * @test
	 */
	public function getGenderWithGenderFemaleReturnsFemaleGender() {
		$this->fixture->setData(
			array('gender' => tx_seminars_Model_Speaker::GENDER_FEMALE)
		);

		$this->assertEquals(
			tx_seminars_Model_Speaker::GENDER_FEMALE,
			$this->fixture->getGender()
		);
	}

	/**
	 * @test
	 */
	public function setGenderSetsGender() {
		$this->fixture->setGender(tx_seminars_Model_Speaker::GENDER_MALE);

		$this->assertEquals(
			tx_seminars_Model_Speaker::GENDER_MALE,
			$this->fixture->getGender()
		);
	}

	/**
	 * @test
	 */
	public function hasGenderWithoutGenderReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasGender()
		);
	}

	/**
	 * @test
	 */
	public function hasGenderWithGenderMaleReturnsTrue() {
		$this->fixture->setGender(tx_seminars_Model_Speaker::GENDER_MALE);

		$this->assertTrue(
			$this->fixture->hasGender()
		);
	}

	/**
	 * @test
	 */
	public function hasGenderWithGenderFemaleReturnsTrue() {
		$this->fixture->setGender(tx_seminars_Model_Speaker::GENDER_FEMALE);

		$this->assertTrue(
			$this->fixture->hasGender()
		);
	}


	//////////////////////////////
	// Tests regarding the notes
	//////////////////////////////

	/**
	 * @test
	 */
	public function getNotesWithoutNotesReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function getNotesWithNonEmptyNotesReturnsNotes() {
		$this->fixture->setData(array('notes' => 'Nothing of interest.'));

		$this->assertEquals(
			'Nothing of interest.',
			$this->fixture->getNotes()
		);
	}

	/**
	 * @test
	 */
	public function setNotesSetsNotes() {
		$this->fixture->setNotes('Nothing of interest.');

		$this->assertEquals(
			'Nothing of interest.',
			$this->fixture->getNotes()
		);
	}


	///////////////////////////////
	// Test regarding the skills.
	///////////////////////////////

	/**
	 * @test
	 */
	public function setSkillsSetsSkills() {
		$skills = new tx_oelib_List();
		$this->fixture->setSkills($skills);

		$this->assertSame(
			$skills,
			$this->fixture->getSkills()
		);
	}
}
?>