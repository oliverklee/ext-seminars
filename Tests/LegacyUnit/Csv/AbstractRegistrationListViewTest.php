<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Csv;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Hooks\Interfaces\RegistrationListCsv;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * @covers \Tx_Seminars_Csv_AbstractRegistrationListView
 */
final class AbstractRegistrationListViewTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Csv_AbstractRegistrationListView&MockObject
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var DummyConfiguration
     */
    private $configuration = null;

    /**
     * PID of the system folder in which we store our test data
     *
     * @var int
     */
    private $pageUid = 0;

    /**
     * UID of a test event record
     *
     * @var int
     */
    private $eventUid = 0;

    /**
     * @var array<int, string>
     */
    public $frontEndUserFieldKeys = [];

    /**
     * @var array<int, string>
     */
    public $registrationFieldKeys = [];

    /**
     * @var array<int, class-string>
     */
    private $mockedClassNames = [];

    /**
     * backed-up extension configuration of the TYPO3 configuration variables
     *
     * @var array<string, mixed>
     */
    private $extConfBackup = [];

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'] = [];

        $this->getLanguageService()->includeLLFile('EXT:seminars/Resources/Private/Language/locallang_db.xlf');
        $this->getLanguageService()->includeLLFile('EXT:lang/Resources/Private/Language/locallang_general.xlf');

        $this->testingFramework = new TestingFramework('tx_seminars');

        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new DummyConfiguration());
        $this->configuration = new DummyConfiguration();
        $this->configuration->setAsString('charsetForCsv', 'utf-8');
        $configurationRegistry->set('plugin.tx_seminars', $this->configuration);

        $this->pageUid = $this->testingFramework->createSystemFolder();
        $this->eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'pid' => $this->pageUid,
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
            ]
        );

        /** @var \Tx_Seminars_Csv_AbstractRegistrationListView&MockObject $subject */
        $subject = $this->getMockForAbstractClass(\Tx_Seminars_Csv_AbstractRegistrationListView::class);
        $subject->method('shouldAlsoContainRegistrationsOnQueue')->willReturn(true);

        $testCase = $this;
        $subject->method('getFrontEndUserFieldKeys')
            ->willReturnCallback(
                static function () use ($testCase): array {
                    return $testCase->frontEndUserFieldKeys;
                }
            );
        $subject->method('getRegistrationFieldKeys')
            ->willReturnCallback(
                static function () use ($testCase): array {
                    return $testCase->registrationFieldKeys;
                }
            );

        $subject->setEventUid($this->eventUid);
        $this->subject = $subject;
    }

    protected function tearDown()
    {
        $this->purgeMockedInstances();
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;

        $this->testingFramework->cleanUp();
    }

    // Utility functions

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Adds an instance to the Typo3 instance FIFO buffer used by `GeneralUtility::makeInstance()`
     * and registers it for purging in `tearDown()`.
     *
     * In case of a failing test or an exception in the test before the instance is taken
     * from the FIFO buffer, the instance would stay in the buffer and make following tests
     * fail. This function adds it to the list of instances to purge in `tearDown()` in addition
     * to `GeneralUtility::addInstance()`.
     *
     * @param class-string $className
     * @param object $instance
     *
     * @return void
     */
    private function addMockedInstance(string $className, $instance)
    {
        GeneralUtility::addInstance($className, $instance);
        $this->mockedClassNames[] = $className;
    }

    /**
     * Purges possibly leftover instances from the Typo3 instance FIFO buffer used by
     * `GeneralUtility::makeInstance()`.
     *
     * @return void
     */
    private function purgeMockedInstances()
    {
        foreach ($this->mockedClassNames as $className) {
            GeneralUtility::makeInstance($className);
        }

        $this->mockedClassNames = [];
    }

    // Tests for the utility functions

    /**
     * @test
     */
    public function mockedInstancesListIsEmptyInitially()
    {
        self::assertEmpty($this->mockedClassNames);
    }

    /**
     * @test
     */
    public function addMockedInstanceAddsClassnameToList()
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        $mockedClassName = \get_class($mockedInstance);

        $this->addMockedInstance($mockedClassName, $mockedInstance);
        // manually purge the Typo3 FIFO here, as purgeMockedInstances() is not tested yet
        GeneralUtility::makeInstance($mockedClassName);

        self::assertCount(1, $this->mockedClassNames);
        self::assertSame($mockedClassName, $this->mockedClassNames[0]);
    }

    /**
     * @test
     */
    public function addMockedInstanceAddsInstanceToTypo3InstanceBuffer()
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        $mockedClassName = \get_class($mockedInstance);

        $this->addMockedInstance($mockedClassName, $mockedInstance);

        self::assertSame($mockedInstance, GeneralUtility::makeInstance($mockedClassName));
    }

    /**
     * @test
     */
    public function purgeMockedInstancesRemovesClassnameFromList()
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        $mockedClassName = \get_class($mockedInstance);
        $this->addMockedInstance($mockedClassName, $mockedInstance);

        $this->purgeMockedInstances();
        // manually purge the Typo3 FIFO here, as purgeMockedInstances() is not tested for that yet
        GeneralUtility::makeInstance($mockedClassName);

        self::assertEmpty($this->mockedClassNames);
    }

    /**
     * @test
     */
    public function purgeMockedInstancesRemovesInstanceFromTypo3InstanceBuffer()
    {
        $mockedInstance = $this->createMock(\stdClass::class);
        $mockedClassName = \get_class($mockedInstance);
        $this->addMockedInstance($mockedClassName, $mockedInstance);

        $this->purgeMockedInstances();

        self::assertNotSame($mockedInstance, GeneralUtility::makeInstance($mockedClassName));
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
    protected function localizeAndRemoveColon(string $locallangKey): string
    {
        return \rtrim($this->getLanguageService()->getLL($locallangKey), ':');
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

        /** @var \Tx_Seminars_Csv_AbstractRegistrationListView&MockObject $subject */
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

        /** @var \Tx_Seminars_Csv_AbstractRegistrationListView&MockObject $subject */
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
            "\r\n" . $firstRegistrationUid . "\r\n" .
            $secondRegistrationUid . "\r\n",
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
                'address' => "foo\nbar",
            ]
        );

        self::assertContains(
            "\"foo\nbar\"",
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
            "sep=;\r\n\r\n",
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
            "\r\n",
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

    /**
     * @test
     */
    public function renderCallsHookAndReturnsModifiedValue()
    {
        $this->configuration->setAsBoolean('addExcelSpecificSeparatorLineToCsv', false);
        $renderResult = "\r\n";
        $modifiedResult = "modified CSV\r\n";

        $hook = $this->createMock(RegistrationListCsv::class);
        $hook->expects(self::once())->method('modifyCsv')
            ->with($renderResult, $this->subject)
            ->willReturn($modifiedResult);

        $hookClass = \get_class($hook);
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][RegistrationListCsv::class][] = $hookClass;
        $this->addMockedInstance($hookClass, $hook);

        self::assertSame($modifiedResult, $this->subject->render());
    }
}
