<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Domain\Model\AccommodationOption;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\AccommodationOption
 */
final class AccommodationOptionTest extends UnitTestCase
{
    /**
     * @var AccommodationOption
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new AccommodationOption();
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
    public function getTitleInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function setTitleSetsTitle(): void
    {
        $value = 'at home';
        $this->subject->setTitle($value);

        self::assertSame($value, $this->subject->getTitle());
    }
}
