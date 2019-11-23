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
    public function testingModelIsAbstractModel()
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }

    /**
     * @test
     */
    public function fromDataCreatesInstanceOfSubclass()
    {
        $result = TestingModel::fromData([]);

        self::assertInstanceOf(TestingModel::class, $result);
    }

    /**
     * @test
     */
    public function fromDataProvidesModelWithGivenData()
    {
        $data = ['test' => true];
        $result = TestingModel::fromData($data);

        self::assertSame($data['test'], $result->getBooleanTest());
    }

    /**
     * @test
     */
    public function fromDataResultsInOkay()
    {
        $subject = TestingModel::fromData(['title' => 'Foo']);

        self::assertTrue($subject->isOk());
    }
}
