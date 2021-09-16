<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\OldModel;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;

final class CategoryTest extends TestCase
{
    /**
     * @var TestingFramework
     */
    private $testingFramework;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    /**
     * @test
     */
    public function createFromUidMapsAllFields()
    {
        $title = 'Test category';
        $icon = 'foo.gif';
        $subjectUid = $this->testingFramework->createRecord(
            'tx_seminars_categories',
            ['title' => $title, 'icon' => $icon]
        );
        $subject = new \Tx_Seminars_OldModel_Category($subjectUid);

        self::assertSame($title, $subject->getTitle());
        self::assertSame($icon, $subject->getIcon());
    }
}
