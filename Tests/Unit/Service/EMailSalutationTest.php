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
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Service_EMailSalutationTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Oelib_TestingFramework the testing framework
     */
    private $testingFramework = null;

    /**
     * @var Tx_Seminars_EmailSalutation
     */
    private $subject = null;

    /**
     * @var array backed-up extension configuration of the TYPO3 configuration
     *            variables
     */
    private $extConfBackup = array();

    /**
     * @var array backed-up T3_VAR configuration
     */
    private $t3VarBackup = array();

    protected function setUp()
    {
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_seminars');
        $this->subject = new Tx_Seminars_EmailSalutation();
        $configuration = new Tx_Oelib_Configuration();
        $configuration->setAsString('salutation', 'formal');
        Tx_Oelib_ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $configuration);
        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
        $this->t3VarBackup = $GLOBALS['T3_VAR']['getUserObj'];
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
        $GLOBALS['T3_VAR']['getUserObj'] = $this->t3VarBackup;
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
     * @return Tx_Seminars_Model_FrontEndUser the loaded testing model of a
     *                                        FE user
     */
    private function createFrontEndUser(
        $gender = Tx_Oelib_Model_FrontEndUser::GENDER_MALE
    ) {
        return Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_FrontEndUser::class)
            ->getLoadedTestingModel(
                array('name' => 'Foo', 'gender' => $gender)
        );
    }

    /**
     * Checks whether the FrontEndUser.gender fields exists and  marks the test as skipped if that extension is not installed.
     *
     * @return void
     */
    protected function skipWithoutGenderField()
    {
        if (!Tx_Oelib_Model_FrontEndUser::hasGenderField()) {
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
        self::assertInstanceOf(Tx_Seminars_Model_FrontEndUser::class, $this->createFrontEndUser());
    }

    /**
     * @test
     */
    public function createFrontEndUserForGivenGenderAssignsGenderToFrontEndUser()
    {
        $this->skipWithoutGenderField();

        self::assertSame(
            Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE,
            $this->createFrontEndUser(Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE)->getGender()
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

        $user = $this->createFrontEndUser(Tx_Oelib_Model_FrontEndUser::GENDER_MALE);

        self::assertContains(
            Tx_Oelib_TranslatorRegistry::getInstance()->get('seminars')->translate('email_hello_formal_0'),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForMaleUserReturnsUsersNameWithGenderSpecificTitle()
    {
        $this->skipWithoutGenderField();

        $user = $this->createFrontEndUser(Tx_Oelib_Model_FrontEndUser::GENDER_MALE);

        self::assertContains(
            Tx_Oelib_TranslatorRegistry::getInstance()->get('seminars')->translate('email_salutation_title_0') .
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

        $user = $this->createFrontEndUser(Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE);

        self::assertContains(
            Tx_Oelib_TranslatorRegistry::getInstance()->get('seminars')
                ->translate('email_hello_formal_1'),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForFemaleUserReturnsUsersNameWithGenderSpecificTitle()
    {
        $this->skipWithoutGenderField();

        $user = $this->createFrontEndUser(Tx_Oelib_Model_FrontEndUser::GENDER_FEMALE);

        self::assertContains(
            Tx_Oelib_TranslatorRegistry::getInstance()->get('seminars')->translate('email_salutation_title_1') .
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
            Tx_Oelib_Model_FrontEndUser::GENDER_UNKNOWN
        );

        self::assertContains(
            Tx_Oelib_TranslatorRegistry::getInstance()->get('seminars')
                ->translate('email_hello_formal_99'),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForUnknownUserReturnsUsersNameWithGenderSpecificTitle()
    {
        $user = $this->createFrontEndUser(
            Tx_Oelib_Model_FrontEndUser::GENDER_UNKNOWN
        );

        self::assertContains(
            Tx_Oelib_TranslatorRegistry::getInstance()->get('seminars')
                ->translate('email_salutation_title_99') . ' ' .
                $user->getLastOrFullName(),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForInformalSalutationReturnsInformalSalutation()
    {
        $user = $this->createFrontEndUser();
        Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')
            ->setAsString('salutation', 'informal');

        self::assertContains(
            Tx_Oelib_TranslatorRegistry::getInstance()->get('seminars')
                    ->translate('email_hello_informal'),
            $this->subject->getSalutation($user)
        );
    }

    /**
     * @test
     */
    public function getSalutationForInformalSalutationReturnsUsersName()
    {
        $user = $this->createFrontEndUser();
        Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')
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
    public function genderDataProvider()
    {
        return array(
            'male' => array(0),
            'female' => array(1),
            'unknown (old)' => array(2),
            'unknown' => array(99),
        );
    }

    /**
     * @test
     * @param int $gender
     * @dataProvider genderDataProvider
     */
    public function getSalutationForFormalSalutationModeContainsNoRawLabelKeys($gender)
    {
        Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', 'formal');

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
     * @param int $gender
     * @dataProvider genderDataProvider
     */
    public function getSalutationForInformalSalutationModeContainsNoRawLabelKeys($gender)
    {
        Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', 'informal');

        $user = $this->createFrontEndUser($gender);
        $salutation = $this->subject->getSalutation($user);

        $this->assertNotContainsRawLabelKey($salutation);
    }

    /**
     * @test
     * @param int $gender
     * @dataProvider genderDataProvider
     */
    public function getSalutationForNoSalutationModeContainsNoRawLabelKeys($gender)
    {
        Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', '');

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
    private function assertNotContainsRawLabelKey($string)
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
        $hookClassName = uniqid('tx_salutationHook');
        $salutationHookMock = $this->getMock(
            $hookClassName, array('modifySalutation')
        );
        $salutationHookMock->expects(self::atLeastOnce())
            ->method('modifySalutation');

        $GLOBALS['T3_VAR']['getUserObj'][$hookClassName] = $salutationHookMock;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']
            ['modifyEmailSalutation'][$hookClassName] = $hookClassName;

        $this->subject->getSalutation($this->createFrontEndUser());
    }

    /**
     * @test
     */
    public function getSalutationCanCallMultipleSetHooks()
    {
        $hookClassName1 = uniqid('tx_salutationHook1');
        $salutationHookMock1 = $this->getMock(
            $hookClassName1, array('modifySalutation')
        );
        $salutationHookMock1->expects(self::atLeastOnce())
            ->method('modifySalutation');
        $GLOBALS['T3_VAR']['getUserObj'][$hookClassName1] = $salutationHookMock1;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']
            ['modifyEmailSalutation'][$hookClassName1] = $hookClassName1;

        $hookClassName2 = uniqid('tx_salutationHook2');
        $salutationHookMock2 = $this->getMock(
            $hookClassName2, array('modifySalutation')
        );
        $salutationHookMock2->expects(self::atLeastOnce())
            ->method('modifySalutation');
        $GLOBALS['T3_VAR']['getUserObj'][$hookClassName2] = $salutationHookMock2;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']
            ['modifyEmailSalutation'][$hookClassName2] = $hookClassName2;

        $this->subject->getSalutation($this->createFrontEndUser());
    }

    /*
     * Tests concerning createIntroduction
     */

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function createIntroductionWithEmptyBeginThrowsException()
    {
        $eventUid = $this->testingFramework->createRecord('tx_seminars_seminars');

        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($eventUid, array());

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
            array('begin_date' => $GLOBALS['SIM_EXEC_TIME'])
        );

        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($eventUid, array(
            'dateFormatYMD' => $dateFormatYMD
        ));

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
            array(
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
                'end_date' => $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_DAY,
            )
        );

        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($eventUid, array(
            'dateFormatYMD' => $dateFormatYMD,
            'dateFormatD' => $dateFormatD,
            'abbreviateDateRanges' => 1,
        ));

        self::assertContains(
            strftime($dateFormatD, $GLOBALS['SIM_EXEC_TIME']) .
                '-' .
                strftime($dateFormatYMD, $GLOBALS['SIM_EXEC_TIME'] + Tx_Oelib_Time::SECONDS_PER_DAY),
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
            array(
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
            )
        );

        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($eventUid, array(
            'timeFormat' => $timeFormat,
        ));

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
            array(
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
                'end_date' => $endDate,
            )
        );

        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($eventUid, array('timeFormat' => $timeFormat));
        $translator = Tx_Oelib_TranslatorRegistry::getInstance()->get('seminars');
        $timeInsert = strftime($timeFormat, $GLOBALS['SIM_EXEC_TIME']) . ' ' .
            $translator->translate('email_timeTo') . ' ' .
            strftime($timeFormat, $endDate);

        self::assertContains(
            sprintf($translator->translate('email_timeFrom'), $timeInsert),
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
            array(
                'begin_date' => $GLOBALS['SIM_EXEC_TIME'],
                'end_date' => $endDate,
            )
        );

        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent($eventUid, array('dateFormatYMD' => $dateFormat));
        $formattedDate = strftime($dateFormat, $GLOBALS['SIM_EXEC_TIME']);

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
        Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', $salutation);

        $dateFormatYMD = '%d.%m.%Y';
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('begin_date' => $GLOBALS['SIM_EXEC_TIME'])
        );

        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent(
            $eventUid,
            array('dateFormatYMD' => $dateFormatYMD, 'salutation' => $salutation)
        );

        $introduction = $this->subject->createIntroduction('%s', $event);

        $this->assertNotContainsRawLabelKey($introduction);
    }

    /**
     * @test
     */
    public function createIntroductionForInformalSalutationModeContainsNoRawLabelKeys()
    {
        $salutation = 'informal';
        Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', $salutation);

        $dateFormatYMD = '%d.%m.%Y';
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('begin_date' => $GLOBALS['SIM_EXEC_TIME'])
        );

        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent(
            $eventUid,
            array('dateFormatYMD' => $dateFormatYMD, 'salutation' => $salutation)
        );

        $introduction = $this->subject->createIntroduction('%s', $event);

        $this->assertNotContainsRawLabelKey($introduction);
    }

    /**
     * @test
     */
    public function createIntroductionForNoSalutationModeContainsNoRawLabelKeys()
    {
        $salutation = '';
        Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')->setAsString('salutation', $salutation);

        $dateFormatYMD = '%d.%m.%Y';
        $eventUid = $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            array('begin_date' => $GLOBALS['SIM_EXEC_TIME'])
        );

        $event = new Tx_Seminars_Tests_Unit_Fixtures_OldModel_TestingEvent(
            $eventUid,
            array('dateFormatYMD' => $dateFormatYMD, 'salutation' => $salutation)
        );

        $introduction = $this->subject->createIntroduction('%s', $event);

        $this->assertNotContainsRawLabelKey($introduction);
    }
}
