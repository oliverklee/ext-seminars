<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Traits;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use TYPO3\CMS\Core\Mail\MailMessage;

/**
 * Helper for writing tests for sending emails.
 *
 * @phpstan-require-extends TestCase
 */
trait EmailTrait
{
    /**
     * @var MailMessage&MockObject
     */
    private MailMessage $email;

    /**
     * @return MailMessage&MockObject
     */
    private function createEmailMock(): MailMessage
    {
        return $this
            ->getMockBuilder(MailMessage::class)
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->onlyMethods(['send'])
            ->getMock();
    }

    /**
     * Returns the recipients of the given email message in an array using the email addresses as keys and names
     * as values.
     *
     * @return array<string, string>
     */
    private function getToOfEmail(MailMessage $message): array
    {
        return $this->addressesToArray($message->getTo());
    }

    /**
     * Returns the sender(s) of the given email message in an array using the email addresses as keys and names
     * as values.
     *
     * @return array<string, string>
     */
    private function getFromOfEmail(MailMessage $message): array
    {
        return $this->addressesToArray($message->getFrom());
    }

    /**
     * Returns the reply-tos of the given email message in an array using the email addresses as keys and names
     * as values.
     *
     * @return array<string, string>
     */
    private function getReplyToOfEmail(MailMessage $message): array
    {
        return $this->addressesToArray($message->getReplyTo());
    }

    /**
     * @param array<array-key, Address> $addresses
     *
     * @return array<string, string> keys: email addresses, values: names
     */
    private function addressesToArray(array $addresses): array
    {
        $plainAddresses = [];
        foreach ($addresses as $address) {
            $plainAddresses[$address->getAddress()] = $address->getName();
        }
        return $plainAddresses;
    }

    /**
     * Returns the attachments of $email that have a content type with the given content type.
     *
     * Example: a content type of `text/calendar` will also find attachments that have `text/calendar; charset="utf-8"`
     * as the content type.
     *
     * @return list<DataPart>
     */
    private function filterEmailAttachmentsByType(MailMessage $email, string $contentType): array
    {
        /** @var list<DataPart> $matches */
        $matches = [];

        foreach ($email->getAttachments() as $attachment) {
            if (\str_contains($this->getContentTypeForDataPart($attachment), $contentType)) {
                $matches[] = $attachment;
            }
        }

        return $matches;
    }

    private function getContentTypeForDataPart(DataPart $dataPart): string
    {
        return $dataPart->getMediaType() . '/' . $dataPart->getMediaSubtype();
    }
}
