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
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_Model_OrganizerTest extends tx_phpunit_testcase {
	/**
	 * @var tx_seminars_Model_Organizer
	 */
	private $fixture;

	protected function setUp() {
		$this->fixture = new tx_seminars_Model_Organizer();
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
		$this->fixture->setName('Fabulous organizer');

		self::assertEquals(
			'Fabulous organizer',
			$this->fixture->getName()
		);
	}

	/**
	 * @test
	 */
	public function getNameWithNonEmptyNameReturnsName() {
		$this->fixture->setData(array('title' => 'Fabulous organizer'));

		self::assertEquals(
			'Fabulous organizer',
			$this->fixture->getName()
		);
	}

	/**
	 * @test
	 */
	public function getTitleWithNonEmptyNameReturnsName() {
		$this->fixture->setData(array('title' => 'Fabulous organizer'));

		self::assertEquals(
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

		self::assertEquals(
			'',
			$this->fixture->getHomepage()
		);
	}

	/**
	 * @test
	 */
	public function getHomepageWithNonEmptyHomepageReturnsHomepage() {
		$this->fixture->setData(array('homepage' => 'http://example.com'));

		self::assertEquals(
			'http://example.com',
			$this->fixture->getHomepage()
		);
	}

	/**
	 * @test
	 */
	public function setHomepageSetsHomepage() {
		$this->fixture->setHomepage('http://example.com');

		self::assertEquals(
			'http://example.com',
			$this->fixture->getHomepage()
		);
	}

	/**
	 * @test
	 */
	public function hasHomepageInitiallyReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasHomepage()
		);
	}

	/**
	 * @test
	 */
	public function hasHomepageWithNonEmptyHomepageReturnsTrue() {
		$this->fixture->setHomepage('http://example.com');

		self::assertTrue(
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
			'InvalidArgumentException',
			'The parameter $eMailAddress must not be empty.'
		);

		$this->fixture->setEMailAddress('');
	}

	/**
	 * @test
	 */
	public function setEMailAddressSetsEMailAddress() {
		$this->fixture->setEMailAddress('mail@example.com');

		self::assertEquals(
			'mail@example.com',
			$this->fixture->getEMailAddress()
		);
	}

	/**
	 * @test
	 */
	public function getEMailAddressWithNonEmptyEMailAddressReturnsEMailAddress() {
		$this->fixture->setData(array('email' => 'mail@example.com'));

		self::assertEquals(
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

		self::assertEquals(
			'',
			$this->fixture->getEMailFooter()
		);
	}

	/**
	 * @test
	 */
	public function getEMailFooterWithNonEmptyEMailFooterReturnsEMailFooter() {
		$this->fixture->setData(array('email_footer' => 'Example Inc.'));

		self::assertEquals(
			'Example Inc.',
			$this->fixture->getEMailFooter()
		);
	}

	/**
	 * @test
	 */
	public function setEMailFooterSetsEMailFooter() {
		$this->fixture->setEMailFooter('Example Inc.');

		self::assertEquals(
			'Example Inc.',
			$this->fixture->getEMailFooter()
		);
	}

	/**
	 * @test
	 */
	public function hasEMailFooterInitiallyReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasEMailFooter()
		);
	}

	/**
	 * @test
	 */
	public function hasEMailFooterWithNonEmptyEMailFooterReturnsTrue() {
		$this->fixture->setEMailFooter('Example Inc.');

		self::assertTrue(
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

		self::assertEquals(
			0,
			$this->fixture->getAttendancesPID()
		);
	}

	/**
	 * @test
	 */
	public function getAttendancesPIDWithAttendancesPIDReturnsAttendancesPID() {
		$this->fixture->setData(array('attendances_pid' => 42));

		self::assertEquals(
			42,
			$this->fixture->getAttendancesPID()
		);
	}

	/**
	 * @test
	 */
	public function setAttendancesPIDWithPositiveAttendancesPIDSetsAttendancesPID() {
		$this->fixture->setAttendancesPID(42);

		self::assertEquals(
			42,
			$this->fixture->getAttendancesPID()
		);
	}

	/**
	 * @test
	 */
	public function setAttendancesPIDWithZeroAttendancesPIDSetsAttendancesPID() {
		$this->fixture->setAttendancesPID(0);

		self::assertEquals(
			0,
			$this->fixture->getAttendancesPID()
		);
	}

	/**
	 * @test
	 */
	public function setAttendancesPIDWithNegativeAttendancesPIDThrowsException() {
		$this->setExpectedException('InvalidArgumentException');

		$this->fixture->setAttendancesPID(-1);
	}

	/**
	 * @test
	 */
	public function hasAttendancesPIDInitiallyReturnsFalse() {
		$this->fixture->setData(array());

		self::assertFalse(
			$this->fixture->hasAttendancesPID()
		);
	}

	/**
	 * @test
	 */
	public function hasAttendancesPIDWithAttendancesPIDReturnsTrue() {
		$this->fixture->setAttendancesPID(42);

		self::assertTrue(
			$this->fixture->hasAttendancesPID()
		);
	}


	/////////////////////////////////////
	// Tests concerning the description
	/////////////////////////////////////

	/**
	 * @test
	 */
	public function hasDescriptionForOrganizerWithoutDescriptionReturnsFalse() {
		$this->fixture->setData(array('description' => ''));

		self::assertFalse(
			$this->fixture->hasDescription()
		);
	}

	/**
	 * @test
	 */
	public function hasDescriptionForOrganizerWithDescriptionReturnsTrue() {
		$this->fixture->setData(array('description' => 'foo'));

		self::assertTrue(
			$this->fixture->hasDescription()
		);
	}

	/**
	 * @test
	 */
	public function getDescriptionForOrganizerWithoutDescriptionReturnsEmptyString() {
		$this->fixture->setData(array('description' => ''));

		self::assertEquals(
			'',
			$this->fixture->getDescription()
		);
	}

	/**
	 * @test
	 */
	public function getDescriptionForOrganizerWithDescriptionReturnsDescription() {
		$this->fixture->setData(array('description' => 'foo'));

		self::assertEquals(
			'foo',
			$this->fixture->getDescription()
		);
	}
}