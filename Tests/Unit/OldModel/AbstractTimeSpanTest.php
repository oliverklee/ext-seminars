<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;
use OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures\TestingTimeSpan;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class AbstractTimeSpanTest extends UnitTestCase
{
    /**
     * @var TestingTimeSpan
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new TestingTimeSpan();
    }

    /**
     * @test
     */
    public function isAbstractModel()
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }
}
