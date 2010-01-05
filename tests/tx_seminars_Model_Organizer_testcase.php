<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Niels Pardon (mail@niels-pardon.de)
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
 * Testcase for the 'organizer model' class in the 'seminars' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Model_Organizer_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_Organizer
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_seminars_Model_Organizer();
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
			'Exception', 'The parameter $name must not be empty.'
		);

		$this->fixture->setName('');
	}

	/**
	 * @test
	 */
	public function setNameSetsName() {
		$this->fixture->setName('Fabulous organizer');

		$this->assertEquals(
			'Fabulous organizer',
			$this->fixture->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameWithNonEmptyNameReturnsName() {
		$this->fixture->setData(array('title' => 'Fabulous organizer'));

		$this->assertEquals(
			'Fabulous organizer',
			$this->fixture->getName()
		);
	}

	/**
	 * @test
	 */
	public function getTitleWithNonEmptyNameReturnsName() {
		$this->fixture->setData(array('title' => 'Fabulous organizer'));

		$this->assertEquals(
			'Fabulous organizer',
			$this->fixture->getTitle()
		);
	}


	//////////////////////////////////
	// Tests regarding the homepage.
	//////////////////////////////////

	/**
	 * @test
	 */
	public function getHomepageInitiallyReturnsAnEmptyString() {
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
	public function hasHomepageInitiallyReturnsFalse() {
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


	////////////////////////////////////////
	// Tests regarding the e-mail address.
	////////////////////////////////////////

	/**
	 * @test
	 */
	public function setEMailAddressWithEmptyEMailAddressThrowsException() {
		$this->setExpectedException(
			'Exception', 'The parameter $eMailAddress must not be empty.'
		);

		$this->fixture->setEMailAddress('');
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
	public function getEMailAddressWithNonEmptyEMailAddressReturnsEMailAddress() {
		$this->fixture->setData(array('email' => 'mail@example.com'));

		$this->assertEquals(
			'mail@example.com',
			$this->fixture->getEMailAddress()
		);
	}


	///////////////////////////////////////
	// Tests regarding the e-mail footer.
	///////////////////////////////////////

	/**
	 * @test
	 */
	public function getEMailFooterInitiallyReturnsAnEmptyString() {
		$this->fixture->setData(array());

		$this->assertEquals(
			'',
			$this->fixture->getEMailFooter()
		);
	}

	/**
	 * @test
	 */
	public function getEMailFooterWithNonEmptyEMailFooterReturnsEMailFooter() {
		$this->fixture->setData(array('email_footer' => 'Example Inc.'));

		$this->assertEquals(
			'Example Inc.',
			$this->fixture->getEMailFooter()
		);
	}

	/**
	 * @test
	 */
	public function setEMailFooterSetsEMailFooter() {
		$this->fixture->setEMailFooter('Example Inc.');

		$this->assertEquals(
			'Example Inc.',
			$this->fixture->getEMailFooter()
		);
	}

	/**
	 * @test
	 */
	public function hasEMailFooterInitiallyReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasEMailFooter()
		);
	}

	/**
	 * @test
	 */
	public function hasEMailFooterWithNonEmptyEMailFooterReturnsTrue() {
		$this->fixture->setEMailFooter('Example Inc.');

		$this->assertTrue(
			$this->fixture->hasEMailFooter()
		);
	}


	/////////////////////////////////////////
	// Tests regarding the attendances PID.
	/////////////////////////////////////////

	/**
	 * @test
	 */
	public function getAttendancesPIDInitiallyReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getAttendancesPID()
		);
	}

	/**
	 * @test
	 */
	public function getAttendancesPIDWithAttendancesPIDReturnsAttendancesPID() {
		$this->fixture->setData(array('attendances_pid' => 42));

		$this->assertEquals(
			42,
			$this->fixture->getAttendancesPID()
		);
	}

	/**
	 * @test
	 */
	public function setAttendancesPIDWithPositiveAttendancesPIDSetsAttendancesPID() {
		$this->fixture->setAttendancesPID(42);

		$this->assertEquals(
			42,
			$this->fixture->getAttendancesPID()
		);
	}

	/**
	 * @test
	 */
	public function setAttendancesPIDWithZeroAttendancesPIDSetsAttendancesPID() {
		$this->fixture->setAttendancesPID(0);

		$this->assertEquals(
			0,
			$this->fixture->getAttendancesPID()
		);
	}

	/**
	 * @test
	 */
	public function setAttendancesPIDWithNegativeAttendancesPIDThrowsException() {
		$this->setExpectedException('Exception', '');

		$this->fixture->setAttendancesPID(-1);
	}

	/**
	 * @test
	 */
	public function hasAttendancesPIDInitiallyReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasAttendancesPID()
		);
	}

	/**
	 * @test
	 */
	public function hasAttendancesPIDWithAttendancesPIDReturnsTrue() {
		$this->fixture->setAttendancesPID(42);

		$this->assertTrue(
			$this->fixture->hasAttendancesPID()
		);
	}


	/////////////////////////////////////
	// Tests concerning the description
	/////////////////////////////////////

	public function test_hasDescription_ForOrganizerWithoutDescription_ReturnsFalse() {
		$this->fixture->setData(array('description' => ''));

		$this->assertFalse(
			$this->fixture->hasDescription()
		);
	}

	public function test_hasDescription_ForOrganizerWithDescription_ReturnsTrue() {
		$this->fixture->setData(array('description' => 'foo'));

		$this->assertTrue(
			$this->fixture->hasDescription()
		);
	}

	public function test_getDescription_ForOrganizerWithoutDescription_ReturnsEmptyString() {
		$this->fixture->setData(array('description' => ''));

		$this->assertEquals(
			'',
			$this->fixture->getDescription()
		);
	}

	public function test_getDescription_ForOrganizerWithDescription_ReturnsDescription() {
		$this->fixture->setData(array('description' => 'foo'));

		$this->assertEquals(
			'foo',
			$this->fixture->getDescription()
		);
	}
}
?>