<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Domain\Model\Event;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Event
 */
final class EventTest extends UnitTestCase
{
    /**
     * @var Event
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Event();
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
    public function getInternalTitleInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getInternalTitle());
    }

    /**
     * @test
     */
    public function setInternalTitleSetsTitle(): void
    {
        $value = 'TYPO3 extension development';
        $this->subject->setInternalTitle($value);

        self::assertSame($value, $this->subject->getInternalTitle());
    }
}
