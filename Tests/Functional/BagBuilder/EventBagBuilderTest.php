<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BagBuilder;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Bag\AbstractBag;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class EventBagBuilderTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Seminars_BagBuilder_Event
     */
    private $subject;

    protected function setUp()
    {
        parent::setUp();

        $this->subject = new \Tx_Seminars_BagBuilder_Event();
    }

    /**
     * @param int $uid
     * @param AbstractBag $bag
     *
     * @return void
     */
    private static function assertBagContainsUid(int $uid, AbstractBag $bag)
    {
        $uids = GeneralUtility::intExplode(',', $bag->getUids(), true);
        self::assertContains($uid, $uids);
    }

    /**
     * @test
     */
    public function limitToEventsWithVacanciesForEventWithVacanciesAndOnlyOfflineAttendeesFindsThisEvent()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $this->subject->limitToEventsWithVacancies();
        $bag = $this->subject->build();

        self::assertBagContainsUid(1, $bag);
    }

    /**
     * @test
     */
    public function limitToEventsWithVacanciesForEventWithOneVacancyFindsThisEvent()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Events.xml');

        $this->subject->limitToEventsWithVacancies();
        $bag = $this->subject->build();

        self::assertBagContainsUid(2, $bag);
    }

    // Tests for limitToCategories

    /**
     * @test
     */
    public function limitToCategoriesWithEmptyStringsFindsEventWithoutCategories()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventBagBuilder/EventWithoutCategories.xml');

        $this->subject->limitToCategories('');

        $bag = $this->subject->build();

        self::assertBagContainsUid(1, $bag);
    }

    /**
     * @test
     */
    public function limitToCategoriesWithEmptyStringsFindsEventWithCategory()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventBagBuilder/EventWithOneCategory.xml');

        $this->subject->limitToCategories('');

        $bag = $this->subject->build();

        self::assertBagContainsUid(1, $bag);
    }

    /**
     * @test
     */
    public function limitToCategoriesWithEmptyStringResetsPreviousCategoryFilter()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventBagBuilder/EventWithoutCategories.xml');

        $this->subject->limitToCategories('2');
        $this->subject->limitToCategories('');

        $bag = $this->subject->build();

        self::assertFalse($bag->isEmpty());
    }

    /**
     * @test
     */
    public function limitToCategoriesWithUidOfExistingCategoryFindsEventWithTheGivenCategory()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventBagBuilder/EventWithOneCategory.xml');

        $this->subject->limitToCategories('1');

        $bag = $this->subject->build();

        self::assertBagContainsUid(1, $bag);
    }

    /**
     * @test
     */
    public function limitToCategoriesWithUidOfExistingAndInexistentCategoryFindsEventWithExistingCategory()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventBagBuilder/EventWithOneCategory.xml');

        $this->subject->limitToCategories('1,999');

        $bag = $this->subject->build();

        self::assertBagContainsUid(1, $bag);
    }

    /**
     * @return array<string, array{0: int}>
     */
    public function nonPositiveIntegerDataProvider(): array
    {
        return [
            'zero' => [0],
            'negative int' => [-1],
        ];
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function sqlStringCharacterDataProvider(): array
    {
        return [
            ';' => [';'],
            ',' => [','],
            '(' => ['('],
            ')' => [')'],
            'double quote' => ['"'],
            'single quote' => ["'"],
            'some random string' => ['There is no spoon.'],
        ];
    }

    /**
     * @test
     *
     * @param int|string $invalidUid
     *
     * @dataProvider nonPositiveIntegerDataProvider
     * @dataProvider sqlStringCharacterDataProvider
     */
    public function limitToCategoriesSilentlyIgnoresInvalidUids($invalidUid)
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventBagBuilder/EventWithOneCategory.xml');

        $this->subject->limitToCategories('1,' . $invalidUid);

        $bag = $this->subject->build();

        self::assertBagContainsUid(1, $bag);
    }

    /**
     * @test
     */
    public function limitToCategoriesWithUidOfExistingCategoryIgnoresEventOnlyWithOtherCategory()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventBagBuilder/EventWithOneCategory.xml');

        $this->subject->limitToCategories('2');

        $bag = $this->subject->build();

        self::assertTrue($bag->isEmpty());
    }

    /**
     * @test
     */
    public function limitToCategoriesWithInexistentCategoryUidIgnoresWithCategory()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventBagBuilder/EventWithOneCategory.xml');

        $this->subject->limitToCategories('15');

        $bag = $this->subject->build();

        self::assertTrue($bag->isEmpty());
    }

    /**
     * @test
     */
    public function limitToCategoriesWithUidOfExistingCategoryIgnoresEventWithoutCategories()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventBagBuilder/EventWithoutCategories.xml');

        $this->subject->limitToCategories('2');

        $bag = $this->subject->build();

        self::assertTrue($bag->isEmpty());
    }

    /**
     * @test
     */
    public function limitToCategoriesWithUidOfTwoExistingCategoriesFindsEventWithOneGivenCategory()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventBagBuilder/EventWithOneCategory.xml');

        $this->subject->limitToCategories('1,2');

        $bag = $this->subject->build();

        self::assertBagContainsUid(1, $bag);
    }

    /**
     * @test
     */
    public function limitToCategoriesWithUidOfTwoExistingCategoriesFindsEventWithBothCategories()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventBagBuilder/EventWithTwoCategories.xml');

        $this->subject->limitToCategories('1,2');

        $bag = $this->subject->build();

        self::assertBagContainsUid(1, $bag);
    }

    /**
     * @test
     */
    public function limitToCategoriesFindsMatchingTopic()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventBagBuilder/TopicWithOneCategory.xml');

        $this->subject->limitToCategories('1');

        $bag = $this->subject->build();

        self::assertBagContainsUid(1, $bag);
    }

    /**
     * @test
     */
    public function limitToCategoriesFindsDateOfMatchingTopic()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventBagBuilder/TopicWithOneCategory.xml');

        $this->subject->limitToCategories('1');

        $bag = $this->subject->build();

        self::assertBagContainsUid(2, $bag);
    }
}
