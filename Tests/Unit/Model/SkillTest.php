<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;

class SkillTest extends UnitTestCase
{
    /**
     * @var \Tx_Seminars_Model_Skill
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Model_Skill();
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
        $this->subject->setTitle('Superhero');

        self::assertSame(
            'Superhero',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle()
    {
        $this->subject->setData(['title' => 'Superhero']);

        self::assertSame(
            'Superhero',
            $this->subject->getTitle()
        );
    }
}
