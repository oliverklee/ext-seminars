<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Service;

use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminar\Email\Salutation;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\OldModel\TestingEvent;
use OliverKlee\Seminars\Tests\LegacyUnit\Service\Fixtures\EmailSalutationHookInterface;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use Tx_Oelib_Model_FrontEndUser;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
final class SalutationTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var \Tx_Oelib_TestingFramework the testing framework
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

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;
        Bootstrap::getInstance()->initializeBackendAuthentication();

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');
        $this->subject = new Salutation();
        $configuration = new \Tx_Oelib_Configuration();
        $configuration->setAsString('salutation', 'formal');
        \Tx_Oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $configuration);
        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
    }

    /*
     * Utility functions
     */

    /**
     * Creates an FE-user with the given gender and the name "Foo".
     *
     * @param int $gender
     *        the gender for the FE user, must be one of
     *        "Tx_Oelib_Model_FrontEndUser::GENDER_MALE",
     *        "Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE" or
     *        "Tx_Oelib_Model_FrontEndUser::GENDER_UNKNOWN", may be empty
     *
     * @return \Tx_Seminars_Model_FrontEndUser the loaded testing model of a
     *                                        FE user
     */
    private function createFrontEndUser(
        int $gender = \Tx_Oelib_Model_FrontEndUser::GENDER_MALE
    ): \Tx_Seminars_Model_FrontEndUser {
        return \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_FrontEndUser::class)
            ->getLoadedTestingModel(['name' => 'Foo', 'gender' => $gender]);
    }

    /**
     * Checks whether the FrontEndUser.gender fields exists and
     * marks the test as skipped if that extension is not installed.
     *
     * @return void
     */
    protected function skipWithoutGenderField()
    {
        if (!\Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
            self::markTestSkipped(
                'This test is skipped because it requires FE user to have a gender field, e.g., ' .
                'from the sr_feuser_register extension.'
            );
        }
    }

    /*
     * Tests concerning the utility functions
     */

    /**
     * @test
     */
    public function createFrontEndUserReturnsFeUserModel()
    {
        self::assertInstanceOf(\Tx_Seminars_Model_FrontEndUser::class, $this->createFrontEndUser());
    }

    /**
     * @test
     */
    public function createFrontEndUserForGivenGenderAssignsGenderToFrontEndUser()
    {
        $this->skipWithoutGenderField();

        self::assertSame(
            \Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE,
            $this->createFrontEndUser(\Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE)->getGender()
        );
    }

    /*
     * Tests concerning getSalutation
     */

    /**
     * @test
     */
    public function getSalutationReturnsUsernameOfRegistration()
    {
        self::assertContains(
            'Foo',
            $this->subject->getSalutation($this->createFrontEndUser())
        );
    }

    /**
     * @test
     */
    public function getSalutationForMaleUserReturnsMaleSalutation()
    {
        $this->skipWithoutGenderField();

        $user = $this->createFrontEndUser();

        self::assertContains(
            $this->getLanguageService()->getLL('email_hello_formal_0'),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForMaleUserReturnsUsersNameWithGenderSpecificTitle()
    {
        $this->skipWithoutGenderField();

        $user = $this->createFrontEndUser();

        self::assertContains(
            $this->getLanguageService()->getLL('email_salutation_title_0') .
            ' ' . $user->getLastOrFullName(),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForFemaleUserReturnsFemaleSalutation()
    {
        $this->skipWithoutGenderField();

        $user = $this->createFrontEndUser(\Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE);

        self::assertContains(
            $this->getLanguageService()->getLL('email_hello_formal_1'),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForFemaleUserReturnsUsersNameWithGenderSpecificTitle()
    {
        $this->skipWithoutGenderField();

        $user = $this->createFrontEndUser(\Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE);

        self::assertContains(
            $this->getLanguageService()->getLL('email_salutation_title_1') .
            ' ' . $user->getLastOrFullName(),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForUnknownUserReturnsUnknownSalutation()
    {
        $user = $this->createFrontEndUser(
            \Tx_Oelib_Model_FrontEndUser::GENDER_UNKNOWN
        );

        self::assertContains(
            $this->getLanguageService()->getLL('email_hello_formal_99'),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForUnknownUserReturnsUsersNameWithGenderSpecificTitle()
    {
        $user = $this->createFrontEndUser(
            \Tx_Oelib_Model_FrontEndUser::GENDER_UNKNOWN
        );

        self::assertContains(
            $this->getLanguageService()->getLL('email_salutation_title_99') . ' ' . $user->getLastOrFullName(),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForInformalSalutationReturnsInformalSalutation()
    {
        $user = $this->createFrontEndUser();
        \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')
            ->setAsString('salutation', 'informal');

        self::assertContains(
            $this->getLanguageService()->getLL('email_hello_informal'),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForInformalSalutationReturnsUsersName()
    {
        $user = $this->createFrontEndUser();
        \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')
            ->setAsString('salutation', 'informal');

        self::assertContains(
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
    public function getSalutationForFormalSalutationModeContainsNoRawLabelKeys(int $gender)
    {
        \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', 'formal');

        $user = $this->createFrontEndUser($gender);
        $salutation = $this->subject->getSalutation($user);

        self::assertNotContains(
            '_',
            $salutation
        );
        self::assertNotContains(
            'salutation',
            $salutation
        );
        self::assertNotContains(
            'email',
            $salutation
        );
        self::assertNotContains(
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
    public function getSalutationForInformalSalutationModeContainsNoRawLabelKeys(int $gender)
    {
        \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', 'informal');

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
    public function getSalutationForNoSalutationModeContainsNoRawLabelKeys(int $gender)
    {
        \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', '');

        $user = $this->createFrontEndUser($gender);
        $salutation = $this->subject->getSalutation($user);

        $this->assertNotContainsRawLabelKey($salutation);
    }

    /**
     * Checks that $string does not contain a raw label key.
     *
     * @param string $string
     *
     * @return void
     */
    private function assertNotContainsRawLabelKey(string $string)
    {
        self::assertNotContains('_', $string);
        self::assertNotContains('salutation', $string);
        self::assertNotContains('formal', $string);
    }

    /*
     * Tests concerning the hooks
     */

    /**
     * @test
     */
    public function getSalutationForHookSetInConfigurationCallsThisHook()
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
    public function getSalutationCanCallMultipleSetHooks()
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

    /*
     * Tests concerning createIntroduction
     */

    /**
     * @test
     */
    public function createIntroductionWithEmptyBeginThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');

        $event = new TestingEvent($eventUid);

        $this->subject->createIntroduction('', $event);
    }

    /**
     * @test
     */
    public function createIntroductionForEventWithDateReturnsEventsDate()
    {
        $dateFormatYMD = '%d.%m.%Y';
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $GLOBALS['SIM_EXEC_TIME']]
        );

        $event = new TestingEvent($eventUid);
        $event->overrideConfiguration(['dateFormatYMD' => $dateFormatYMD]);

        self::assertContains(
            strftime($dateFormatYMD, $GLOBALS['SIM_EXEC_TIME']),
            $this->subject->createIntroduction('%s', $event)
        );
    }

    /**
     * @test
     */
    public function createIntroductionForEventWithBeginAndEndDateOnDifferentDaysReturnsEventsDateFromTo()
    {
        $dateFormatYMD = '%d.%m.%Y';
        $dateFormatD = '%d';
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY,
            ]
        );

        $event = new TestingEvent($eventUid);
        $event->overrideConfiguration(
            [
                'dateFormatYMD' => $dateFormatYMD,
                'dateFormatD' => $dateFormatD,
                'abbreviateDateRanges' => 1,
            ]
        );

        self::assertContains(
            strftime($dateFormatD, $GLOBALS['SIM_EXEC_TIME']) .
            '-' .
            strftime($dateFormatYMD, $GLOBALS['SIM_EXEC_TIME'] + \Tx_Oelib_Time::SECONDS_PER_DAY),
            $this->subject->createIntroduction('%s', $event)
        );
    }

    /**
     * @test
     */
    public function createIntroductionForEventWithTimeReturnsEventsTime()
    {
        $timeFormat = '%H:%M';
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
            ]
        );

        $event = new TestingEvent($eventUid);
        $event->overrideConfiguration(['timeFormat' => $timeFormat]);

        self::assertContains(
            strftime($timeFormat, $GLOBALS['SIM_EXEC_TIME']),
            $this->subject->createIntroduction('%s', $event)
        );
    }

    /**
     * @test
     */
    public function createIntroductionForEventWithStartAndEndOnOneDayReturnsTimeFromTo()
    {
        $timeFormat = '%H:%M';
        $endDate = $GLOBALS['SIM_EXEC_TIME'] + 3600;
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
                'end_date' => $endDate,
            ]
        );

        $event = new TestingEvent($eventUid);
        $event->overrideConfiguration(['timeFormat' => $timeFormat]);
        $timeInsert = \strftime($timeFormat, $GLOBALS['SIM_EXEC_TIME']) . ' ' .
            $this->getLanguageService()->getLL('email_timeTo') . ' ' .
            \strftime($timeFormat, $endDate);

        self::assertContains(
            \sprintf($this->getLanguageService()->getLL('email_timeFrom'), $timeInsert),
            $this->subject->createIntroduction('%s', $event)
        );
    }

    /**
     * @test
     */
    public function createIntroductionForEventWithStartAndEndOnOneDayContainsDate()
    {
        $dateFormat = '%d.%m.%Y';
        $endDate = $GLOBALS['SIM_EXEC_TIME'] + 3600;
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            [
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
                'end_date' => $endDate,
            ]
        );

        $event = new TestingEvent($eventUid);
        $event->overrideConfiguration(['dateFormatYMD' => $dateFormat]);
        $formattedDate = \strftime($dateFormat, $GLOBALS['SIM_EXEC_TIME']);

        self::assertContains(
            $formattedDate,
            $this->subject->createIntroduction('%s', $event)
        );
    }

    /**
     * @test
     */
    public function createIntroductionForFormalSalutationModeContainsNoRawLabelKeys()
    {
        $salutation = 'formal';
        \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', $salutation);

        $dateFormatYMD = '%d.%m.%Y';
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $GLOBALS['SIM_EXEC_TIME']]
        );

        $event = new TestingEvent($eventUid);
        $event->overrideConfiguration(['dateFormatYMD' => $dateFormatYMD, 'salutation' => $salutation]);

        $introduction = $this->subject->createIntroduction('%s', $event);

        $this->assertNotContainsRawLabelKey($introduction);
    }

    /**
     * @test
     */
    public function createIntroductionForInformalSalutationModeContainsNoRawLabelKeys()
    {
        $salutation = 'informal';
        \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', $salutation);

        $dateFormatYMD = '%d.%m.%Y';
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $GLOBALS['SIM_EXEC_TIME']]
        );

        $event = new TestingEvent($eventUid);
        $event->overrideConfiguration(['dateFormatYMD' => $dateFormatYMD, 'salutation' => $salutation]);

        $introduction = $this->subject->createIntroduction('%s', $event);

        $this->assertNotContainsRawLabelKey($introduction);
    }

    /**
     * @test
     */
    public function createIntroductionForNoSalutationModeContainsNoRawLabelKeys()
    {
        $salutation = '';
        \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', $salutation);

        $dateFormatYMD = '%d.%m.%Y';
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['begin_date' => $GLOBALS['SIM_EXEC_TIME']]
        );

        $event = new TestingEvent($eventUid);
        $event->overrideConfiguration(['dateFormatYMD' => $dateFormatYMD, 'salutation' => $salutation]);

        $introduction = $this->subject->createIntroduction('%s', $event);

        $this->assertNotContainsRawLabelKey($introduction);
    }
}
