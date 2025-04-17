<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BagBuilder;

use OliverKlee\Seminars\Tests\Functional\BagBuilder\Fixtures\TestingBagBuilder;
use OliverKlee\Seminars\Tests\Functional\Traits\BagHelper;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\BagBuilder\AbstractBagBuilder
 */
final class AbstractBagBuilderTest extends FunctionalTestCase
{
    use BagHelper;

    protected array $testExtensionsToLoad = [
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/seminars',
    ];

    private TestingBagBuilder $subject;

    protected function setUp(): void
    {
        parent::setUp();

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
