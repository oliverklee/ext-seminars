<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\SchedulerTasks;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\SchedulerTasks\MailNotifierConfiguration;
use TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface;

/**
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class MailNotifierConfigurationTest extends UnitTestCase
{
    /**
     * @var MailNotifierConfiguration
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new MailNotifierConfiguration();
    }

    /**
     * @test
     */
    public function classImplementsAdditionalFieldProvider()
    {
        self::assertInstanceOf(AdditionalFieldProviderInterface::class, $this->subject);
    }
}
