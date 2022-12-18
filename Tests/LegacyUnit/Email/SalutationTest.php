<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Interfaces\Time;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Email\Salutation;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\OldModel\TestingLegacyEvent;
use OliverKlee\Seminars\Tests\LegacyUnit\Service\Fixtures\EmailSalutationHookInterface;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Seminars\Email\Salutation
 */
final class SalutationTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var string
     */
    private const DATE_FORMAT = '%d.%m.%Y';

    /**
     * @var string
     */
    private const TIME_FORMAT = '%H:%M';

    /**
     * @var array<string, string>
     */
    private const CONFIGURATION = [
        'salutation' => 'formal',
        'dateFormatYMD' => self::DATE_FORMAT,
        'timeFormat' => self::TIME_FORMAT,
    ];

    /**
     * @var TestingFramework the testing framework
     */
    private $testingFramework;

    /**
     * @var Salutation
     */
    private $subject;

    /**
     * @var array backed-up extension configuration of the TYPO3 configuration
     *            variables
     */
    private $extConfBackup = [];

    /**
     * @var DummyConfiguration
     */
    private $configuration;

    protected function setUp(): void
    {
        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];

        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;
        Bootstrap::initializeBackendAuthentication();

        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->configuration = new DummyConfiguration(self::CONFIGURATION);
        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $this->configuration);

        $this->subject = new Salutation();
    }

    protected function tearDown(): void
    {
        ConfigurationRegistry::purgeInstance();
        $this->testingFramework->cleanUp();
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
    }

    // Utility functions

    /**
     * Creates an FE-user with the name "Foo".
     */
    private function createFrontEndUser(): FrontEndUser
    {
        return MapperRegistry::get(FrontEndUserMapper::class)->getLoadedTestingModel(['name' => 'Foo']);
    }

    // Tests concerning the utility functions

    /**
     * @test
     */
    public function createFrontEndUserReturnsFeUserModel(): void
    {
        self::assertInstanceOf(FrontEndUser::class, $this->createFrontEndUser());
    }

    // Tests concerning getSalutation

    /**
     * @test
     */
    public function getSalutationReturnsUsernameOfRegistration(): void
    {
        self::assertStringContainsString(
            'Foo',
            $this->subject->getSalutation($this->createFrontEndUser())
        );
    }

    /**
     * @test
     */
    public function getSalutationReturnsGenderUnspecificSalutation(): void
    {
        $user = $this->createFrontEndUser();

        self::assertStringContainsString(
            $this->translate('email_hello_formal_99'),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForUnknownUserReturnsUsersNameWithGenderUnspecificTitle(): void
    {
        $user = $this->createFrontEndUser();

        self::assertStringContainsString(
            $this->translate('email_salutation_title_99') . ' ' . $user->getName(),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForInformalSalutationReturnsInformalSalutation(): void
    {
        $user = $this->createFrontEndUser();
        $this->configuration->setAsString('salutation', 'informal');

        self::assertStringContainsString(
            $this->translate('email_hello_informal'),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForInformalSalutationReturnsUsersName(): void
    {
        $user = $this->createFrontEndUser();
        $this->configuration->setAsString('salutation', 'informal');

        self::assertStringContainsString(
            $user->getLastOrFullName(),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForFormalSalutationModeContainsNoRawLabelKeys(): void
    {
        $this->configuration->setAsString('salutation', 'formal');

        $user = $this->createFrontEndUser();
        $salutation = $this->subject->getSalutation($user);

        self::assertStringNotContainsString(
            '_',
            $salutation
        );
        self::assertStringNotContainsString(
            'salutation',
            $salutation
        );
        self::assertStringNotContainsString(
            'email',
            $salutation
        );
        self::assertStringNotContainsString(
            'formal',
            $salutation
        );
    }

    /**
     * @test
     */
    public function getSalutationForInformalSalutationModeContainsNoRawLabelKeys(): void
    {
        $this->configuration->setAsString('salutation', 'informal');

        $user = $this->createFrontEndUser();
        $salutation = $this->subject->getSalutation($user);

        $this->assertNotContainsRawLabelKey($salutation);
    }

    /**
     * @test
     */
    public function getSalutationForNoSalutationModeContainsNoRawLabelKeys(): void
    {
        $this->configuration->setAsString('salutation', '');

        $user = $this->createFrontEndUser();
        $salutation = $this->subject->getSalutation($user);

        $this->assertNotContainsRawLabelKey($salutation);
    }

    /**
     * Checks that $string does not contain a raw label key.
     *
     * @param string $string
     */
    private function assertNotContainsRawLabelKey(string $string): void
    {
        self::assertStringNotContainsString('_', $string);
        self::assertStringNotContainsString('salutation', $string);
        self::assertStringNotContainsString('formal', $string);
    }

    // Tests concerning the hooks

    /**
     * @test
     */
    public function getSalutationForHookSetInConfigurationCallsThisHook(): void
    {
        $salutationHookMock = $this->createMock(EmailSalutationHookInterface::class);
        $hookClassName = \get_class($salutationHookMock);
        $frontendUser = $this->createFrontEndUser();
        $salutationHookMock->expects(self::atLeastOnce())->method('modifySalutation')->with(
            self::isType('array'),
            self::identicalTo($frontendUser)
        );

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['modifyEmailSalutation'][$hookClassName] = $hookClassName;
        GeneralUtility::addInstance($hookClassName, $salutationHookMock);

        $this->subject->getSalutation($frontendUser);
    }

    /**
     * @test
     */
    public function getSalutationCanCallMultipleSetHooks(): void
    {
        $hookClassName1 = 'AnEmailSalutationHook';
        $salutationHookMock1 = $this->getMockBuilder(EmailSalutationHookInterface::class)
            ->setMockClassName($hookClassName1)->getMock();
        $frontendUser = $this->createFrontEndUser();
        $salutationHookMock1->expects(self::atLeastOnce())->method('modifySalutation')->with(
            self::isType('array'),
            self::identicalTo($frontendUser)
        );
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['modifyEmailSalutation'][$hookClassName1] = $hookClassName1;
        GeneralUtility::addInstance($hookClassName1, $salutationHookMock1);

        $hookClassName2 = 'AnotherEmailSalutationHook';
        $salutationHookMock2 = $this->getMockBuilder(EmailSalutationHookInterface::class)
            ->setMockClassName($hookClassName2)->getMock();
        $salutationHookMock2->expects(self::atLeastOnce())->method('modifySalutation')->with(
            self::isType('array'),
            self::identicalTo($frontendUser)
        );
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['modifyEmailSalutation'][$hookClassName2] = $hookClassName2;
        GeneralUtility::addInstance($hookClassName2, $salutationHookMock2);

        $this->subject->getSalutation($frontendUser);
    }

    // Tests concerning createIntroduction

    /**
     * @test
     */
    public function createIntroductionWithEmptyBeginThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');

        $event = new TestingLegacyEvent($eventUid);

        $this->subject->createIntroduction('', $event);
    }

    /**
     * @test
     */
    public function createIntroductionForEventWithDateReturnsEventsDate(): void
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $GLOBALS['SIM_EXEC_TIME']]
        );

        $event = new TestingLegacyEvent($eventUid);

        self::assertStringContainsString(
            strftime(self::DATE_FORMAT, $GLOBALS['SIM_EXEC_TIME']),
            $this->subject->createIntroduction('%s', $event)
        );
    }

    /**
     * @test
     */
    public function createIntroductionForEventWithBeginAndEndDateOnDifferentDaysReturnsEventsDateFromTo(): void
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
            ]
        );
        $event = new TestingLegacyEvent($eventUid);

        self::assertStringContainsString(
            \strftime(self::DATE_FORMAT, $GLOBALS['SIM_EXEC_TIME']) . '-' .
            strftime(self::DATE_FORMAT, $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY),
            $this->subject->createIntroduction('%s', $event)
        );
    }

    /**
     * @test
     */
    public function createIntroductionForEventWithTimeReturnsEventsTime(): void
    {
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
            ]
        );

        $event = new TestingLegacyEvent($eventUid);

        self::assertStringContainsString(
            \strftime(self::TIME_FORMAT, $GLOBALS['SIM_EXEC_TIME']),
            $this->subject->createIntroduction('%s', $event)
        );
    }

    /**
     * @test
     */
    public function createIntroductionForEventWithStartAndEndOnOneDayReturnsTimeFromTo(): void
    {
        $endDate = $GLOBALS['SIM_EXEC_TIME'] + 3600;
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
                'end_date' => $endDate,
            ]
        );

        $event = new TestingLegacyEvent($eventUid);
        $timeInsert = \strftime(self::TIME_FORMAT, $GLOBALS['SIM_EXEC_TIME']) . ' ' .
            $this->translate('email_timeTo') . ' ' .
            \strftime(self::TIME_FORMAT, $endDate);

        self::assertStringContainsString(
            \sprintf($this->translate('email_timeFrom'), $timeInsert),
            $this->subject->createIntroduction('%s', $event)
        );
    }

    /**
     * @test
     */
    public function createIntroductionForEventWithStartAndEndOnOneDayContainsDate(): void
    {
        $endDate = $GLOBALS['SIM_EXEC_TIME'] + 3600;
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
                'end_date' => $endDate,
            ]
        );

        $event = new TestingLegacyEvent($eventUid);
        $formattedDate = \strftime(self::DATE_FORMAT, $GLOBALS['SIM_EXEC_TIME']);

        self::assertStringContainsString(
            $formattedDate,
            $this->subject->createIntroduction('%s', $event)
        );
    }

    /**
     * @test
     */
    public function createIntroductionForFormalSalutationModeContainsNoRawLabelKeys(): void
    {
        $salutation = 'formal';
        $this->configuration->setAsString('salutation', $salutation);

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $GLOBALS['SIM_EXEC_TIME']]
        );

        $event = new TestingLegacyEvent($eventUid);

        $introduction = $this->subject->createIntroduction('%s', $event);

        $this->assertNotContainsRawLabelKey($introduction);
    }

    /**
     * @test
     */
    public function createIntroductionForInformalSalutationModeContainsNoRawLabelKeys(): void
    {
        $salutation = 'informal';
        $this->configuration->setAsString('salutation', $salutation);

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $GLOBALS['SIM_EXEC_TIME']]
        );

        $event = new TestingLegacyEvent($eventUid);

        $introduction = $this->subject->createIntroduction('%s', $event);

        $this->assertNotContainsRawLabelKey($introduction);
    }

    /**
     * @test
     */
    public function createIntroductionForNoSalutationModeContainsNoRawLabelKeys(): void
    {
        $salutation = '';
        $this->configuration->setAsString('salutation', $salutation);

        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $GLOBALS['SIM_EXEC_TIME']]
        );

        $event = new TestingLegacyEvent($eventUid);

        $introduction = $this->subject->createIntroduction('%s', $event);

        $this->assertNotContainsRawLabelKey($introduction);
    }
}
