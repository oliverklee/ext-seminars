<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Service;

use OliverKlee\Seminars\Service\RegistrationManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Service\RegistrationManager
 */
final class RegistrationManagerTest extends UnitTestCase
{
    /**
     * @test
     */
    public function isSingleton(): void
    {
        $subject = new RegistrationManager();

        self::assertInstanceOf(SingletonInterface::class, $subject);
    }
}
