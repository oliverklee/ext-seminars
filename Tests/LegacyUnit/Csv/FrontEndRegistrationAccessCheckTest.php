<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Csv;

use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Configuration\Configuration;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Csv\FrontEndRegistrationAccessCheck;
use OliverKlee\Seminars\Csv\Interfaces\CsvAccessCheck;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FrontEndRegistrationAccessCheckTest extends TestCase
{
    /**
     * @var FrontEndRegistrationAccessCheck
     */
    private $subject = null;

    /**
     * @var Configuration
     */
    private $seminarsPluginConfiguration = null;

    /**
     * @var int
     */
    private $vipsGroupUid = 12431;

    protected function setUp()
    {
        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new Configuration());

        $this->seminarsPluginConfiguration = new Configuration();
        $this->seminarsPluginConfiguration->setAsInteger('defaultEventVipsFeGroupID', $this->vipsGroupUid);
        $configurationRegistry->set('plugin.tx_seminars_pi1', $this->seminarsPluginConfiguration);

        $this->subject = new FrontEndRegistrationAccessCheck();
    }

    protected function tearDown()
    {
        ConfigurationRegistry::purgeInstance();
        FrontEndLoginManager::purgeInstance();
    }

    /**
     * @test
     */
    public function subjectImplementsAccessCheck()
    {
        self::assertInstanceOf(
            CsvAccessCheck::class,
            $this->subject
        );
    }

    /**
     * @test
     */
    public function hasAccessForNoFrontEndUserReturnsFalse()
    {
        FrontEndLoginManager::getInstance()->logInUser();

        /** @var \Tx_Seminars_OldModel_Event&MockObject $event */
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

        /** @var \Tx_Seminars_Model_FrontEndUser&MockObject $user */
        $user = $this->createMock(\Tx_Seminars_Model_FrontEndUser::class);
        $userUid = 42;
        $user->method('getUid')->willReturn($userUid);
        FrontEndLoginManager::getInstance()->logInUser($user);

        /** @var \Tx_Seminars_OldModel_Event&MockObject $event */
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

        /** @var \Tx_Seminars_Model_FrontEndUser&MockObject $user */
        $user = $this->createMock(\Tx_Seminars_Model_FrontEndUser::class);
        $userUid = 42;
        $user->method('getUid')->willReturn($userUid);
        FrontEndLoginManager::getInstance()->logInUser($user);

        /** @var \Tx_Seminars_OldModel_Event&MockObject $event */
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

        /** @var \Tx_Seminars_Model_FrontEndUser&MockObject $user */
        $user = $this->createMock(\Tx_Seminars_Model_FrontEndUser::class);
        $userUid = 42;
        $user->method('getUid')->willReturn($userUid);
        FrontEndLoginManager::getInstance()->logInUser($user);

        /** @var \Tx_Seminars_OldModel_Event&MockObject $event */
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

        /** @var \Tx_Seminars_Model_FrontEndUser&MockObject $user */
        $user = $this->createMock(\Tx_Seminars_Model_FrontEndUser::class);
        $userUid = 42;
        $user->method('getUid')->willReturn($userUid);
        FrontEndLoginManager::getInstance()->logInUser($user);

        /** @var \Tx_Seminars_OldModel_Event&MockObject $event */
        $event = $this->createMock(\Tx_Seminars_OldModel_Event::class);
        $event->method('isUserVip')->with($userUid, $this->vipsGroupUid)
            ->willReturn(true);
        $this->subject->setEvent($event);

        self::assertTrue(
            $this->subject->hasAccess()
        );
    }
}
