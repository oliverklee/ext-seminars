<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Email\Fixtures;

use OliverKlee\Oelib\Interfaces\MailRole;

final class TestingMailRole implements MailRole
{
    private string $name;

    private string $emailAddress;

    public function __construct(string $name, string $emailAddress)
    {
        $this->name = $name;
        $this->emailAddress = $emailAddress;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }
}
