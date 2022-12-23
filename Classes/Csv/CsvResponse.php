<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Csv;

use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\Stream;

/**
 * HTTP response for CSV data.
 */
class CsvResponse extends Response
{
    /**
     * @param non-empty-string $filename
     */
    public function __construct(string $content, string $filename)
    {
        $body = new Stream('php://temp', 'wb+');
        $body->write($content);
        $body->rewind();
        parent::__construct($body);

        $this->headers['Content-Type'][] = 'text/csv; header=present; charset=utf-8';
        $this->lowercasedHeaderNames['content-type'] = 'Content-Type';

        $contentDisposition = 'attachment';
        if (\is_string($filename)) {
            $contentDisposition .= '; filename=' . $filename;
        }
        $this->headers['Content-Disposition'][] = $contentDisposition;
        $this->lowercasedHeaderNames['content-disposition'] = 'Content-Disposition';
    }
}
