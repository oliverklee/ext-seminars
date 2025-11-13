<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Controller;

use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @phpstan-require-extends UnitTestCase
 */
trait RedirectMockTrait
{
    /**
     * @param string|array<string, mixed>|int|null ...$arguments
     */
    private function mockRedirect(...$arguments): void
    {
        if ((new Typo3Version())->getMajorVersion() < 12) {
            $this->subject
                ->expects(self::once())->method('redirect')
                ->with(...$arguments)
                ->willThrowException(new StopActionException('redirectToUri', 1476045828));
            $this->expectException(StopActionException::class);
        } else {
            $redirectResponse = $this->createStub(RedirectResponse::class);
            $this->subject
                ->expects(self::once())->method('redirect')->with(...$arguments)
                ->willReturn($redirectResponse);
        }
    }

    private function stubRedirect(): void
    {
        if ((new Typo3Version())->getMajorVersion() < 12) {
            $this->subject
                ->method('redirect')
                ->willThrowException(new StopActionException('redirectToUri', 1476045828));
            $this->expectException(StopActionException::class);
        } else {
            $redirectResponse = $this->createStub(RedirectResponse::class);
            $this->subject->method('redirect')->willReturn($redirectResponse);
        }
    }

    /**
     * @param non-empty-string $uri
     */
    private function mockRedirectToUri(string $uri): void
    {
        if ((new Typo3Version())->getMajorVersion() < 12) {
            $this->subject
                ->expects(self::once())->method('redirectToUri')
                ->with($uri)
                ->willThrowException(new StopActionException('redirectToUri', 1476045828));
            $this->expectException(StopActionException::class);
        } else {
            $redirectResponse = $this->createStub(RedirectResponse::class);
            $this->subject
                ->expects(self::once())->method('redirectToUri')->with($uri)
                ->willReturn($redirectResponse);
        }
    }
}
