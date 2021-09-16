<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\SchedulerTask;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\Configuration;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\SchedulerTask\RegistrationDigest;

final class RegistrationDigestTest extends UnitTestCase
{
    /**
     * @var RegistrationDigest
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new RegistrationDigest();
    }

    protected function tearDown()
    {
        MapperRegistry::purgeInstance();
        ConfigurationRegistry::purgeInstance();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function isInitializedInitiallyReturnsFalse()
    {
        self::assertFalse($this->subject->isInitialized());
    }

    /**
     * @test
     */
    public function isInitializedAfterInitializeObjectReturnsTrue()
    {
        $this->subject->initializeObject();

        self::assertTrue($this->subject->isInitialized());
    }

    /**
     * @test
     */
    public function initializeObjectCanBeCalledTwice()
    {
        $this->subject->initializeObject();
        $this->subject->initializeObject();

        self::assertTrue($this->subject->isInitialized());
    }

    /**
     * @test
     */
    public function initializeObjectInitializesConfiguration()
    {
        $this->subject->initializeObject();

        self::assertInstanceOf(Configuration::class, $this->subject->getConfiguration());
    }

    /**
     * @test
     */
    public function initializeObjectInitializesEventMapper()
    {
        $this->subject->initializeObject();

        self::assertInstanceOf(\Tx_Seminars_Mapper_Event::class, $this->subject->getEventMapper());
    }
}
