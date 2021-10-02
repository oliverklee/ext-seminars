<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Model\PaymentMethod;

final class PaymentMethodTest extends TestCase
{
    /**
     * @var PaymentMethod
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new PaymentMethod();
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
        $this->subject->setTitle('Cash');

        self::assertEquals(
            'Cash',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle(): void
    {
        $this->subject->setData(['title' => 'Cash']);

        self::assertEquals(
            'Cash',
            $this->subject->getTitle()
        );
    }

    /////////////////////////////////////
    // Tests regarding the description.
    /////////////////////////////////////

    /**
     * @test
     */
    public function getDescriptionWithoutDescriptionReturnsAnEmptyString(): void
    {
        $this->subject->setData([]);

        self::assertEquals(
            '',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionWithDescriptionReturnsDescription(): void
    {
        $this->subject->setData(['description' => 'Just plain cash, baby!']);

        self::assertEquals(
            'Just plain cash, baby!',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function setDescriptionSetsDescription(): void
    {
        $this->subject->setDescription('Just plain cash, baby!');

        self::assertEquals(
            'Just plain cash, baby!',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionWithoutDescriptionReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionWithDescriptionReturnsTrue(): void
    {
        $this->subject->setDescription('Just plain cash, baby!');

        self::assertTrue(
            $this->subject->hasDescription()
        );
    }
}
