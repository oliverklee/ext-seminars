<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyFunctional\Model;

use OliverKlee\Seminars\Model\Lodging;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Model\Lodging
 */
final class LodgingTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    protected bool $initializeDatabase = false;

    /**
     * @var Lodging
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Lodging();
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle(): void
    {
        $this->subject->setData(['title' => 'Shack']);

        self::assertEquals(
            'Shack',
            $this->subject->getTitle()
        );
    }
}
