<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Mapper;

use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Mapper\CheckboxMapper;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Model\Checkbox;
use OliverKlee\Seminars\Model\FrontEndUser;
use PHPUnit\Framework\TestCase;

final class CheckboxMapperTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var CheckboxMapper
     */
    private $subject;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new CheckboxMapper();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    // Tests concerning find

    /**
     * @test
     */
    public function findWithUidReturnsCheckboxInstance(): void
    {
        self::assertInstanceOf(Checkbox::class, $this->subject->find(1));
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_checkboxes',
            ['title' => 'I agree with the T&C.']
        );
        $model = $this->subject->find($uid);

        self::assertEquals(
            'I agree with the T&C.',
            $model->getTitle()
        );
    }

    // Tests regarding the owner.

    /**
     * @test
     */
    public function getOwnerWithoutOwnerReturnsNull(): void
    {
        $model = $this->subject->getLoadedTestingModel([]);

        self::assertNull($model->getOwner());
    }

    /**
     * @test
     */
    public function getOwnerWithOwnerReturnsOwnerInstance(): void
    {
        $frontEndUser = MapperRegistry::get(FrontEndUserMapper::class)
            ->getLoadedTestingModel([]);
        $model = $this->subject->getLoadedTestingModel(['owner' => $frontEndUser->getUid()]);

        self::assertInstanceOf(FrontEndUser::class, $model->getOwner());
    }
}
