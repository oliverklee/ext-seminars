<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\OldModel;

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class CategoryTest extends TestCase
{
    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');
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
