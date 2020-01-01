<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class EventTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_Model_Event
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Model_Event();
    }

    /**
     * @test
     */
    public function isTitled()
    {
        self::assertInstanceOf(\Tx_Seminars_Interface_Titled::class, $this->subject);
    }
}
