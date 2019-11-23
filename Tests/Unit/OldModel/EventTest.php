<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class EventTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_OldModel_Event
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_OldModel_Event();
    }

    /**
     * @test
     */
    public function isAbstractModel()
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }
}
