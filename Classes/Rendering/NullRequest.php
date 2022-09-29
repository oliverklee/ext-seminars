<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Rendering;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\Uri;

/**
 * Dummy request to be used with `HtmlViewHelper`.
 */
final class NullRequest implements ServerRequestInterface
{
    public function getServerParams(): array
    {
        return [];
    }

    public function getCookieParams(): array
    {
        return [];
    }

    public function withCookieParams(array $cookies): self
    {
        return new self();
    }

    public function getQueryParams(): array
    {
        return [];
    }

    public function withQueryParams(array $query): self
    {
        return new self();
    }

    public function getUploadedFiles(): array
    {
        return [];
    }

    public function withUploadedFiles(array $uploadedFiles): self
    {
        return new NullRequest();
    }

    public function getParsedBody()
    {
        return null;
    }

    public function withParsedBody($data): self
    {
        return new NullRequest();
    }

    public function getAttribute($name, $default = null)
    {
        if ($name === 'applicationType') {
            return SystemEnvironmentBuilder::REQUESTTYPE_BE;
        }

        return '';
    }

    public function getAttributes(): array
    {
        return [];
    }

    public function withAttribute($name, $value): self
    {
        return new NullRequest();
    }

    public function withoutAttribute($name): self
    {
        return new NullRequest();
    }

    public function getRequestTarget(): string
    {
        return '';
    }

    public function withRequestTarget($requestTarget): self
    {
        return new NullRequest();
    }

    public function getMethod(): string
    {
        return '';
    }

    public function withMethod($method): self
    {
        return new NullRequest();
    }

    public function getUri(): UriInterface
    {
        return new Uri('');
    }

    public function withUri(UriInterface $uri, $preserveHost = false): self
    {
        return new NullRequest();
    }

    public function getProtocolVersion(): string
    {
        return '';
    }

    public function withProtocolVersion($version): self
    {
        return new NullRequest();
    }

    public function getHeaders(): array
    {
        return [];
    }

    public function hasHeader($name): bool
    {
        return false;
    }

    public function getHeader($name): array
    {
        return [];
    }

    public function getHeaderLine($name): string
    {
        return '';
    }

    public function withHeader($name, $value): self
    {
        return new NullRequest();
    }

    public function withAddedHeader($name, $value)
    {
        return new NullRequest();
    }

    public function withoutHeader($name): self
    {
        return new NullRequest();
    }

    /**
     * @return never
     */
    public function getBody(): StreamInterface
    {
        throw new \BadMethodCallException('Not implemented.', 1664435982);
    }

    public function withBody(StreamInterface $body): self
    {
        return new NullRequest();
    }
}
