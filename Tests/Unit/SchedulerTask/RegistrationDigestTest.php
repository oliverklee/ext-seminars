<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\SchedulerTask;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Interfaces\Configuration;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\SchedulerTask\RegistrationDigest;

final class RegistrationDigestTest extends UnitTestCase
{
    /**
     * @var RegistrationDigest
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->subject = new RegistrationDigest();
    }

    protected function tearDown(): void
    {
        MapperRegistry::purgeInstance();
        ConfigurationRegistry::purgeInstance();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function isInitializedInitiallyReturnsFalse(): void
    {
        self::assertFalse($this->subject->isInitialized());
    }

    /**
     * @test
     */
    public function isInitializedAfterInitializeObjectReturnsTrue(): void
    {
        $this->subject->initializeObject();

        self::assertTrue($this->subject->isInitialized());
    }

    /**
     * @test
     */
    public function initializeObjectCanBeCalledTwice(): void
    {
        $this->subject->initializeObject();
        $this->subject->initializeObject();

        self::assertTrue($this->subject->isInitialized());
    }

    /**
     * @test
     */
    public function initializeObjectInitializesConfiguration(): void
    {
        $this->subject->initializeObject();

        self::assertInstanceOf(Configuration::class, $this->subject->getConfiguration());
    }

    /**
     * @test
     */
    public function initializeObjectInitializesEventMapper(): void
    {
        $this->subject->initializeObject();

        self::assertInstanceOf(EventMapper::class, $this->subject->getEventMapper());
    }
}
