<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Traits;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Mail\MailMessage;

/**
 * Helper for creating email mocks.
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
}
