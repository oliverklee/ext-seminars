<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Bag;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;

final class CategoryBagTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Bag_Category
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->testingFramework->createRecord('tx_seminars_categories');

        $this->subject = new \Tx_Seminars_Bag_Category('is_dummy_record=1');
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
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
