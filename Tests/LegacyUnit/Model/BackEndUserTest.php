<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Seminars\Model\BackEndUser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Model\BackEndUser
 */
final class BackEndUserTest extends TestCase
{
    /**
     * @var BackEndUser
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new BackEndUser();
    }

    /**
     * @test
     */
    public function isModel(): void
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }
}
