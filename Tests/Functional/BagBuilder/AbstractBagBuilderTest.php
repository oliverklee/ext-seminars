<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BagBuilder;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Tests\Functional\Traits\BagHelper;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\BagBuilder\TestingBagBuilder;

/**
 * @covers \OliverKlee\Seminars\BagBuilder\AbstractBagBuilder
 */
final class AbstractBagBuilderTest extends FunctionalTestCase
{
    use BagHelper;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var TestingBagBuilder
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->subject = new TestingBagBuilder();
    }

    /**
     * @test
     */
    public function findsVisibleRecords(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $bag = $this->subject->build();

        self::assertBagHasUid($bag, 1);
    }

    /**
     * @test
     */
    public function byDefaultIgnoresHiddenRecords(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $bag = $this->subject->build();

        self::assertBagNotHasUid($bag, 2);
    }

    /**
     * @test
     */
    public function inBackEndModeFindsHiddenRecords(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $this->subject->setBackEndMode();
        $bag = $this->subject->build();

        self::assertBagHasUid($bag, 2);
    }

    /**
     * @test
     */
    public function byDefaultIgnoresTimedRecords(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $bag = $this->subject->build();

        self::assertBagNotHasUid($bag, 4);
    }

    /**
     * @test
     */
    public function inBackEndModeFindsTimedRecords(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $this->subject->setBackEndMode();
        $bag = $this->subject->build();

        self::assertBagHasUid($bag, 4);
    }

    /**
     * @test
     */
    public function byDefaultIgnoresDeletedRecords(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $bag = $this->subject->build();

        self::assertBagNotHasUid($bag, 3);
    }

    /**
     * @test
     */
    public function inBackEndModeIgnoresDeletedRecords(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $this->subject->setBackEndMode();
        $bag = $this->subject->build();

        self::assertBagNotHasUid($bag, 3);
    }

    /**
     * @test
     */
    public function limitToTitleFindRecordWithMatchingTitle(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $this->subject->limitToTitle('visible');
        $bag = $this->subject->build();

        self::assertBagHasUid($bag, 1);
    }

    /**
     * @test
     */
    public function limitToTitleIgnoresRecordWithNonMatchingTitle(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $this->subject->limitToTitle('some other title');
        $bag = $this->subject->build();

        self::assertBagNotHasUid($bag, 1);
    }
}
