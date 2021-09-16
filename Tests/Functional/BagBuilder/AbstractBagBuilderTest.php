<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BagBuilder;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Tests\Functional\Traits\BagHelper;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\BagBuilder\TestingBagBuilder;

/**
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class AbstractBagBuilderTest extends FunctionalTestCase
{
    use BagHelper;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var TestingBagBuilder
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->subject = new TestingBagBuilder();
    }

    /**
     * @test
     */
    public function findsVisibleRecords()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $bag = $this->subject->build();

        self::assertBagHasUid($bag, 1);
    }

    /**
     * @test
     */
    public function byDefaultIgnoresHiddenRecords()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $bag = $this->subject->build();

        self::assertBagNotHasUid($bag, 2);
    }

    /**
     * @test
     */
    public function inBackEndModeFindsHiddenRecords()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $this->subject->setBackEndMode();
        $bag = $this->subject->build();

        self::assertBagHasUid($bag, 2);
    }

    /**
     * @test
     */
    public function byDefaultIgnoresTimedRecords()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $bag = $this->subject->build();

        self::assertBagNotHasUid($bag, 4);
    }

    /**
     * @test
     */
    public function inBackEndModeFindsTimedRecords()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $this->subject->setBackEndMode();
        $bag = $this->subject->build();

        self::assertBagHasUid($bag, 4);
    }

    /**
     * @test
     */
    public function byDefaultIgnoresDeletedRecords()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $bag = $this->subject->build();

        self::assertBagNotHasUid($bag, 3);
    }

    /**
     * @test
     */
    public function inBackEndModeIgnoresDeletedRecords()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $this->subject->setBackEndMode();
        $bag = $this->subject->build();

        self::assertBagNotHasUid($bag, 3);
    }

    /**
     * @test
     */
    public function limitToTitleFindRecordWithMatchingTitle()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $this->subject->limitToTitle('visible');
        $bag = $this->subject->build();

        self::assertBagHasUid($bag, 1);
    }

    /**
     * @test
     */
    public function limitToTitleIgnoresRecordWithNonMatchingTitle()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Testing.xml');

        $this->subject->limitToTitle('some other title');
        $bag = $this->subject->build();

        self::assertBagNotHasUid($bag, 1);
    }
}
