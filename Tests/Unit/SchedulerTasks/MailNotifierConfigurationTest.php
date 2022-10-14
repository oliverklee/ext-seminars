<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\SchedulerTasks;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\SchedulerTasks\MailNotifierConfiguration;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;

final class MailNotifierConfigurationTest extends UnitTestCase
{
    /**
     * @var MailNotifierConfiguration
     */
    private $subject;

    protected function setUp(): void
    {
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
