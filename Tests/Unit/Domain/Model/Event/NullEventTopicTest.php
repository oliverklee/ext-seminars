<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model\Event;

use OliverKlee\Seminars\Domain\Model\Event\EventTopicInterface;
use OliverKlee\Seminars\Domain\Model\Event\NullEventTopic;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Event\NullEventTopic
 */
final class NullEventTopicTest extends UnitTestCase
{
    private NullEventTopic $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new NullEventTopic();
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
    public function implementsEventTopicInterface(): void
    {
        self::assertInstanceOf(EventTopicInterface::class, $this->subject);
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

    /**
     * @test
     */
    public function getCategoriesReturnsEmptyObjectStorage(): void
    {
        $categories = $this->subject->getCategories();

        self::assertInstanceOf(ObjectStorage::class, $categories);
        self::assertCount(0, $categories);
    }
}
