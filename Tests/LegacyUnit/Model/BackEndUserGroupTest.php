<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Seminars\Model\BackEndUserGroup;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Model\BackEndUserGroup
 */
final class BackEndUserGroupTest extends TestCase
{
    /**
     * @var BackEndUserGroup
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new BackEndUserGroup();
    }

    ////////////////////////////////
    // Tests concerning getTitle()
    ////////////////////////////////

    /**
     * @test
     */
    public function getTitleForNonEmptyGroupTitleReturnsGroupTitle(): void
    {
        $this->subject->setData(['title' => 'foo']);

        self::assertEquals(
            'foo',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleForEmptyGroupTitleReturnsEmptyString(): void
    {
        $this->subject->setData(['title' => '']);

        self::assertEquals(
            '',
            $this->subject->getTitle()
        );
    }
}
