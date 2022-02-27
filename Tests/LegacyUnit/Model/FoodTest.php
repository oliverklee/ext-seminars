<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Seminars\Model\Food;
use PHPUnit\Framework\TestCase;

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
    public function setTitleWithEmptyTitleThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $title must not be empty.'
        );

        $this->subject->setTitle('');
    }

    /**
     * @test
     */
    public function setTitleSetsTitle(): void
    {
        $this->subject->setTitle('Crunchy crisps');

        self::assertEquals(
            'Crunchy crisps',
            $this->subject->getTitle()
        );
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
