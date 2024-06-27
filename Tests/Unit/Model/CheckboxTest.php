<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Model\Checkbox;

/**
 * @covers \OliverKlee\Seminars\Model\Checkbox
 */
final class CheckboxTest extends UnitTestCase
{
    /**
     * @var Checkbox
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Checkbox();
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle(): void
    {
        $this->subject->setData(['title' => 'I agree with the T&C.']);

        self::assertSame(
            'I agree with the T&C.',
            $this->subject->getTitle()
        );
    }
}
