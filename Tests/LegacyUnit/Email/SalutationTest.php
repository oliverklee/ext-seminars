<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Interfaces\Time;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUser as OelibFrontEndUser;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminar\Email\Salutation;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\OldModel\TestingLegacyEvent;
use OliverKlee\Seminars\Tests\LegacyUnit\Service\Fixtures\EmailSalutationHookInterface;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Seminar\Email\Salutation
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
    private const DATE_FORMAT_DAY = '%d';

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
        'dateFormatD' => self::DATE_FORMAT_DAY,
        'timeFormat' => self::TIME_FORMAT,
    ];

    /**
     * @var TestingFramework the testing framework
     */
    private $testingFramework = null;

    /**
     * @var Salutation
     */
    private $subject = null;

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
     * Creates an FE-user with the given gender and the name "Foo".
     *
     * @param int $gender
     *        the gender for the FE user, must be one of
     *        FrontEndUser::GENDER_MALE,
     *        FrontEndUser::GENDER_FEMALE or
     *        FrontEndUser::GENDER_UNKNOWN, may be empty
     *
     * @return FrontEndUser the loaded testing model of a FE user
     */
    private function createFrontEndUser(int $gender = OelibFrontEndUser::GENDER_MALE): FrontEndUser
    {
        return MapperRegistry::get(FrontEndUserMapper::class)
            ->getLoadedTestingModel(['name' => 'Foo', 'gender' => $gender]);
    }

    /**
     * Checks whether the FrontEndUser.gender fields exists and
     * marks the test as skipped if that extension is not installed.
     */
    protected function skipWithoutGenderField(): void
    {
        if (!OelibFrontEndUser::hasGenderField()) {
            self::markTestSkipped(
                'This test is skipped because it requires FE user to have a gender field, e.g., ' .
                'from the sr_feuser_register extension.'
            );
        }
    }

    // Tests concerning the utility functions

    /**
     * @test
     */
    public function createFrontEndUserReturnsFeUserModel(): void
    {
        self::assertInstanceOf(FrontEndUser::class, $this->createFrontEndUser());
    }

    /**
     * @test
     */
    public function createFrontEndUserForGivenGenderAssignsGenderToFrontEndUser(): void
    {
        $this->skipWithoutGenderField();

        self::assertSame(
            OelibFrontEndUser::GENDER_FEMALE,
            $this->createFrontEndUser(OelibFrontEndUser::GENDER_FEMALE)->getGender()
        );
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
    public function getSalutationForMaleUserReturnsMaleSalutation(): void
    {
        $this->skipWithoutGenderField();

        $user = $this->createFrontEndUser();

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('email_hello_formal_0'),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForMaleUserReturnsUsersNameWithGenderSpecificTitle(): void
    {
        $this->skipWithoutGenderField();

        $user = $this->createFrontEndUser();

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('email_salutation_title_0') .
            ' ' . $user->getLastOrFullName(),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForFemaleUserReturnsFemaleSalutation(): void
    {
        $this->skipWithoutGenderField();

        $user = $this->createFrontEndUser(OelibFrontEndUser::GENDER_FEMALE);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('email_hello_formal_1'),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForFemaleUserReturnsUsersNameWithGenderSpecificTitle(): void
    {
        $this->skipWithoutGenderField();

        $user = $this->createFrontEndUser(OelibFrontEndUser::GENDER_FEMALE);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('email_salutation_title_1') .
            ' ' . $user->getLastOrFullName(),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForUnknownUserReturnsUnknownSalutation(): void
    {
        $user = $this->createFrontEndUser(OelibFrontEndUser::GENDER_UNKNOWN);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('email_hello_formal_99'),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForUnknownUserReturnsUsersNameWithGenderSpecificTitle(): void
    {
        $user = $this->createFrontEndUser(OelibFrontEndUser::GENDER_UNKNOWN);

        self::assertStringContainsString(
            $this->getLanguageService()->getLL('email_salutation_title_99') . ' ' . $user->getLastOrFullName(),
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
            $this->getLanguageService()->getLL('email_hello_informal'),
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
     * Returns all valid genders.
     *
     * @return int[][]
     */
    public function genderDataProvider(): array
    {
        return [
            'male' => [0],
            'female' => [1],
            'unknown (old)' => [2],
            'unknown' => [99],
        ];
    }

    /**
     * @test
     *
     * @param int $gender
     *
     * @dataProvider genderDataProvider
     */
    public function getSalutationForFormalSalutationModeContainsNoRawLabelKeys(int $gender): void
    {
        $this->configuration->setAsString('salutation', 'formal');

        $user = $this->createFrontEndUser($gender);
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
     *
     * @param int $gender
     *
     * @dataProvider genderDataProvider
     */
    public function getSalutationForInformalSalutationModeContainsNoRawLabelKeys(int $gender): void
    {
        $this->configuration->setAsString('salutation', 'informal');

        $user = $this->createFrontEndUser($gender);
        $salutation = $this->subject->getSalutation($user);

        $this->assertNotContainsRawLabelKey($salutation);
    }

    /**
     * @test
     *
     * @param int $gender
     *
     * @dataProvider genderDataProvider
     */
    public function getSalutationForNoSalutationModeContainsNoRawLabelKeys(int $gender): void
    {
        $this->configuration->setAsString('salutation', '');

        $user = $this->createFrontEndUser($gender);
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
        $salutationHookMock = $this->createPartialMock(\stdClass::class, ['modifySalutation']);
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

        self::assertContains(
            strftime(self::DATE_FORMAT, $GLOBALS['SIM_EXEC_TIME']),
            $this->subject->createIntroduction('%s', $event)
        );
    }

    /**
     * @test
     */
    public function createIntroductionForEventWithBeginAndEndDateOnDifferentDaysReturnsEventsDateFromTo(): void
    {
        $this->configuration->setAsBoolean('abbreviateDateRanges', true);
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Time::SECONDS_PER_DAY,
            ]
        );
        $event = new TestingLegacyEvent($eventUid);

        self::assertStringContainsString(
            \strftime(self::DATE_FORMAT_DAY, $GLOBALS['SIM_EXEC_TIME']) . '-' .
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

        self::assertContains(
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
            $this->getLanguageService()->getLL('email_timeTo') . ' ' .
            \strftime(self::TIME_FORMAT, $endDate);

        self::assertStringContainsString(
            \sprintf($this->getLanguageService()->getLL('email_timeFrom'), $timeInsert),
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

        self::assertContains(
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
