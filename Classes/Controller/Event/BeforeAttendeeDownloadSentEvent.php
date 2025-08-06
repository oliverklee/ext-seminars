<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\Event;

use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use Psr\Http\Message\StreamInterface;
use TYPO3\CMS\Core\Resource\ResourceInterface;

/**
 * Event that gets fired before an attendee download is sent.
 *
 * This event allows modifying the content stream before it is sent to the user, for example for adding a watermark.
 */
final class BeforeAttendeeDownloadSentEvent
{
    private Registration $registration;

    private ResourceInterface $fileResource;

    private StreamInterface $contentStream;

    public function __construct(
        Registration $registration,
        ResourceInterface $fileResource,
        StreamInterface $contentStream
    ) {
        $this->registration = $registration;
        $this->fileResource = $fileResource;
        $this->contentStream = $contentStream;
    }

    public function getRegistration(): Registration
    {
        return $this->registration;
    }

    public function getFileResource(): ResourceInterface
    {
        return $this->fileResource;
    }

    public function getContentStream(): StreamInterface
    {
        return $this->contentStream;
    }

    public function setContentStream(StreamInterface $contentStream): void
    {
        $this->contentStream = $contentStream;
    }
}
