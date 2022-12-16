<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Seminars\Model\FrontEndUserGroup;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OliverKlee\Seminars\Model\FrontEndUserGroup
 */
final class FrontEndUserGroupTest extends TestCase
{
    /**
     * @var FrontEndUserGroup the object to test
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new FrontEndUserGroup();
    }

    /**
     * @test
     */
    public function isModel(): void
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }
}
