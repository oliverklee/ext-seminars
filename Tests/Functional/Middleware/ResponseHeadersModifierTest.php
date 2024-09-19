<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Middleware;

use OliverKlee\Seminars\Middleware\ResponseHeadersModifier;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Middleware\ResponseHeadersModifier
 */
final class ResponseHeadersModifierTest extends FunctionalTestCase
{
    protected bool $initializeDatabase = false;

    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @test
     */
    public function isRegisteredAsBackendMiddleware(): void
    {
        $middlewares = $this->get('backend.middlewares');
        self::assertInstanceOf(\ArrayObject::class, $middlewares);

        self::assertSame(ResponseHeadersModifier::class, $middlewares['oliverklee/seminars/response-headers-modifier']);
    }

    /**
     * @test
     */
    public function isRegisteredAsFrontendMiddleware(): void
    {
        $middlewares = $this->get('frontend.middlewares');
        self::assertInstanceOf(\ArrayObject::class, $middlewares);

        self::assertSame(ResponseHeadersModifier::class, $middlewares['oliverklee/seminars/response-headers-modifier']);
    }
}
