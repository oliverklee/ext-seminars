<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Service\EmailService;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * @covers \OliverKlee\Seminars\Service\EmailService
 */
final class EmailServiceTest extends UnitTestCase
{
    /**
     * @var EmailService
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new EmailService();
    }

    /**
     * @test
     */
    public function classIsSingleton(): void
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }
}
