<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Mapper\BackEndUserMapper;
use OliverKlee\Seminars\Model\BackEndUserGroup;

/**
 * @covers \OliverKlee\Seminars\Mapper\BackEndUserMapper
 */
final class BackEndUserMapperTest extends FunctionalTestCase
{
    /**
     * @var array<int, string>
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var BackEndUserMapper
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new BackEndUserMapper();
    }

    /**
     * @test
     */
    public function loadForExistingRecordLoadsScalarData(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');
        $model = $this->subject->find(1);

        $this->subject->load($model);

        self::assertSame('max', $model->getUserName());
    }

    /**
     * @test
     */
    public function loadPopulatesUserGroupAssociation(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');
        $model = $this->subject->find(1);

        $this->subject->load($model);

        $userGroups = $model->getGroups();

        $firstGroup = $userGroups->first();
        self::assertInstanceOf(BackEndUserGroup::class, $firstGroup);
        self::assertSame(1, $firstGroup->getUid());
    }
}
