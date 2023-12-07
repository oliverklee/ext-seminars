<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Rendering;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Rendering\NullRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\Uri;

/**
 * @covers \OliverKlee\Seminars\Rendering\NullRequest
 */
final class NullRequestTest extends UnitTestCase
{
    /**
     * @var NullRequest
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new NullRequest();
    }

    /**
     * @test
     */
    public function implementsServerRequestInterface(): void
    {
        self::assertInstanceOf(ServerRequestInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function getServerParamsReturnsEmptyArray(): void
    {
        $result = $this->subject->getServerParams();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getCookieParamsReturnsEmptyArray(): void
    {
        $result = $this->subject->getCookieParams();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function withCookieParamsReturnsInstanceOfSameClass(): void
    {
        self::assertInstanceOf(NullRequest::class, $this->subject->withCookieParams([]));
    }

    /**
     * @test
     */
    public function withCookieParamsReturnsNewInstance(): void
    {
        self::assertNotSame($this->subject, $this->subject->withCookieParams([]));
    }

    /**
     * @test
     */
    public function getQueryParamsReturnsEmptyArray(): void
    {
        $result = $this->subject->getQueryParams();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function withQueryParamsReturnsInstanceOfSameClass(): void
    {
        self::assertInstanceOf(NullRequest::class, $this->subject->withQueryParams([]));
    }

    /**
     * @test
     */
    public function withQueryParamsReturnsNewInstance(): void
    {
        self::assertNotSame($this->subject, $this->subject->withQueryParams([]));
    }

    /**
     * @test
     */
    public function getUploadedFilesReturnsEmptyArray(): void
    {
        $result = $this->subject->getUploadedFiles();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function withUploadedFilesReturnsInstanceOfSameClass(): void
    {
        self::assertInstanceOf(NullRequest::class, $this->subject->withUploadedFiles([]));
    }

    /**
     * @test
     */
    public function withUploadedFilesReturnsNewInstance(): void
    {
        self::assertNotSame($this->subject, $this->subject->withUploadedFiles([]));
    }

    /**
     * @test
     */
    public function getParsedBodyReturnsNull(): void
    {
        $result = $this->subject->getParsedBody();

        self::assertNull($result);
    }

    /**
     * @test
     */
    public function withParsedBodyReturnsInstanceOfSameClass(): void
    {
        self::assertInstanceOf(NullRequest::class, $this->subject->withParsedBody(null));
    }

    /**
     * @test
     */
    public function withParsedBodyReturnsNewInstance(): void
    {
        self::assertNotSame($this->subject, $this->subject->withParsedBody(null));
    }

    /**
     * @test
     */
    public function getAttributeWithEmptyStringReturnsEmptyString(): void
    {
        $result = $this->subject->getAttribute('');

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function getAttributeWithApplicationTypeReturnsBackendRequestType(): void
    {
        $result = $this->subject->getAttribute('applicationType');

        self::assertSame(SystemEnvironmentBuilder::REQUESTTYPE_BE, $result);
    }

    /**
     * @test
     */
    public function getAttributesReturnsEmptyArray(): void
    {
        $result = $this->subject->getAttributes();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function withAttributeReturnsInstanceOfSameClass(): void
    {
        self::assertInstanceOf(NullRequest::class, $this->subject->withAttribute('foo', 'bar'));
    }

    /**
     * @test
     */
    public function withAttributeReturnsNewInstance(): void
    {
        self::assertNotSame($this->subject, $this->subject->withAttribute('foo', 'bar'));
    }

    /**
     * @test
     */
    public function withoutAttributeReturnsInstanceOfSameClass(): void
    {
        self::assertInstanceOf(NullRequest::class, $this->subject->withoutAttribute('foo'));
    }

    /**
     * @test
     */
    public function withoutAttributeReturnsNewInstance(): void
    {
        self::assertNotSame($this->subject, $this->subject->withoutAttribute('foo'));
    }

    /**
     * @test
     */
    public function getRequestTargetReturnsEmptyString(): void
    {
        $result = $this->subject->getRequestTarget();

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function withRequestTargetReturnsInstanceOfSameClass(): void
    {
        self::assertInstanceOf(NullRequest::class, $this->subject->withRequestTarget(''));
    }

    /**
     * @test
     */
    public function withRequestTargetReturnsNewInstance(): void
    {
        self::assertNotSame($this->subject, $this->subject->withRequestTarget(''));
    }

    /**
     * @test
     */
    public function getMethodReturnsEmptyString(): void
    {
        $result = $this->subject->getMethod();

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function withMethodReturnsInstanceOfSameClass(): void
    {
        self::assertInstanceOf(NullRequest::class, $this->subject->withMethod(''));
    }

    /**
     * @test
     */
    public function withMethodReturnsNewInstance(): void
    {
        self::assertNotSame($this->subject, $this->subject->withMethod(''));
    }

    /**
     * @test
     */
    public function getUriReturnsUri(): void
    {
        $result = $this->subject->getUri();

        self::assertInstanceOf(UriInterface::class, $result);
    }

    /**
     * @test
     */
    public function withUriReturnsInstanceOfSameClass(): void
    {
        self::assertInstanceOf(NullRequest::class, $this->subject->withUri(new Uri('')));
    }

    /**
     * @test
     */
    public function withUriReturnsNewInstance(): void
    {
        self::assertNotSame($this->subject, $this->subject->withUri(new Uri('')));
    }

    /**
     * @test
     */
    public function getProtocolVersionReturnsEmptyString(): void
    {
        $result = $this->subject->getProtocolVersion();

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function withProtocolVersionReturnsInstanceOfSameClass(): void
    {
        self::assertInstanceOf(NullRequest::class, $this->subject->withProtocolVersion(''));
    }

    /**
     * @test
     */
    public function withProtocolVersionReturnsNewInstance(): void
    {
        self::assertNotSame($this->subject, $this->subject->withProtocolVersion(''));
    }

    /**
     * @test
     */
    public function getHeadersReturnsEmptyArray(): void
    {
        $result = $this->subject->getHeaders();

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function hasHeaderReturnsFalse(): void
    {
        $result = $this->subject->hasHeader('foo');

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function getHeaderReturnsEmptyArray(): void
    {
        $result = $this->subject->getHeader('foo');

        self::assertSame([], $result);
    }

    /**
     * @test
     */
    public function getHeaderLineReturnsEmptyString(): void
    {
        $result = $this->subject->getHeaderLine('foo');

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function withHeaderReturnsInstanceOfSameClass(): void
    {
        self::assertInstanceOf(NullRequest::class, $this->subject->withHeader('foo', ''));
    }

    /**
     * @test
     */
    public function withHeaderReturnsNewInstance(): void
    {
        self::assertNotSame($this->subject, $this->subject->withHeader('foo', ''));
    }

    /**
     * @test
     */
    public function withAddedHeaderReturnsInstanceOfSameClass(): void
    {
        self::assertInstanceOf(NullRequest::class, $this->subject->withAddedHeader('foo', ''));
    }

    /**
     * @test
     */
    public function withAddedHeaderReturnsNewInstance(): void
    {
        self::assertNotSame($this->subject, $this->subject->withAddedHeader('foo', ''));
    }

    /**
     * @test
     */
    public function withoutHeaderReturnsInstanceOfSameClass(): void
    {
        self::assertInstanceOf(NullRequest::class, $this->subject->withoutHeader('foo'));
    }

    /**
     * @test
     */
    public function withoutHeaderReturnsNewInstance(): void
    {
        self::assertNotSame($this->subject, $this->subject->withoutHeader('foo'));
    }

    /**
     * @test
     */
    public function getBodyMustNotBeCalled(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Not implemented.');
        $this->expectExceptionCode(1664435982);

        $this->subject->getBody();
    }

    /**
     * @test
     */
    public function withBodyReturnsInstanceOfSameClass(): void
    {
        $body = $this->createMock(StreamInterface::class);

        self::assertInstanceOf(NullRequest::class, $this->subject->withBody($body));
    }

    /**
     * @test
     */
    public function withBodyReturnsNewInstance(): void
    {
        $body = $this->createMock(StreamInterface::class);

        self::assertNotSame($this->subject, $this->subject->withBody($body));
    }
}
