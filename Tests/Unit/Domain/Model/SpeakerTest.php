<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Interfaces\MailRole;
use OliverKlee\Seminars\Domain\Model\Speaker;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Speaker
 */
final class SpeakerTest extends UnitTestCase
{
    /**
     * @var Speaker
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Speaker();
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
    public function implementsMailRole(): void
    {
        self::assertInstanceOf(MailRole::class, $this->subject);
    }

    /**
     * @test
     */
    public function getNameInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getName());
    }

    /**
     * @test
     */
    public function setNameSetsName(): void
    {
        $value = 'Cactus Bill';
        $this->subject->setName($value);

        self::assertSame($value, $this->subject->getName());
    }

    /**
     * @test
     */
    public function getEmailAddressInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getEmailAddress());
    }

    /**
     * @test
     */
    public function setEmailAddressSetsEmailAddress(): void
    {
        $value = 'bill@example.com';
        $this->subject->setEmailAddress($value);

        self::assertSame($value, $this->subject->getEmailAddress());
    }
}
