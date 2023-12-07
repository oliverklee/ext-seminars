<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Model\Skill;

/**
 * @covers \OliverKlee\Seminars\Model\Skill
 */
final class SkillTest extends UnitTestCase
{
    /**
     * @var Skill
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Skill();
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle(): void
    {
        $this->subject->setData(['title' => 'Superhero']);

        self::assertSame(
            'Superhero',
            $this->subject->getTitle()
        );
    }
}
