<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Model;

use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Seminars\Model\FrontEndUser;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Model\FrontEndUser
 */
final class FrontEndUserTest extends UnitTestCase
{
    private FrontEndUser $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FrontEndUser();
    }

    /**
     * @test
     */
    public function isModel(): void
    {
        self::assertInstanceOf(AbstractModel::class, $this->subject);
    }
}
