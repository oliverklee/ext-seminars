<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Model\FrontEndUserGroup;
use OliverKlee\Seminars\Model\Registration;

/**
 * @covers \OliverKlee\Seminars\Mapper\FrontEndUserMapper
 */
final class FrontEndUserMapperTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var FrontEndUserMapper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FrontEndUserMapper();
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

        /** @var FrontEndUserGroup $firstGroup */
        $firstGroup = $model->getUserGroups()->first();
        self::assertInstanceOf(FrontEndUserGroup::class, $firstGroup);
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
        self::assertInstanceOf(Registration::class, $registration);
        self::assertSame(1, $registration->getUid());
    }
}
