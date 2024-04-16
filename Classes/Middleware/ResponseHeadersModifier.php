<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * @internal
 */
class ResponseHeadersModifier implements MiddlewareInterface, SingletonInterface
{
    /**
     * @var positive-int|null
     */
    private $overrideStatusCode;

    /**
     * @var array<non-empty-string, non-empty-string>
     */
    private $overrideHeaders = [];

    /**
     * @return positive-int|null
     */
    public function getOverrideStatusCode(): ?int
    {
        return $this->overrideStatusCode;
    }

    /**
     * @param positive-int $statusCode
     */
    public function setOverrideStatusCode(int $statusCode): void
    {
        $this->overrideStatusCode = $statusCode;
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     */
    public function getOverrideHeaders(): array
    {
        return $this->overrideHeaders;
    }

    /**
     * @param non-empty-string $headerName
     * @param non-empty-string $headerValue
     */
    public function addOverrideHeader(string $headerName, string $headerValue): void
    {
        $this->overrideHeaders[$headerName] = $headerValue;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (\is_int($this->overrideStatusCode)) {
            $response = $response->withStatus($this->overrideStatusCode);
        }
        foreach ($this->overrideHeaders as $headerName => $headerValue) {
            $response = $response->withHeader($headerName, $headerValue);
        }

        return $response;
    }
}
