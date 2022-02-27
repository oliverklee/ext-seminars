<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Mapper\OrganizerMapper;
use OliverKlee\Seminars\Model\Organizer;
use PHPUnit\Framework\TestCase;

final class OrganizerMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var OrganizerMapper
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new OrganizerMapper();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    // Tests concerning find

    /**
     * @test
     */
    public function findWithUidReturnsOrganizerInstance(): void
    {
        self::assertInstanceOf(Organizer::class, $this->subject->find(1));
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_organizers',
            ['title' => 'Fabulous organizer']
        );

        $model = $this->subject->find($uid);
        self::assertEquals(
            'Fabulous organizer',
            $model->getName()
        );
    }
}
