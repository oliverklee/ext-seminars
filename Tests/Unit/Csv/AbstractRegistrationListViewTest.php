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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

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

	protected function setUp() {
		$GLOBALS['LANG']->includeLLFile(ExtensionManagementUtility::extPath('seminars') . 'locallang_db.xml');
		$GLOBALS['LANG']->includeLLFile(ExtensionManagementUtility::extPath('lang') . 'locallang_general.xml');

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
		$this->subject->expects(self::any())->method('shouldAlsoContainRegistrationsOnQueue')->will(self::returnValue(TRUE));

		$testCase = $this;
		$this->subject->expects(self::any())->method('getFrontEndUserFieldKeys')
			->will(self::returnCallback(
				function() use ($testCase) {
					return $testCase->frontEndUserFieldKeys;
				}
			));
		$this->subject->expects(self::any())->method('getRegistrationFieldKeys')
			->will(self::returnCallback(
				function() use ($testCase) {
					return $testCase->registrationFieldKeys;
				}
			));

		$this->subject->setEventUid($this->eventUid);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();
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

		self::assertSame(
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

		self::assertContains(
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
		self::assertContains(
			(string) $firstRegistrationUid,
			$registrationsList
		);
		self::assertContains(
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

		self::assertContains(
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

		self::assertNotContains(
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

		self::assertNotContains(
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

		self::assertContains(
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

		self::assertRegExp(
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

		self::assertContains(
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

		self::assertNotContains(
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

		self::assertContains(
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

		self::assertContains(
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

		self::assertContains(
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

		self::assertContains(
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

		self::assertContains(
			$localizedAddress,
			$registrationsList
		);
		self::assertNotContains(
			'"' . $localizedAddress . '"',
			$registrationsList
		);
	}

	/**
	 * @test
	 */
	public function renderSeparatesHeadlineFieldsWithSemicolons() {
		$this->registrationFieldKeys = array('address', 'title');

		self::assertContains(
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

		self::assertNotContains(
			'name;',
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForConfigurationFeUserCsvFieldsEmptyDoesNotAddSemicolonAtBeginningOfHeadline() {
		$this->registrationFieldKeys = array('address');

		self::assertNotContains(
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

		self::assertContains(
			$this->localizeAndRemoveColon('LGL.name') . ';' . $this->localizeAndRemoveColon('tx_seminars_attendances.address'),
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForBothConfigurationFieldsEmptyAndSeparatorEnabledReturnsSeparatorMarkerAndEmptyLine() {
		$this->configuration->setAsBoolean('addExcelSpecificSeparatorLineToCsv', TRUE);

		self::assertSame(
			'sep=;' . CRLF . CRLF,
			$this->subject->render()
		);
	}

	/**
	 * @test
	 */
	public function renderForBothConfigurationFieldsEmptyAndSeparatorDisabledReturnsEmptyLine() {
		$this->configuration->setAsBoolean('addExcelSpecificSeparatorLineToCsv', FALSE);

		self::assertSame(
			CRLF,
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

		self::assertContains(
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

		self::assertNotContains(
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

		self::assertContains(
			'foo',
			$this->subject->render()
		);
	}
}