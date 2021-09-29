<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

/**
 * @covers \Tx_Seminars_Mapper_BackEndUserGroup
 */
final class BackEndUserGroupMapperTest extends FunctionalTestCase
{
    /**
     * @var array<int, string>
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Seminars_Mapper_BackEndUserGroup
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new \Tx_Seminars_Mapper_BackEndUserGroup();
    }

    /**
     * @test
     */
    public function loadForExistingRecordLoadsScalarData(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');

        $model = $this->subject->find(1);

        $this->subject->load($model);

        self::assertSame('Content people', $model->getTitle());
    }
}
