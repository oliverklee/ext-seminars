<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model;

use OliverKlee\Seminars\Domain\Model\RegistrationCheckbox;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\RegistrationCheckbox
 */
final class RegistrationCheckboxTest extends UnitTestCase
{
    private RegistrationCheckbox $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new RegistrationCheckbox();
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
