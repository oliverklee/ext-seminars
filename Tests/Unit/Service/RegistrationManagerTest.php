<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use OliverKlee\Seminars\Service\RegistrationManager;

/**
 * @covers \OliverKlee\Seminars\Service\RegistrationManager
 */
final class RegistrationManagerTest extends UnitTestCase
{
    /**
     * @var RegistrationManager
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new RegistrationManager();
    }

    protected function tearDown(): void
    {
        RegistrationManager::purgeInstance();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getInstanceReturnsRegistationManagerInstance(): void
    {
        self::assertInstanceOf(RegistrationManager::class, RegistrationManager::getInstance());
    }

    /**
     * @test
     */
    public function getInstanceCalledTwoTimesReturnsTheSameInstance(): void
    {
        $firstInstance = RegistrationManager::getInstance();
        $secondInstance = RegistrationManager::getInstance();

        self::assertSame($firstInstance, $secondInstance);
    }

    /**
     * @test
     */
    public function purgeInstanceCausesGetInstanceToReturnNewInstance(): void
    {
        $firstInstance = RegistrationManager::getInstance();
        RegistrationManager::purgeInstance();
        $secondInstance = RegistrationManager::getInstance();

        self::assertNotSame($firstInstance, $secondInstance);
    }

    /**
     * @test
     */
    public function getRegistrationInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getRegistration());
    }

    /**
     * @test
     */
    public function setRegistrationSetsRegistration(): void
    {
        $model = new LegacyRegistration();
        $this->subject->setRegistration($model);

        self::assertSame($model, $this->subject->getRegistration());
    }
}
