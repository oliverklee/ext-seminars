<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\ViewHelpers;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\ViewHelpers\SalutationAwareTranslateViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * @covers \OliverKlee\Seminars\ViewHelpers\SalutationAwareTranslateViewHelper
 */
final class SalutationAwareTranslateViewHelperTest extends UnitTestCase
{
    /**
     * @var SalutationAwareTranslateViewHelper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new SalutationAwareTranslateViewHelper();
    }

    /**
     * @test
     */
    public function isViewHelper(): void
    {
        self::assertInstanceOf(AbstractViewHelper::class, $this->subject);
    }

    /**
     * @test
     */
    public function doesNotEscapeChildren(): void
    {
        self::assertFalse($this->subject->isChildrenEscapingEnabled());
    }
}
