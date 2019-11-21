<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\OldModel;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\OldModel\AbstractModel;
use OliverKlee\Seminars\Tests\Unit\OldModel\Fixtures\TestingModel;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class AbstractModelTest extends UnitTestCase
{
    /**
     * @var TestingModel
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new TestingModel();
    }

    /**
     * @test
     */
    public function canBeCreatedWithoutConstructorParameters()
    {
        $subject = new TestingModel();

        self::assertNotNull($subject);
    }

    /**
     * @test
     */
    public function testingModelIsAbstractModel()
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }
}
