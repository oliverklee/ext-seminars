<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Mapper\SpeakerMapper;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\Model\Speaker;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Mapper\SpeakerMapper
 */
final class SpeakerMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var SpeakerMapper
     */
    private $subject;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new SpeakerMapper();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    // Tests concerning find

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsOrganizerInstance(): void
    {
        $uid = $this->testingFramework->createRecord('tx_seminars_speakers');

        self::assertInstanceOf(
            Speaker::class,
            $this->subject->find($uid)
        );
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_speakers',
            ['title' => 'John Doe']
        );

        $model = $this->subject->find($uid);
        self::assertEquals(
            'John Doe',
            $model->getName()
        );
    }

    // Tests regarding the owner.

    /**
     * @test
     */
    public function getOwnerWithoutOwnerReturnsNull(): void
    {
        $testingModel = $this->subject->getLoadedTestingModel([]);

        self::assertNull($testingModel->getOwner());
    }

    /**
     * @test
     */
    public function getOwnerWithOwnerReturnsOwnerInstance(): void
    {
        $frontEndUser = MapperRegistry::get(FrontEndUserMapper::class)
            ->getLoadedTestingModel([]);
        $testingModel = $this->subject->getLoadedTestingModel(
            ['owner' => $frontEndUser->getUid()]
        );

        self::assertInstanceOf(FrontEndUser::class, $testingModel->getOwner());
    }
}
