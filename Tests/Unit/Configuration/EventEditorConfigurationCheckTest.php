<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Seminars\Configuration\EventEditorConfigurationCheck;

/**
 * @covers \OliverKlee\Seminars\Configuration\EventEditorConfigurationCheck
 */
final class EventEditorConfigurationCheckTest extends UnitTestCase
{
    /**
     * @var EventEditorConfigurationCheck
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new EventEditorConfigurationCheck(new DummyConfiguration(), 'plugin.tx_seminars_pi1');
    }

    /**
     * @test
     */
    public function isConfigurationCheck(): void
    {
        self::assertInstanceOf(AbstractConfigurationCheck::class, $this->subject);
    }
}
