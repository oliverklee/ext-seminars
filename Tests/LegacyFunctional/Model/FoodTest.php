<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Seminars\Model\Food;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Model\Food
 */
final class FoodTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    protected $initializeDatabase = false;

    /**
     * @var Food
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Food();
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle(): void
    {
        $this->subject->setData(['title' => 'Crunchy crisps']);

        self::assertEquals(
            'Crunchy crisps',
            $this->subject->getTitle()
        );
    }
}
