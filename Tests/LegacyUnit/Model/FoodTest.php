<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Seminars\Model\Food;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Model\Food
 */
final class FoodTest extends TestCase
{
    /**
     * @var Food
     */
    private $subject;

    protected function setUp(): void
    {
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
