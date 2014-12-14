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
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Csv_AbstractRegistrationListViewTest extends Tx_Phpunit_TestCase {
	/**
	 * @var Tx_Seminars_Csv_AbstractRegistrationListView
	 */
	protected $subject = NULL;

	/**
	 * @var Tx_Oelib_TestingFramework
	 */
	protected $testingFramework = NULL;

	/**
	 * @var Tx_Oelib_Configuration
	 */
	protected $configuration = NULL;

	/**
	 * PID of the system folder in which we store our test data
	 *
	 * @var int
	 */
	protected $pageUid = 0;

	/**
	 * UID of a test event record
	 *
	 * @var int
	 */
	protected $eventUid = 0;

	/**
	 * @var string[]
	 */
	public $frontEndUserFieldKeys = array();

	/**
	 * @var array[]
	 */
	public $registrationFieldKeys = array();

	public function setUp() {
		$GLOBALS['LANG']->includeLLFile(t3lib_extMgm::extPath('seminars') . 'locallang_db.xml');
		$GLOBALS['LANG']->includeLLFile(t3lib_extMgm::extPath('lang') . 'locallang_general.xml');

		$this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');

		$configurationRegistry = Tx_Oelib_ConfigurationRegistry::getInstance();
		$configurationRegistry->set('plugin', new Tx_Oelib_Configuration());
		$this->configuration = new Tx_Oelib_Configuration();
		$this->configuration->setData(array('charsetForCsv' => 'utf-8'));
		$configurationRegistry->set('plugin.tx_seminars', $this->configuration);

		$this->pageUid = $this->testingFramework->createSystemFolder();
		$this->eventUid = $this->testingFramework->createRecord(
			'tx_seminars_seminars',
			array(
				'pid' => $this->pageUid,
				'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
			)
		);

		$this->subject = $this->getMockForAbstractClass('Tx_Seminars_Csv_AbstractRegistrationListView');
		$this->subject->expects($this->any())->method('shouldAlsoContainRegistrationsOnQueue')->will($this->returnValue(TRUE));

		$testCase = $this;
		$this->subject->expects($this->any())->method('getFrontEndUserFieldKeys')
			->will($this->returnCallback(
				function() use ($testCase) {
					return $testCase->frontEndUserFieldKeys;
				}
			));
		$this->subject->expects($this->any())->method('getRegistrationFieldKeys')
			->will($this->returnCallback(
				function() use ($testCase) {
					return $testCase->registrationFieldKeys;
				}
			));

		$this->subject->setEventUid($this->eventUid);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		unset($this->subject, $this->testingFramework, $this->configuration);
	}

	/**
	 * Retrieves the localization for the given locallang key and then strips the trailing colon from the localization.
	 *
	 * @param string $locallangKey
	 *        the locallang key with the localization to remove the trailing colon from, must not be empty and the localization
	 *        must have a trailing colon
	 *
	 * @return string locallang string with the removed trailing colon, will not be empty
	 */
	protected function localizeAndRemoveColon($locallangKey) {
		return rtrim($GLOBALS['LANG']->getLL($locallangKey), ':');
	}

	/**
	 * @test
	 */
	public function setPageUidWithPositivePageUidNotThrowsException() {
		$this->subject->setPageUid($this->testingFramework->createSystemFolder());
	}

	/**
	 * @test
	 */
	public function setPageUidWithZeroPageUidNotThrowsException() {
		$this->subject->setPageUid(0);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setPageUidWithNegativePageUidThrowsException() {
		$this->subject->setPageUid(-1);
	}

	/**
	 * @test
	 */
	public function setEventUidWithZeroEventUidNotThrowsException() {
		$this->subject->setEventUid(0);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function setEventUidWithNegativeEventUidThrowsException() {
		$this->subject->setEventUid(-1);
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function renderForNoPageAndNoEventThrowsException() {
		$subject = $this->getMockForAbstractClass('Tx_Seminars_Csv_AbstractRegistrationListView');

		$this->assertSame(
			'',
			$subject->render()
		);
	}

	/**
	 * @test
	 *
	 * @expectedException BadMethodCallException
	 */
	public function renderForPageAndEventThrowsException() {
		$subject = $this->getMockForAbstractClass('Tx_Seminars_Csv_AbstractRegistrationListView');
		$subject->setEventUid($this->eventUid);
		$subject->setPageUid($this->pageUid);

		$subject->render();
	}

	/**
	 * @test
	 */
	public function renderCanContainOneRegistrationUid() {
		$this->registrationFieldKeys = array('uid');

		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->assertContains(
			(string) $registrationUid,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderCanContainTwoRegistrationUids() {
		$this->registrationFieldKeys = array('uid');

		$firstRegistrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$secondRegistrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => ($GLOBALS['SIM_EXEC_TIME'] + 1),
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$registrationsList = $this->subject->render();
		$this->assertContains(
			(string) $firstRegistrationUid,
			$registrationsList
		);
		$this->assertContains(
			(string) $secondRegistrationUid,
			$registrationsList
		);
	}

	/**
	 * @test
	 */
	public function renderCanContainNameOfUser() {
		$this->frontEndUserFieldKeys = array('name');

		$frontEndUserUid = $this->testingFramework->createFrontEndUser('', array('name' => 'foo_user'));
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $frontEndUserUid,
			)
		);

		$this->assertContains(
			'foo_user',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderNotContainsUidOfRegistrationWithDeletedUser() {
		$this->registrationFieldKeys = array('uid');

		$frontEndUserUid = $this->testingFramework->createFrontEndUser('', array('deleted' => 1));
		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $frontEndUserUid,
			)
		);

		$this->assertNotContains(
			(string) $registrationUid,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderNotContainsUidOfRegistrationWithInexistentUser() {
		$this->registrationFieldKeys = array('uid');

		$registrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->getAutoIncrement('fe_users'),
			)
		);

		$this->assertNotContains(
			(string) $registrationUid,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderSeparatesLinesWithCarriageReturnAndLineFeed() {
		$this->registrationFieldKeys = array('uid');

		$firstRegistrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => 1,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$secondRegistrationUid = $this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => 2,
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->assertContains(
			CRLF . $firstRegistrationUid . CRLF .
			$secondRegistrationUid . CRLF,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderHasResultThatEndsWithCarriageReturnAndLineFeed() {
		$this->registrationFieldKeys = array('uid');

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);
		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
			)
		);

		$this->assertRegExp(
			'/\r\n$/',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderEscapesDoubleQuotes() {
		$this->registrationFieldKeys = array('uid', 'address');

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo " bar',
			)
		);

		$this->assertContains(
			'foo "" bar',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderNotEscapesRegularValues() {
		$this->registrationFieldKeys = array('address');

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo " bar',
			)
		);

		$this->assertNotContains(
			'"foo bar"',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWrapsValuesWithSemicolonsInDoubleQuotes() {
		$this->registrationFieldKeys = array('address');

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo ; bar',
			)
		);

		$this->assertContains(
			'"foo ; bar"',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWrapsValuesWithLineFeedsInDoubleQuotes() {
		$this->registrationFieldKeys = array('address');

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo' . LF . 'bar',
			)
		);

		$this->assertContains(
			'"foo' . LF . 'bar"',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderWrapsValuesWithDoubleQuotesInDoubleQuotes() {
		$this->registrationFieldKeys = array('address');

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo " bar',
			)
		);

		$this->assertContains(
			'"foo "" bar"',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderSeparatesTwoValuesWithSemicolons() {
		$this->registrationFieldKeys = array('address', 'title');

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'crdate' => $GLOBALS['SIM_EXEC_TIME'],
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo',
				'title' => 'test',
			)
		);

		$this->assertContains(
			'foo;test',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderDoesNotWrapHeadlineFieldsInDoubleQuotes() {
		$this->registrationFieldKeys = array('address');

		$registrationsList = $this->subject->render();
		$localizedAddress = $this->localizeAndRemoveColon('tx_seminars_attendances.address');

		$this->assertContains(
			$localizedAddress,
			$registrationsList
		);
		$this->assertNotContains(
			'"' . $localizedAddress . '"',
			$registrationsList
		);
	}

	/**
	 * @test
	 */
	public function renderSeparatesHeadlineFieldsWithSemicolons() {
		$this->registrationFieldKeys = array('address', 'title');

		$this->assertContains(
			$this->localizeAndRemoveColon('tx_seminars_attendances.address') .
				';' . $this->localizeAndRemoveColon('tx_seminars_attendances.title'),
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForConfigurationAttendanceCsvFieldsEmptyDoesNotAddSemicolonOnEndOfHeadline() {
		$this->frontEndUserFieldKeys = array('name');

		$this->assertNotContains(
			'name;',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForConfigurationFeUserCsvFieldsEmptyDoesNotAddSemicolonAtBeginningOfHeadline() {
		$this->registrationFieldKeys = array('address');

		$this->assertNotContains(
			';address',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForBothConfigurationFieldsNotEmptyAddsSemicolonBetweenConfigurationFields() {
		$this->registrationFieldKeys = array('address');
		$this->frontEndUserFieldKeys = array('name');

		$this->assertContains(
			$this->localizeAndRemoveColon('LGL.name') . ';' . $this->localizeAndRemoveColon('tx_seminars_attendances.address'),
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForBothConfigurationFieldsEmptyReturnsSeparatorMarkerAndEmptyLine() {
		$this->assertSame(
			'sep=;' . CRLF . CRLF,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderReturnsRegistrationsOnSetPage() {
		$this->subject->setEventUid(0);
		$this->subject->setPageUid($this->pageUid);

		$this->registrationFieldKeys = array('address');

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo',
				'pid' => $this->pageUid,
			)
		);

		$this->assertContains(
			'foo',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderNotReturnsRegistrationsOnOtherPage() {
		$this->subject->setEventUid(0);
		$this->subject->setPageUid($this->pageUid);

		$this->registrationFieldKeys = array('address');

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo',
				'pid' => $this->pageUid + 1,
			)
		);

		$this->assertNotContains(
			'foo',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderReturnsRegistrationsOnSubpageOfGivenPage() {
		$this->subject->setEventUid(0);
		$this->subject->setPageUid($this->pageUid);

		$subpagePid = $this->testingFramework->createSystemFolder($this->pageUid);
		$this->registrationFieldKeys = array('address');

		$this->testingFramework->createRecord(
			'tx_seminars_attendances',
			array(
				'seminar' => $this->eventUid,
				'user' => $this->testingFramework->createFrontEndUser(),
				'address' => 'foo',
				'pid' => $subpagePid,
			)
		);

		$this->assertContains(
			'foo',
			$this->subject->render()
		);
	}
}