<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

/**
 * @covers \Tx_Seminars_Mapper_FrontEndUser
 */
final class FrontEndUserMapperTest extends FunctionalTestCase
{
    /**
     * @var array<int, string>
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Seminars_Mapper_FrontEndUser
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new \Tx_Seminars_Mapper_FrontEndUser();
    }

    /**
     * @test
     */
    public function loadForExistingRecordLoadsScalarData(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/FrontEndUsers.xml');

        $model = $this->subject->find(1);

        $this->subject->load($model);

        self::assertSame('ben', $model->getUserName());
    }

    /**
     * @test
     */
    public function loadPopulatesUserGroupsAssociation(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/FrontEndUsers.xml');

        $model = $this->subject->find(1);

        $this->subject->load($model);

        /** @var \Tx_Seminars_Model_FrontEndUserGroup $firstGroup */
        $firstGroup = $model->getUserGroups()->first();
        self::assertInstanceOf(\Tx_Seminars_Model_FrontEndUserGroup::class, $firstGroup);
        self::assertSame(1, $firstGroup->getUid());
        self::assertSame('Editors', $firstGroup->getTitle());
    }

    /**
     * @test
     */
    public function loadPopulatesRegistrationsAssociation(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/FrontEndUsers.xml');

        $model = $this->subject->find(1);

        $this->subject->load($model);

        $registration = $model->getRegistration();
        self::assertInstanceOf(\Tx_Seminars_Model_Registration::class, $registration);
        self::assertSame(1, $registration->getUid());
    }
}
