<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Bag;

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class CategoryBagTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Bag_Category
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $this->testingFramework->createRecord('tx_seminars_categories');

        $this->subject = new \Tx_Seminars_Bag_Category('is_dummy_record=1');
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    ///////////////////////////////////////////
    // Tests for the basic bag functionality.
    ///////////////////////////////////////////

    public function testBagCanHaveAtLeastOneElement()
    {
        self::assertFalse(
            $this->subject->isEmpty()
        );
    }
}
