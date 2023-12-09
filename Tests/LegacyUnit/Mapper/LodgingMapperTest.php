<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Mapper\LodgingMapper;
use OliverKlee\Seminars\Model\Lodging;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Mapper\LodgingMapper
 */
final class LodgingMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var LodgingMapper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new LodgingMapper();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();

        parent::tearDown();
    }

    // Tests concerning find

    /**
     * @test
     */
    public function findWithUidReturnsLodgingInstance(): void
    {
        self::assertInstanceOf(Lodging::class, $this->subject->find(1));
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_lodgings',
            ['title' => 'Shack']
        );

        $model = $this->subject->find($uid);
        self::assertEquals(
            'Shack',
            $model->getTitle()
        );
    }
}
