<?php
/***************************************************************
 * Copyright notice
 *
 * (c) 2014 Oliver Klee (typo3-coding@oliverklee.de)
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
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Csv_FrontEndRegistrationAccessCheckTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Seminars_Csv_FrontEndRegistrationAccessCheck
	 */
	protected $subject = NULL;

	/**
	 * @var Tx_Oelib_Configuration
	 */
	protected $seminarsPluginConfiguration = NULL;

	/**
	 * @var int
	 */
	protected $vipsGroupUid = 12431;

	protected function setUp() {
		$configurationRegistry = Tx_Oelib_ConfigurationRegistry::getInstance();
		$configurationRegistry->set('plugin', new Tx_Oelib_Configuration());

		$this->seminarsPluginConfiguration = new Tx_Oelib_Configuration();
		$this->seminarsPluginConfiguration->setAsInteger('defaultEventVipsFeGroupID', $this->vipsGroupUid);
		$configurationRegistry->set('plugin.tx_seminars_pi1', $this->seminarsPluginConfiguration);

		$this->subject = new Tx_Seminars_Csv_FrontEndRegistrationAccessCheck();
	}

	protected function tearDown() {
		Tx_Oelib_ConfigurationRegistry::purgeInstance();
		Tx_Oelib_FrontEndLoginManager::purgeInstance();
	}

	/**
	 * @test
	 */
	public function subjectImplementsAccessCheck() {
		self::assertInstanceOf(
			'Tx_Seminars_Interface_CsvAccessCheck',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function hasAccessForNoFrontEndUserReturnsFalse() {
		Tx_Oelib_FrontEndLoginManager::getInstance()->logInUser(NULL);

		$event = $this->getMock('tx_seminars_seminar', array(), array(), '', FALSE);
		/** @var $event tx_seminars_seminar */
		$this->subject->setEvent($event);

		self::assertFalse(
			$this->subject->hasAccess()
		);
	}

	/**
	 * @test
	 */
	public function hasAccessForNonVipFrontEndUserAndNoVipAccessReturnsFalse() {
		$this->seminarsPluginConfiguration->setAsBoolean('allowCsvExportOfRegistrationsInMyVipEventsView', FALSE);

		$user = $this->getMock('tx_seminars_Model_FrontEndUser');
		$userUid = 42;
		$user->expects(self::any())->method('getUid')->will(self::returnValue($userUid));
		/** @var $user tx_seminars_Model_FrontEndUser */
		Tx_Oelib_FrontEndLoginManager::getInstance()->logInUser($user);

		$event = $this->getMock('tx_seminars_seminar', array(), array(), '', FALSE);
		$event->expects(self::any())->method('isUserVip')->with($userUid, $this->vipsGroupUid)->will(self::returnValue(FALSE));
		/** @var $event tx_seminars_seminar */
		$this->subject->setEvent($event);

		self::assertFalse(
			$this->subject->hasAccess()
		);
	}

	/**
	 * @test
	 */
	public function hasAccessForVipFrontEndUserAndNoVipAccessReturnsFalse() {
		$this->seminarsPluginConfiguration->setAsBoolean('allowCsvExportOfRegistrationsInMyVipEventsView', FALSE);

		$user = $this->getMock('tx_seminars_Model_FrontEndUser');
		$userUid = 42;
		$user->expects(self::any())->method('getUid')->will(self::returnValue($userUid));
		/** @var $user tx_seminars_Model_FrontEndUser */
		Tx_Oelib_FrontEndLoginManager::getInstance()->logInUser($user);

		$event = $this->getMock('tx_seminars_seminar', array(), array(), '', FALSE);
		$event->expects(self::any())->method('isUserVip')->with($userUid, $this->vipsGroupUid)->will(self::returnValue(TRUE));
		/** @var $event tx_seminars_seminar */
		$this->subject->setEvent($event);

		self::assertFalse(
			$this->subject->hasAccess()
		);
	}

	/**
	 * @test
	 */
	public function hasAccessForNonVipFrontEndUserAndVipAccessReturnsFalse() {
		$this->seminarsPluginConfiguration->setAsBoolean('allowCsvExportOfRegistrationsInMyVipEventsView', TRUE);

		$user = $this->getMock('tx_seminars_Model_FrontEndUser');
		$userUid = 42;
		$user->expects(self::any())->method('getUid')->will(self::returnValue($userUid));
		/** @var $user tx_seminars_Model_FrontEndUser */
		Tx_Oelib_FrontEndLoginManager::getInstance()->logInUser($user);

		$event = $this->getMock('tx_seminars_seminar', array(), array(), '', FALSE);
		$event->expects(self::any())->method('isUserVip')->with($userUid, $this->vipsGroupUid)->will(self::returnValue(FALSE));
		/** @var $event tx_seminars_seminar */
		$this->subject->setEvent($event);

		self::assertFalse(
			$this->subject->hasAccess()
		);
	}

	/**
	 * @test
	 */
	public function hasAccessForVipFrontEndUserAndVipAccessReturnsTrue() {
		$this->seminarsPluginConfiguration->setAsBoolean('allowCsvExportOfRegistrationsInMyVipEventsView', TRUE);

		$user = $this->getMock('tx_seminars_Model_FrontEndUser');
		$userUid = 42;
		$user->expects(self::any())->method('getUid')->will(self::returnValue($userUid));
		/** @var $user tx_seminars_Model_FrontEndUser */
		Tx_Oelib_FrontEndLoginManager::getInstance()->logInUser($user);

		$event = $this->getMock('tx_seminars_seminar', array(), array(), '', FALSE);
		$event->expects(self::any())->method('isUserVip')->with($userUid, $this->vipsGroupUid)->will(self::returnValue(TRUE));
		/** @var $event tx_seminars_seminar */
		$this->subject->setEvent($event);

		self::assertTrue(
			$this->subject->hasAccess()
		);
	}
}