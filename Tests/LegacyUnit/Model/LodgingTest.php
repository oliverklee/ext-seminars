<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Seminars\Model\Lodging;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Model\Lodging
 */
final class LodgingTest extends TestCase
{
    /**
     * @var Lodging
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new Lodging();
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
        $this->subject->setTitle('Shack');

        self::assertEquals(
            'Shack',
            $this->subject->getTitle()
        );
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
