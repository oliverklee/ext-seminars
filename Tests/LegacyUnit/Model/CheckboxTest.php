<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\PhpUnit\TestCase;

class CheckboxTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_Checkbox
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Model_Checkbox();
    }

    /**
     * @test
     */
    public function setTitleWithEmptyTitleThrowsException()
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
    public function setTitleSetsTitle()
    {
        $this->subject->setTitle('I agree with the T&C.');

        self::assertEquals(
            'I agree with the T&C.',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle()
    {
        $this->subject->setData(['title' => 'I agree with the T&C.']);

        self::assertEquals(
            'I agree with the T&C.',
            $this->subject->getTitle()
        );
    }

    /////////////////////////////////////
    // Tests regarding the description.
    /////////////////////////////////////

    /**
     * @test
     */
    public function getDescriptionWithoutDescriptionReturnsAnEmptyString()
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
    public function getDescriptionWithDescriptionReturnsDescription()
    {
        $this->subject->setData(['description' => 'I agree with the T&C.']);

        self::assertEquals(
            'I agree with the T&C.',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function setDescriptionSetsDescription()
    {
        $this->subject->setDescription('I agree with the T&C.');

        self::assertEquals(
            'I agree with the T&C.',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionWithoutDescriptionReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionWithDescriptionReturnsTrue()
    {
        $this->subject->setDescription('I agree with the T&C.');

        self::assertTrue(
            $this->subject->hasDescription()
        );
    }
}
