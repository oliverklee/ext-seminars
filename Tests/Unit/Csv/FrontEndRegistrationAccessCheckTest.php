<?php

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Csv_FrontEndRegistrationAccessCheckTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_Csv_FrontEndRegistrationAccessCheck
     */
    protected $subject = null;

    /**
     * @var Tx_Oelib_Configuration
     */
    protected $seminarsPluginConfiguration = null;

    /**
     * @var int
     */
    protected $vipsGroupUid = 12431;

    protected function setUp()
    {
        $configurationRegistry = Tx_Oelib_ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new Tx_Oelib_Configuration());

        $this->seminarsPluginConfiguration = new Tx_Oelib_Configuration();
        $this->seminarsPluginConfiguration->setAsInteger('defaultEventVipsFeGroupID', $this->vipsGroupUid);
        $configurationRegistry->set('plugin.tx_seminars_pi1', $this->seminarsPluginConfiguration);

        $this->subject = new Tx_Seminars_Csv_FrontEndRegistrationAccessCheck();
    }

    protected function tearDown()
    {
        Tx_Oelib_ConfigurationRegistry::purgeInstance();
        Tx_Oelib_FrontEndLoginManager::purgeInstance();
    }

    /**
     * @test
     */
    public function subjectImplementsAccessCheck()
    {
        self::assertInstanceOf(
            'Tx_Seminars_Interface_CsvAccessCheck',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function hasAccessForNoFrontEndUserReturnsFalse()
    {
        Tx_Oelib_FrontEndLoginManager::getInstance()->logInUser(null);

        /** @var Tx_Seminars_OldModel_Event|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, [], [], '', false);
        $this->subject->setEvent($event);

        self::assertFalse(
            $this->subject->hasAccess()
        );
    }

    /**
     * @test
     */
    public function hasAccessForNonVipFrontEndUserAndNoVipAccessReturnsFalse()
    {
        $this->seminarsPluginConfiguration->setAsBoolean('allowCsvExportOfRegistrationsInMyVipEventsView', false);

        /** @var Tx_Seminars_Model_FrontEndUser|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMock(Tx_Seminars_Model_FrontEndUser::class);
        $userUid = 42;
        $user->expects(self::any())->method('getUid')->will(self::returnValue($userUid));
        Tx_Oelib_FrontEndLoginManager::getInstance()->logInUser($user);

        /** @var Tx_Seminars_OldModel_Event|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, [], [], '', false);
        $event->expects(self::any())->method('isUserVip')->with($userUid, $this->vipsGroupUid)->will(self::returnValue(false));
        $this->subject->setEvent($event);

        self::assertFalse(
            $this->subject->hasAccess()
        );
    }

    /**
     * @test
     */
    public function hasAccessForVipFrontEndUserAndNoVipAccessReturnsFalse()
    {
        $this->seminarsPluginConfiguration->setAsBoolean('allowCsvExportOfRegistrationsInMyVipEventsView', false);

        /** @var Tx_Seminars_Model_FrontEndUser|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMock(Tx_Seminars_Model_FrontEndUser::class);
        $userUid = 42;
        $user->expects(self::any())->method('getUid')->will(self::returnValue($userUid));
        Tx_Oelib_FrontEndLoginManager::getInstance()->logInUser($user);

        /** @var Tx_Seminars_OldModel_Event|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, [], [], '', false);
        $event->expects(self::any())->method('isUserVip')->with($userUid, $this->vipsGroupUid)->will(self::returnValue(true));
        $this->subject->setEvent($event);

        self::assertFalse(
            $this->subject->hasAccess()
        );
    }

    /**
     * @test
     */
    public function hasAccessForNonVipFrontEndUserAndVipAccessReturnsFalse()
    {
        $this->seminarsPluginConfiguration->setAsBoolean('allowCsvExportOfRegistrationsInMyVipEventsView', true);

        /** @var Tx_Seminars_Model_FrontEndUser|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMock(Tx_Seminars_Model_FrontEndUser::class);
        $userUid = 42;
        $user->expects(self::any())->method('getUid')->will(self::returnValue($userUid));
        Tx_Oelib_FrontEndLoginManager::getInstance()->logInUser($user);

        /** @var Tx_Seminars_OldModel_Event|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, [], [], '', false);
        $event->expects(self::any())->method('isUserVip')->with($userUid, $this->vipsGroupUid)->will(self::returnValue(false));
        $this->subject->setEvent($event);

        self::assertFalse(
            $this->subject->hasAccess()
        );
    }

    /**
     * @test
     */
    public function hasAccessForVipFrontEndUserAndVipAccessReturnsTrue()
    {
        $this->seminarsPluginConfiguration->setAsBoolean('allowCsvExportOfRegistrationsInMyVipEventsView', true);

        /** @var Tx_Seminars_Model_FrontEndUser|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMock(Tx_Seminars_Model_FrontEndUser::class);
        $userUid = 42;
        $user->expects(self::any())->method('getUid')->will(self::returnValue($userUid));
        Tx_Oelib_FrontEndLoginManager::getInstance()->logInUser($user);

        /** @var Tx_Seminars_OldModel_Event|\PHPUnit_Framework_MockObject_MockObject $event */
        $event = $this->getMock(Tx_Seminars_OldModel_Event::class, [], [], '', false);
        $event->expects(self::any())->method('isUserVip')->with($userUid, $this->vipsGroupUid)->will(self::returnValue(true));
        $this->subject->setEvent($event);

        self::assertTrue(
            $this->subject->hasAccess()
        );
    }
}
