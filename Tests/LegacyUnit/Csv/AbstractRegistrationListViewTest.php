<?php

use OliverKlee\PhpUnit\TestCase;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_Csv_AbstractRegistrationListViewTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Csv_AbstractRegistrationListView
     */
    protected $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    protected $testingFramework = null;

    /**
     * @var \Tx_Oelib_Configuration
     */
    protected $configuration = null;

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
    public $frontEndUserFieldKeys = [];

    /**
     * @var array[]
     */
    public $registrationFieldKeys = [];

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $GLOBALS['LANG']->includeLLFile('EXT:seminars/Resources/Private/Language/locallang_db.xlf');
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8007000) {
            $GLOBALS['LANG']->includeLLFile('EXT:lang/Resources/Private/Language/locallang_general.xlf');
        } else {
            $GLOBALS['LANG']->includeLLFile('EXT:lang/locallang_general.xlf');
        }

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $configurationRegistry = \Tx_Oelib_ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new \Tx_Oelib_Configuration());
        $this->configuration = new \Tx_Oelib_Configuration();
        $this->configuration->setData(['charsetForCsv' => 'utf-8']);
        $configurationRegistry->set('plugin.tx_seminars', $this->configuration);

        $this->pageUid = $this->testingFramework->createSystemFolder();
        $this->eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->pageUid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
            ]
        );

        $this->subject = $this->getMockForAbstractClass(\Tx_Seminars_Csv_AbstractRegistrationListView::class);
        $this->subject->method(
            'shouldAlsoContainRegistrationsOnQueue'
        )->willReturn(true);

        $testCase = $this;
        $this->subject->method('getFrontEndUserFieldKeys')
            ->willReturnCallback(
                static function () use ($testCase) {
                    return $testCase->frontEndUserFieldKeys;
                }
            );
        $this->subject->method('getRegistrationFieldKeys')
            ->willReturnCallback(
                static function () use ($testCase) {
                    return $testCase->registrationFieldKeys;
                }
            );

        $this->subject->setEventUid($this->eventUid);
    }

    protected function tearDown()
    {
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
    protected function localizeAndRemoveColon($locallangKey)
    {
        return rtrim($GLOBALS['LANG']->getLL($locallangKey), ':');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setPageUidWithPositivePageUidNotThrowsException()
    {
        $this->subject->setPageUid($this->testingFramework->createSystemFolder());
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setPageUidWithZeroPageUidNotThrowsException()
    {
        $this->subject->setPageUid(0);
    }

    /**
     * @test
     */
    public function setPageUidWithNegativePageUidThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setPageUid(-1);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setEventUidWithZeroEventUidNotThrowsException()
    {
        $this->subject->setEventUid(0);
    }

    /**
     * @test
     */
    public function setEventUidWithNegativeEventUidThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setEventUid(-1);
    }

    /**
     * @test
     */
    public function renderForNoPageAndNoEventThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        /** @var \Tx_Seminars_Csv_AbstractRegistrationListView|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockForAbstractClass(\Tx_Seminars_Csv_AbstractRegistrationListView::class);

        self::assertSame(
            '',
            $subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForPageAndEventThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        /** @var \Tx_Seminars_Csv_AbstractRegistrationListView|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMockForAbstractClass(\Tx_Seminars_Csv_AbstractRegistrationListView::class);
        $subject->setEventUid($this->eventUid);
        $subject->setPageUid($this->pageUid);

        $subject->render();
    }

    /**
     * @test
     */
    public function renderCanContainOneRegistrationUid()
    {
        $this->registrationFieldKeys = ['uid'];

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        self::assertContains(
            (string)$registrationUid,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderCanContainTwoRegistrationUids()
    {
        $this->registrationFieldKeys = ['uid'];

        $firstRegistrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );
        $secondRegistrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'] + 1,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        $registrationsList = $this->subject->render();
        self::assertContains(
            (string)$firstRegistrationUid,
            $registrationsList
        );
        self::assertContains(
            (string)$secondRegistrationUid,
            $registrationsList
        );
    }

    /**
     * @test
     */
    public function renderCanContainNameOfUser()
    {
        $this->frontEndUserFieldKeys = ['name'];

        $frontEndUserUid = $this->testingFramework->createFrontEndUser('', ['name' => 'foo_user']);
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $frontEndUserUid,
            ]
        );

        self::assertContains(
            'foo_user',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderNotContainsUidOfRegistrationWithDeletedUser()
    {
        $this->registrationFieldKeys = ['uid'];

        $frontEndUserUid = $this->testingFramework->createFrontEndUser('', ['deleted' => 1]);
        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $frontEndUserUid,
            ]
        );

        self::assertNotContains(
            (string)$registrationUid,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderNotContainsUidOfRegistrationWithInexistentUser()
    {
        $this->registrationFieldKeys = ['uid'];

        $registrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->getAutoIncrement('fe_users'),
            ]
        );

        self::assertNotContains(
            (string)$registrationUid,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderSeparatesLinesWithCarriageReturnAndLineFeed()
    {
        $this->registrationFieldKeys = ['uid'];

        $firstRegistrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => 1,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );
        $secondRegistrationUid = $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => 2,
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
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
    public function renderHasResultThatEndsWithCarriageReturnAndLineFeed()
    {
        $this->registrationFieldKeys = ['uid'];

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );
        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
            ]
        );

        self::assertRegExp(
            '/\\r\\n$/',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderEscapesDoubleQuotes()
    {
        $this->registrationFieldKeys = ['uid', 'address'];

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo " bar',
            ]
        );

        self::assertContains(
            'foo "" bar',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderNotEscapesRegularValues()
    {
        $this->registrationFieldKeys = ['address'];

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo " bar',
            ]
        );

        self::assertNotContains(
            '"foo bar"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWrapsValuesWithSemicolonsInDoubleQuotes()
    {
        $this->registrationFieldKeys = ['address'];

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo ; bar',
            ]
        );

        self::assertContains(
            '"foo ; bar"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWrapsValuesWithLineFeedsInDoubleQuotes()
    {
        $this->registrationFieldKeys = ['address'];

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo' . LF . 'bar',
            ]
        );

        self::assertContains(
            '"foo' . LF . 'bar"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWrapsValuesWithDoubleQuotesInDoubleQuotes()
    {
        $this->registrationFieldKeys = ['address'];

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo " bar',
            ]
        );

        self::assertContains(
            '"foo "" bar"',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderSeparatesTwoValuesWithSemicolons()
    {
        $this->registrationFieldKeys = ['address', 'title'];

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'crdate' => $GLOBALS['SIM_EXEC_TIME'],
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo',
                'title' => 'test',
            ]
        );

        self::assertContains(
            'foo;test',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderDoesNotWrapHeadlineFieldsInDoubleQuotes()
    {
        $this->registrationFieldKeys = ['address'];

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
    public function renderSeparatesHeadlineFieldsWithSemicolons()
    {
        $this->registrationFieldKeys = ['address', 'title'];

        self::assertContains(
            $this->localizeAndRemoveColon('tx_seminars_attendances.address') .
            ';' . $this->localizeAndRemoveColon('tx_seminars_attendances.title'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForConfigurationAttendanceCsvFieldsEmptyDoesNotAddSemicolonOnEndOfHeadline()
    {
        $this->frontEndUserFieldKeys = ['name'];

        self::assertNotContains(
            'name;',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForConfigurationFeUserCsvFieldsEmptyDoesNotAddSemicolonAtBeginningOfHeadline()
    {
        $this->registrationFieldKeys = ['address'];

        self::assertNotContains(
            ';address',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForBothConfigurationFieldsNotEmptyAddsSemicolonBetweenConfigurationFields()
    {
        $this->registrationFieldKeys = ['address'];
        $this->frontEndUserFieldKeys = ['name'];

        self::assertContains(
            $this->localizeAndRemoveColon(
                'LGL.name'
            ) . ';' . $this->localizeAndRemoveColon('tx_seminars_attendances.address'),
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForBothConfigurationFieldsEmptyAndSeparatorEnabledReturnsSeparatorMarkerAndEmptyLine()
    {
        $this->configuration->setAsBoolean('addExcelSpecificSeparatorLineToCsv', true);

        self::assertSame(
            'sep=;' . CRLF . CRLF,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForBothConfigurationFieldsEmptyAndSeparatorDisabledReturnsEmptyLine()
    {
        $this->configuration->setAsBoolean('addExcelSpecificSeparatorLineToCsv', false);

        self::assertSame(
            CRLF,
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderReturnsRegistrationsOnSetPage()
    {
        $this->subject->setEventUid(0);
        $this->subject->setPageUid($this->pageUid);

        $this->registrationFieldKeys = ['address'];

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo',
                'pid' => $this->pageUid,
            ]
        );

        self::assertContains(
            'foo',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderNotReturnsRegistrationsOnOtherPage()
    {
        $this->subject->setEventUid(0);
        $this->subject->setPageUid($this->pageUid);

        $this->registrationFieldKeys = ['address'];

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo',
                'pid' => $this->pageUid + 1,
            ]
        );

        self::assertNotContains(
            'foo',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderReturnsRegistrationsOnSubpageOfGivenPage()
    {
        $this->subject->setEventUid(0);
        $this->subject->setPageUid($this->pageUid);

        $subpagePid = $this->testingFramework->createSystemFolder($this->pageUid);
        $this->registrationFieldKeys = ['address'];

        $this->testingFramework->createRecord(
            'tx_seminars_attendances',
            [
                'seminar' => $this->eventUid,
                'user' => $this->testingFramework->createFrontEndUser(),
                'address' => 'foo',
                'pid' => $subpagePid,
            ]
        );

        self::assertContains(
            'foo',
            $this->subject->render()
        );
    }
}
