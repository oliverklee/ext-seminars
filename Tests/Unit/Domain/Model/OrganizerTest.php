<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model;

use OliverKlee\Oelib\Interfaces\MailRole;
use OliverKlee\Seminars\Domain\Model\Organizer;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Organizer
 */
final class OrganizerTest extends UnitTestCase
{
    private Organizer $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Organizer();
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
        $value = 'TYPO3 GmbH';
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
        $value = 'typo3-gmbh@example.com';
        $this->subject->setEmailAddress($value);

        self::assertSame($value, $this->subject->getEmailAddress());
    }

    /**
     * @test
     */
    public function getEmailFooterInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getEmailFooter());
    }

    /**
     * @test
     */
    public function setEmailFooterSetsEmailFooter(): void
    {
        $value = 'Club-Mate';
        $this->subject->setEmailFooter($value);

        self::assertSame($value, $this->subject->getEmailFooter());
    }

    /**
     * @test
     */
    public function hasEmailFooterForEmptyFooterReturnsFalse(): void
    {
        $this->subject->setEmailFooter('');

        self::assertFalse($this->subject->hasEmailFooter());
    }

    /**
     * @test
     */
    public function hasEmailFooterForNonEmptyFooterReturnsTrue(): void
    {
        $this->subject->setEmailFooter('The best speaker in the world!');

        self::assertTrue($this->subject->hasEmailFooter());
    }
}
