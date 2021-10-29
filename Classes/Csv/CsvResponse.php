<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Csv;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;

/**
 * HTTP response for CSV data.
 */
class CsvResponse extends Response
{
    public function __construct(string $content, ?string $filename = null)
    {
        $body = new Stream('php://temp', 'wb+');
        $body->write($content);
        $body->rewind();
        parent::__construct($body);

        $charset = ConfigurationRegistry::get('plugin.tx_seminars')->getAsString('charsetForCsv');

        $this->headers['Content-Type'][] = 'text/csv; header=present; charset=' . $charset;
        $this->lowercasedHeaderNames['content-type'] = 'Content-Type';

        $contentDisposition = 'attachment';
        if (\is_string($filename)) {
            $contentDisposition .= '; filename=' . $filename;
        }
        $this->headers['Content-Disposition'][] = $contentDisposition;
        $this->lowercasedHeaderNames['content-disposition'] = 'Content-Disposition';
    }
}
