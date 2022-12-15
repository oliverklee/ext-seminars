<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Csv;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Seminars\Csv\CsvResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * @covers \OliverKlee\Seminars\Csv\CsvResponse
 */
final class CsvResponseTest extends UnitTestCase
{
    /**
     * @var DummyConfiguration
     */
    private $configuration;

    protected function setUp(): void
    {
        $this->configuration = new DummyConfiguration();
        ConfigurationRegistry::getInstance()->set('plugin.tx_seminars', $this->configuration);
    }

    protected function tearDown(): void
    {
        ConfigurationRegistry::purgeInstance();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function implementsResponse(): void
    {
        self::assertInstanceOf(ResponseInterface::class, new CsvResponse(''));
    }

    /**
     * @test
     */
    public function hasCsvContentTypeWithHeader(): void
    {
        $subject = new CsvResponse('');

        $contentTypeHeader = $subject->getHeader('Content-Type')[0];
        self::assertStringContainsString('text/csv; header=present;', $contentTypeHeader);
    }

    /**
     * @test
     */
    public function hasCsvContentTypeWithHeaderForLowercasedHeaderName(): void
    {
        $subject = new CsvResponse('');

        $contentTypeHeader = $subject->getHeader('content-type')[0];
        self::assertStringContainsString('text/csv; header=present; ', $contentTypeHeader);
    }

    /**
     * @test
     */
    public function usesUtf8ForTheContentType(): void
    {
        $subject = new CsvResponse('');

        $contentTypeHeader = $subject->getHeader('Content-Type')[0];
        self::assertStringContainsString('charset=utf-8', $contentTypeHeader);
    }

    /**
     * @test
     */
    public function hasContentDispositionAttachment(): void
    {
        $subject = new CsvResponse('');

        $contentDispositionHeader = $subject->getHeader('Content-Disposition')[0];
        self::assertStringContainsString('attachment', $contentDispositionHeader);
    }

    /**
     * @test
     */
    public function hasContentDispositionAttachmentWithLowercasedHeaderName(): void
    {
        $subject = new CsvResponse('');

        $contentDispositionHeader = $subject->getHeader('content-disposition')[0];
        self::assertStringContainsString('attachment', $contentDispositionHeader);
    }

    /**
     * @test
     */
    public function usesProvidedFileNameInContentDisposition(): void
    {
        $filename = 'registrations.csv';
        $subject = new CsvResponse('php://temp', $filename);

        $contentDispositionHeader = $subject->getHeader('Content-Disposition')[0];
        self::assertStringContainsString('; filename=' . $filename, $contentDispositionHeader);
    }

    /**
     * @test
     */
    public function withoutFilenameNotUsesAnyFileNameInContentDisposition(): void
    {
        $subject = new CsvResponse('');

        $contentDispositionHeader = $subject->getHeader('Content-Disposition')[0];
        self::assertStringNotContainsString('filename=', $contentDispositionHeader);
    }

    /**
     * @test
     */
    public function bodyHasProvidedContent(): void
    {
        $bodyContent = "a;b;c\r\n1;2;3";

        $subject = new CsvResponse($bodyContent);

        self::assertSame($bodyContent, (string)$subject->getBody());
    }
}
