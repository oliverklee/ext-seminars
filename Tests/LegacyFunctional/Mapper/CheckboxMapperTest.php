<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyFunctional\Mapper;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Mapper\CheckboxMapper;
use OliverKlee\Seminars\Model\Checkbox;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Mapper\CheckboxMapper
 */
final class CheckboxMapperTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    private TestingFramework $testingFramework;

    private CheckboxMapper $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new CheckboxMapper();
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
}
