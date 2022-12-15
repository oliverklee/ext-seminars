<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Mapper\CategoryMapper;
use OliverKlee\Seminars\Model\Category;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Mapper\CategoryMapper
 */
final class CategoryMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var CategoryMapper
     */
    private $subject;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new CategoryMapper();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    // Tests concerning find

    /**
     * @test
     */
    public function findWithUidReturnsCategoryInstance(): void
    {
        self::assertInstanceOf(Category::class, $this->subject->find(1));
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => 'Lecture']
        );
        $model = $this->subject->find($uid);

        self::assertEquals(
            'Lecture',
            $model->getTitle()
        );
    }
}
