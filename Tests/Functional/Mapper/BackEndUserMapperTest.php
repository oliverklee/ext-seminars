<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * @covers \Tx_Seminars_Mapper_BackEndUser
 */
final class BackEndUserMapperTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Seminars_Mapper_BackEndUser
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $GLOBALS['BE_USER'] = $this->prophesize(BackendUserAuthentication::class)->reveal();

        $this->subject = new \Tx_Seminars_Mapper_BackEndUser();
    }

    /**
     * @test
     */
    public function loadForExistingRecordLoadsScalarData()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');
        $model = $this->subject->find(1);

        $this->subject->load($model);

        self::assertSame('max', $model->getUserName());
    }

    /**
     * @test
     */
    public function loadPopulatesUserGroupAssociation()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');
        $model = $this->subject->find(1);

        $this->subject->load($model);

        $userGroups = $model->getGroups();

        $firstGroup = $userGroups->first();
        self::assertInstanceOf(\Tx_Seminars_Model_BackEndUserGroup::class, $firstGroup);
        self::assertSame(1, $firstGroup->getUid());
    }
}
