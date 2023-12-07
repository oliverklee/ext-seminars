<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model;

use OliverKlee\Seminars\Domain\Model\EventTypeInterface;
use OliverKlee\Seminars\Domain\Model\NullEventType;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\NullEventType
 */
final class NullEventTypeTest extends UnitTestCase
{
    /**
     * @var NullEventType
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new NullEventType();
    }

    /**
     * @test
     */
    public function isDomainObject(): void
    {
        self::assertInstanceOf(AbstractDomainObject::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsEventTypeInterface(): void
    {
        self::assertInstanceOf(EventTypeInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function getUidReturnsNull(): void
    {
        self::assertNull($this->subject->getUid());
    }

    /**
     * @test
     */
    public function getTitleReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getTitle());
    }
}
