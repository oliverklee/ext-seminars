<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Seminars\Model\TargetGroup;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Model\TargetGroup
 */
final class TargetGroupTest extends TestCase
{
    /**
     * @var TargetGroup
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new TargetGroup();
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle(): void
    {
        $this->subject->setData(['title' => 'Housewives']);

        self::assertEquals(
            'Housewives',
            $this->subject->getTitle()
        );
    }
}
