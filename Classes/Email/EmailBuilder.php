<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Email;

use OliverKlee\Oelib\Interfaces\MailRole;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class is an abstraction for the email classes in TYPO3 V9 and V10.
 *
 * You will need to create a new instance of this class for each new email instance you would like to build.
 */
class EmailBuilder
{
    /**
     * @var MailMessage|\Swift_Message
     */
    private $email;

    public function __construct()
    {
        $this->email = GeneralUtility::makeInstance(MailMessage::class);
    }

    /**
     * Builds the email message according to the other calls made before.
     *
     * Calling other methods after building the message will still modify the built message as the builder holds and
     * returns a reference.
     *
     * Calling this method multiple times on the same builder instance will always return the same message instance.
     *
     * @return MailMessage
     */
    public function build(): MailMessage
    {
        return $this->email;
    }

    /**
     * Sets the subject.
     *
     * @return $this
     */
    public function subject(string $subject): self
    {
        $this->email->setSubject($subject);

        return $this;
    }

    /**
     * Sets the plain text body.
     *
     * @return $this
     */
    public function text(string $body): self
    {
        if ($this->email instanceof \Swift_Message) {
            $this->email->setBody($body);
        } else {
            $this->email->text($body);
        }

        return $this;
    }

    /**
     * Sets the HTML content.
     *
     * @return $this
     */
    public function html(string $html): self
    {
        if ($this->email instanceof \Swift_Message) {
            $this->email->addPart($html, 'text/html');
        } else {
            $this->email->html($html);
        }

        return $this;
    }

    /**
     * Sets the recipients.
     *
     * @param MailRole ...$recipients
     *
     * @return $this
     */
    public function to(...$recipients): self
    {
        if ($this->email instanceof \Swift_Message) {
            foreach ($recipients as $recipient) {
                $this->email->addTo($recipient->getEmailAddress(), $recipient->getName());
            }
        } else {
            /** @var array<int, Address> $preparedRecipients */
            $preparedRecipients = [];
            foreach ($recipients as $recipient) {
                $preparedRecipients[] = $this->mailRoleToAddress($recipient);
            }
            $this->email->to(...$preparedRecipients);
        }

        return $this;
    }

    /**
     * Sets the "from".
     *
     * @return $this
     */
    public function from(MailRole $from): self
    {
        if ($this->email instanceof \Swift_Message) {
            $this->email->setFrom($this->mailRoleToSwiftMailerAddress($from));
        } else {
            $this->email->from($this->mailRoleToAddress($from));
        }

        return $this;
    }

    /**
     * Sets the reply-to.
     *
     * @param MailRole $recipients
     *
     * @return $this
     */
    public function replyTo(...$recipients): self
    {
        if ($this->email instanceof \Swift_Message) {
            foreach ($recipients as $recipient) {
                $this->email->addReplyTo($recipient->getEmailAddress(), $recipient->getName());
            }
        } else {
            /** @var array<int, Address> $preparedRecipients */
            $preparedRecipients = [];
            foreach ($recipients as $recipient) {
                $preparedRecipients[] = $this->mailRoleToAddress($recipient);
            }
            $this->email->replyTo(...$preparedRecipients);
        }

        return $this;
    }

    /**
     * Adds an attachment.
     *
     * The name is only used in TYPO3 V10.
     *
     * @param string|null $contentType defaults to `application/octet-stream`
     *
     * @return $this
     */
    public function attach(string $body, ?string $contentType = null, ?string $fileName = null): self
    {
        if ($this->email instanceof \Swift_Message) {
            $attachment = \Swift_Attachment::newInstance($body, $fileName, $contentType);
            $this->email->attach($attachment);
        } else {
            $this->email->attach($body, $fileName, $contentType);
        }

        return $this;
    }

    private function mailRoleToAddress(MailRole $mailRole): Address
    {
        return new Address($mailRole->getEmailAddress(), $mailRole->getName());
    }

    /**
     * @return array<string, string>
     */
    private function mailRoleToSwiftMailerAddress(MailRole $mailRole): array
    {
        return [$mailRole->getEmailAddress() => $mailRole->getName()];
    }
}
