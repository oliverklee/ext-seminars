<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Traits;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\MailMessage;

/**
 * Helper for writing tests for sending emails.
 *
 * @mixin TestCase
 */
trait EmailTrait
{
    /**
     * @var (MailMessage&MockObject)|null
     */
    private $email = null;

    /**
     * @return MailMessage&MockObject
     */
    private function createEmailMock(): MailMessage
    {
        /** @var MailMessage&MockObject $message */
        $message = $this->getMockBuilder(MailMessage::class)
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMethods(['send'])
            ->getMock();

        return $message;
    }

    /**
     * Returns the recipients of the given email message in an array using the email addresses as keys and names
     * as values.
     *
     * @return array<string, string>
     */
    private function getToOfEmail(MailMessage $message): array
    {
        // @phpstan-ignore-next-line This line is V9-specific, and we are running PHPStan with V10.
        if ($message instanceof \Swift_Message) {
            $addresses = $message->getTo();
        } else {
            $addresses = $this->addressesToArray($message->getTo());
        }

        return $addresses;
    }

    /**
     * Returns the sender(s) of the given email message in an array using the email addresses as keys and names
     * as values.
     *
     * @return array<string, string>
     */
    private function getFromOfEmail(MailMessage $message): array
    {
        // @phpstan-ignore-next-line This line is V9-specific, and we are running PHPStan with V10.
        if ($message instanceof \Swift_Message) {
            $addresses = $message->getFrom();
        } else {
            $addresses = $this->addressesToArray($message->getFrom());
        }

        return $addresses;
    }

    /**
     * Returns the reply-tos of the given email message in an array using the email addresses as keys and names
     * as values.
     *
     * @return array<string, string>
     */
    private function getReplyToOfEmail(MailMessage $message): array
    {
        // @phpstan-ignore-next-line This line is V9-specific, and we are running PHPStan with V10.
        if ($message instanceof \Swift_Message) {
            /** @var array<string, string> $addresses */
            $addresses = $message->getReplyTo();
        } else {
            $addresses = $this->addressesToArray($message->getReplyTo());
        }

        return $addresses;
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
     * Returns plain-text content of an email.
     */
    private function getTextBodyOfEmail(MailMessage $message): string
    {
        // @phpstan-ignore-next-line This line is V9-specific, and we are running PHPStan with V10.
        if ($message instanceof \Swift_Message) {
            /** @var array<string, string> $text */
            $text = $message->getBody();
        } else {
            $text = $message->getTextBody();
        }

        return $text;
    }

    /**
     * Returns HTML content of an email.
     */
    private function getHtmlBodyOfEmail(MailMessage $message): string
    {
        // @phpstan-ignore-next-line This line is V9-specific, and we are running PHPStan with V10.
        if ($message instanceof \Swift_Message) {
            $htmlPart = $this->filterSwiftEmailAttachmentsByType($message, 'text/html')[0] ?? null;
            $htmlBody = $htmlPart instanceof \Swift_Mime_MimeEntity ? (string)$htmlPart->getBody() : '';
        } else {
            $htmlBody = (string)$message->getHtmlBody();
        }

        return $htmlBody;
    }

    /**
     * Returns the attachments of $email that have a content type with the given content type.
     *
     * Example: a content type of `text/calendar` will also find attachments that have `text/calendar; charset="utf-8"`
     * as the content type.
     *
     * @return array<int, \Swift_Mime_MimeEntity>
     */
    private function filterEmailAttachmentsByType(\Swift_Message $email, string $contentType): array
    {
        /** @var array<int, \Swift_Mime_MimeEntity> $matches */
        $matches = [];

        foreach ($email->getChildren() as $attachment) {
            if (\strpos($attachment->getContentType(), $contentType) !== false) {
                $matches[] = $attachment;
            }
        }

        return $matches;
    }

    /**
     * Returns the attachments of $email that have a content type with the given content type.
     *
     * Example: a content type of `text/calendar` will also find attachments that have `text/calendar; charset="utf-8"`
     * as the content type.
     *
     * @return array<int, \Swift_Mime_MimeEntity>
     */
    private function filterSwiftEmailAttachmentsByType(\Swift_Message $email, string $contentType): array
    {
        /** @var array<int, \Swift_Mime_MimeEntity> $matches */
        $matches = [];

        foreach ($email->getChildren() as $attachment) {
            if (\strpos($attachment->getContentType(), $contentType) !== false) {
                $matches[] = $attachment;
            }
        }

        return $matches;
    }
}
