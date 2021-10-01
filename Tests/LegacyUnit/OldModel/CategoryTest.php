<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\OldModel;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\OldModel\LegacyCategory;

/**
 * @covers \OliverKlee\Seminars\OldModel\LegacyCategory
 */
final class CategoryTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework;

    protected function setUp(): void
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();
    }

    /**
     * @test
     */
    public function createFromUidMapsAllFields(): void
    {
        $title = 'Test category';
        $icon = 'foo.gif';
        $subjectUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => $title, 'icon' => $icon]
        );
        $subject = new LegacyCategory($subjectUid);

        self::assertSame($title, $subject->getTitle());
        self::assertSame($icon, $subject->getIcon());
    }
}
