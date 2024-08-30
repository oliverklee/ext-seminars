<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\SchedulerTasks;

use OliverKlee\Seminars\SchedulerTasks\MailNotifierConfiguration;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\SchedulerTasks\MailNotifierConfiguration
 */
final class MailNotifierConfigurationTest extends UnitTestCase
{
    /**
     * @var MailNotifierConfiguration
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new MailNotifierConfiguration();
    }

    /**
     * @test
     */
    public function implementsAdditionalFieldProvider(): void
    {
        self::assertInstanceOf(AdditionalFieldProviderInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function isAdditionalFieldProvider(): void
    {
        self::assertInstanceOf(AbstractAdditionalFieldProvider::class, $this->subject);
    }
}
