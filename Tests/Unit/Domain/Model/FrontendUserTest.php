<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser as ExtraFieldsFrontendUser;
use OliverKlee\Seminars\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\FrontendUser
 */
final class FrontendUserTest extends UnitTestCase
{
    private FrontendUser $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FrontendUser();
    }

    /**
     * @test
     */
    public function isAbstractEntity(): void
    {
        self::assertInstanceOf(AbstractEntity::class, $this->subject);
    }

    /**
     * @test
     */
    public function isExtraFieldsFrontendUserEntity(): void
    {
        self::assertInstanceOf(ExtraFieldsFrontendUser::class, $this->subject);
    }
}
