<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Rendering;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Rendering\NullRenderingContext;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * @covers \OliverKlee\Seminars\Rendering\NullRenderingContext
 */
final class NullRenderingContextTest extends UnitTestCase
{
    /**
     * @test
     */
    public function implementsRenderingContextInterface(): void
    {
        $subject = new NullRenderingContext();

        self::assertInstanceOf(RenderingContextInterface::class, $subject);
    }
}
