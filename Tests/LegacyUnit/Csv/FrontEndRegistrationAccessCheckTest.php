<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Csv;

use OliverKlee\PhpUnit\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FrontEndRegistrationAccessCheckTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Csv_FrontEndRegistrationAccessCheck
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_Configuration
     */
    private $seminarsPluginConfiguration = null;

    /**
     * @var int
     */
    private $vipsGroupUid = 12431;

    protected function setUp()
    {
        $configurationRegistry = \Tx_Oelib_ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new \Tx_Oelib_Configuration());

        $this->seminarsPluginConfiguration = new \Tx_Oelib_Configuration();
        $this->seminarsPluginConfiguration->setAsInteger('defaultEventVipsFeGroupID', $this->vipsGroupUid);
        $configurationRegistry->set('plugin.tx_seminars_pi1', $this->seminarsPluginConfiguration);

        $this->subject = new \Tx_Seminars_Csv_FrontEndRegistrationAccessCheck();
    }

    protected function tearDown()
    {
        \Tx_Oelib_ConfigurationRegistry::purgeInstance();
        \Tx_Oelib_FrontEndLoginManager::purgeInstance();
    }

    /**
     * @test
     */
    public function subjectImplementsAccessCheck()
    {
        self::assertInstanceOf(
            \Tx_Seminars_Interface_CsvAccessCheck::class,
            $this->subject
        );
    }

    /**
     * @test
     */
    public function hasAccessForNoFrontEndUserReturnsFalse()
    {
        \Tx_Oelib_FrontEndLoginManager::getInstance()->logInUser();

        /** @var \Tx_Seminars_OldModel_Event|MockObject $event */
        $event = $this->createMock(\Tx_Seminars_OldModel_Event::class);
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

        /** @var \Tx_Seminars_Model_FrontEndUser|MockObject $user */
        $user = $this->createMock(\Tx_Seminars_Model_FrontEndUser::class);
        $userUid = 42;
        $user->method('getUid')->willReturn($userUid);
        \Tx_Oelib_FrontEndLoginManager::getInstance()->logInUser($user);

        /** @var \Tx_Seminars_OldModel_Event|MockObject $event */
        $event = $this->createMock(\Tx_Seminars_OldModel_Event::class);
        $event->method('isUserVip')->with(
            $userUid,
            $this->vipsGroupUid
        )->willReturn(false);
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

        /** @var \Tx_Seminars_Model_FrontEndUser|MockObject $user */
        $user = $this->createMock(\Tx_Seminars_Model_FrontEndUser::class);
        $userUid = 42;
        $user->method('getUid')->willReturn($userUid);
        \Tx_Oelib_FrontEndLoginManager::getInstance()->logInUser($user);

        /** @var \Tx_Seminars_OldModel_Event|MockObject $event */
        $event = $this->createMock(\Tx_Seminars_OldModel_Event::class);
        $event->method('isUserVip')->with(
            $userUid,
            $this->vipsGroupUid
        )->willReturn(true);
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

        /** @var \Tx_Seminars_Model_FrontEndUser|MockObject $user */
        $user = $this->createMock(\Tx_Seminars_Model_FrontEndUser::class);
        $userUid = 42;
        $user->method('getUid')->willReturn($userUid);
        \Tx_Oelib_FrontEndLoginManager::getInstance()->logInUser($user);

        /** @var \Tx_Seminars_OldModel_Event|MockObject $event */
        $event = $this->createMock(\Tx_Seminars_OldModel_Event::class);
        $event->method('isUserVip')->with(
            $userUid,
            $this->vipsGroupUid
        )->willReturn(false);
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

        /** @var \Tx_Seminars_Model_FrontEndUser|MockObject $user */
        $user = $this->createMock(\Tx_Seminars_Model_FrontEndUser::class);
        $userUid = 42;
        $user->method('getUid')->willReturn($userUid);
        \Tx_Oelib_FrontEndLoginManager::getInstance()->logInUser($user);

        /** @var \Tx_Seminars_OldModel_Event|MockObject $event */
        $event = $this->createMock(\Tx_Seminars_OldModel_Event::class);
        $event->method('isUserVip')->with($userUid, $this->vipsGroupUid)
            ->willReturn(true);
        $this->subject->setEvent($event);

        self::assertTrue(
            $this->subject->hasAccess()
        );
    }
}
