<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Bag;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Bag\EventBag;
use OliverKlee\Seminars\Service\RegistrationManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Bag\EventBag
 */
final class EventBagTest extends TestCase
{
    /**
     * @var EventBag
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->testingFramework->createRecord(
            'tx_seminars_seminars',
            ['title' => 'test event']
        );

        $this->subject = new EventBag('is_dummy_record=1');
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();

        RegistrationManager::purgeInstance();

        parent::tearDown();
    }

    ///////////////////////////////////////////
    // Tests for the basic bag functionality.
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function bagCanHaveAtLeastOneElement(): void
    {
        self::assertFalse(
            $this->subject->isEmpty()
        );
    }
}
