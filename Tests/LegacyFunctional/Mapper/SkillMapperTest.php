<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyFunctional\Mapper;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Mapper\SkillMapper;
use OliverKlee\Seminars\Model\Skill;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Mapper\SkillMapper
 */
final class SkillMapperTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    private TestingFramework $testingFramework;

    private SkillMapper $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new SkillMapper();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUpWithoutDatabase();

        parent::tearDown();
    }

    // Tests concerning find

    /**
     * @test
     */
    public function findWithUidReturnsSkillInstance(): void
    {
        self::assertInstanceOf(
            Skill::class,
            $this->subject->find(1)
        );
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel(): void
    {
        $uid = $this->testingFramework->createRecord(
            'tx_seminars_skills',
            ['title' => 'Superhero']
        );

        $model = $this->subject->find($uid);
        self::assertEquals(
            'Superhero',
            $model->getTitle()
        );
    }
}
