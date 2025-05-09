<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Email;

use OliverKlee\Oelib\Interfaces\MailRole;
use OliverKlee\Seminars\Domain\Model\FrontendUser;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class is an abstraction for the email classes in TYPO3 V9 and V10.
 *
 * You will need to create a new instance of this class for each new email instance you would like to build.
 *
 * @deprecated will be removed in version 6.0.0 in #2973
 *
 * @internal
 */
class EmailBuilder
{
    private MailMessage $email;

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
        $this->email->text($body);

        return $this;
    }

    /**
     * Sets the HTML content.
     *
     * @return $this
     */
    public function html(string $html): self
    {
        $this->email->html($html);

        return $this;
    }

    /**
     * Sets the recipients.
     *
     * @param MailRole|FrontendUser ...$recipients
     *
     * @return $this
     */
    public function to(...$recipients): self
    {
        /** @var list<Address> $preparedRecipients */
        $preparedRecipients = [];
        foreach ($recipients as $recipient) {
            $preparedRecipients[] = $this->mailRoleToAddress($recipient);
        }
        $this->email->to(...$preparedRecipients);

        return $this;
    }

    /**
     * Sets the "from".
     *
     * @return $this
     */
    public function from(MailRole $from): self
    {
        $this->email->from($this->mailRoleToAddress($from));

        return $this;
    }

    /**
     * Sets the reply-to.
     *
     * @param MailRole|FrontendUser $recipients
     *
     * @return $this
     */
    public function replyTo(...$recipients): self
    {
        /** @var list<Address> $preparedRecipients */
        $preparedRecipients = [];
        foreach ($recipients as $recipient) {
            $preparedRecipients[] = $this->mailRoleToAddress($recipient);
        }
        $this->email->replyTo(...$preparedRecipients);

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
        $this->email->attach($body, $fileName, $contentType);

        return $this;
    }

    /**
     * @param MailRole|FrontendUser $mailRole
     */
    private function mailRoleToAddress($mailRole): Address
    {
        if ($mailRole instanceof FrontendUser) {
            $address = new Address($mailRole->getEmail(), $mailRole->getName());
        } else {
            $address = new Address($mailRole->getEmailAddress(), $mailRole->getName());
        }

        return $address;
    }
}
