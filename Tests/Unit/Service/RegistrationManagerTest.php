<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Service;

use OliverKlee\Seminars\Service\RegistrationManager;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Service\RegistrationManager
 * @covers \OliverKlee\Seminars\Templating\TemplateHelper
 */
final class RegistrationManagerTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    protected function tearDown(): void
    {
        RegistrationManager::purgeInstance();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getInstanceReturnsRegistrationManagerInstance(): void
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
}
