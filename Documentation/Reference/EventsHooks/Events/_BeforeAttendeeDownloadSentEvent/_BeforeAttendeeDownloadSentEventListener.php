<?php

declare(strict_types=1);

namespace MyVendor\MyExtension\EventListener\Controller;

use OliverKlee\Seminars\Controller\Event\BeforeAttendeeDownloadSentEvent;

/**
 * Provides a modified PDF for the attendee download.
 */
final class BeforeAttendeeDownloadSentEventListener
{
    public function __invoke(BeforeAttendeeDownloadSentEvent $event): void
    {
        $contentStream = $event->getContentStream();

        // Here you would modify the content stream as needed.
        // For example, you could add a watermark or modify the PDF content.

        // Set the modified content stream back to the event.
        $event->setContentStream($contentStream);
    }
}
