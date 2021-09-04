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
 *
 * @author Stefano Kowalke <info@arroba-it.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
trait EmailTrait
{
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
