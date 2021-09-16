<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;

class CategoryMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Seminars_Mapper_Category
     */
    private $subject = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new \Tx_Seminars_Mapper_Category();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    // Tests concerning find

    /**
     * @test
     */
    public function findWithUidReturnsCategoryInstance()
    {
        self::assertInstanceOf(\Tx_Seminars_Model_Category::class, $this->subject->find(1));
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel()
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
