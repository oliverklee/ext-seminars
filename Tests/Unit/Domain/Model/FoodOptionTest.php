<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model;

use OliverKlee\Seminars\Domain\Model\FoodOption;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\FoodOption
 */
final class FoodOptionTest extends UnitTestCase
{
    private FoodOption $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FoodOption();
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
