<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyFunctional\Bag;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Bag\CategoryBag;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Bag\CategoryBag
 */
final class CategoryBagTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    private CategoryBag $subject;

    private TestingFramework $testingFramework;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->testingFramework->createRecord('tx_seminars_categories');

        $this->subject = new CategoryBag();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUpWithoutDatabase();

        parent::tearDown();
    }

    ///////////////////////////////////////////
    // Tests for the basic bag functionality.
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function bagCanHaveAtLeastOneElement(): void
    {
        self::assertFalse(
            $this->subject->isEmpty()
        );
    }
}
