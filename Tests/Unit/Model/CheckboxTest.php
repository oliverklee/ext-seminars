<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Model;

use OliverKlee\Seminars\Model\Checkbox;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Model\Checkbox
 */
final class CheckboxTest extends UnitTestCase
{
    private Checkbox $subject;

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
