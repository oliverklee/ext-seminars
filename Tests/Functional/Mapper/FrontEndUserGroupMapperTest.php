<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Mapper\FrontEndUserGroupMapper;

/**
 * @covers \OliverKlee\Seminars\Mapper\FrontEndUserGroupMapper
 */
final class FrontEndUserGroupMapperTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var FrontEndUserGroupMapper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FrontEndUserGroupMapper();
    }

    /**
     * @test
     */
    public function loadForExistingRecordLoadsScalarData(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/FrontEndUsers.xml');

        $model = $this->subject->find(1);

        $this->subject->load($model);

        self::assertSame('Editors', $model->getTitle());
    }
}
