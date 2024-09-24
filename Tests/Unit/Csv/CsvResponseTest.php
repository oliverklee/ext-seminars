<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Csv;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Seminars\Csv\CsvResponse;
use Psr\Http\Message\ResponseInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Csv\CsvResponse
 */
final class CsvResponseTest extends UnitTestCase
{
    private DummyConfiguration $configuration;

    protected function setUp(): void
    {
        parent::setUp();

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
        self::assertInstanceOf(ResponseInterface::class, new CsvResponse('', 'things.csv'));
    }

    /**
     * @test
     */
    public function hasCsvContentTypeWithHeader(): void
    {
        $subject = new CsvResponse('', 'things.csv');

        $contentTypeHeader = $subject->getHeader('Content-Type')[0];
        self::assertStringContainsString('text/csv; header=present;', $contentTypeHeader);
    }

    /**
     * @test
     */
    public function hasCsvContentTypeWithHeaderForLowercasedHeaderName(): void
    {
        $subject = new CsvResponse('', 'things.csv');

        $contentTypeHeader = $subject->getHeader('content-type')[0];
        self::assertStringContainsString('text/csv; header=present; ', $contentTypeHeader);
    }

    /**
     * @test
     */
    public function usesUtf8ForTheContentType(): void
    {
        $subject = new CsvResponse('', 'things.csv');

        $contentTypeHeader = $subject->getHeader('Content-Type')[0];
        self::assertStringContainsString('charset=utf-8', $contentTypeHeader);
    }

    /**
     * @test
     */
    public function hasContentDispositionAttachment(): void
    {
        $subject = new CsvResponse('', 'things.csv');

        $contentDispositionHeader = $subject->getHeader('Content-Disposition')[0];
        self::assertStringContainsString('attachment', $contentDispositionHeader);
    }

    /**
     * @test
     */
    public function hasContentDispositionAttachmentWithLowercasedHeaderName(): void
    {
        $subject = new CsvResponse('', 'things.csv');

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
    public function bodyHasProvidedContent(): void
    {
        $bodyContent = "a;b;c\r\n1;2;3";

        $subject = new CsvResponse($bodyContent, 'things.csv');

        self::assertSame($bodyContent, (string)$subject->getBody());
    }
}
