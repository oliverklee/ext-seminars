<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Seminars\Model\Checkbox;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Model\Checkbox
 */
final class CheckboxTest extends TestCase
{
    /**
     * @var Checkbox
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new Checkbox();
    }

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle(): void
    {
        $this->subject->setData(['title' => 'I agree with the T&C.']);

        self::assertEquals(
            'I agree with the T&C.',
            $this->subject->getTitle()
        );
    }
}
