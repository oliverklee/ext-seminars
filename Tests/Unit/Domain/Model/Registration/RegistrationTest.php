<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Domain\Model\Registration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @covers \OliverKlee\Seminars\Domain\Model\Registration\Registration
 */
final class RegistrationTest extends UnitTestCase
{
    /**
     * @var Registration
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Registration();
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
        $value = 'the latest registration';
        $this->subject->setTitle($value);

        self::assertSame($value, $this->subject->getTitle());
    }

    /**
     * @test
     */
    public function getEventInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getEvent());
    }

    /**
     * @test
     */
    public function setEventCanSetEventToSingleEvent(): void
    {
        $model = new SingleEvent();
        $this->subject->setEvent($model);

        self::assertSame($model, $this->subject->getEvent());
    }

    /**
     * @test
     */
    public function setEventCanSetEventToEventDate(): void
    {
        $model = new EventDate();
        $this->subject->setEvent($model);

        self::assertSame($model, $this->subject->getEvent());
    }

    /**
     * @test
     */
    public function hasValidEventTypeWithoutEventReturnsFalse(): void
    {
        self::assertFalse($this->subject->hasValidEventType());
    }

    /**
     * @test
     */
    public function hasValidEventTypeWithEventTopicReturnsFalse(): void
    {
        $this->subject->setEvent(new EventTopic());

        self::assertFalse($this->subject->hasValidEventType());
    }

    /**
     * @return array<string, array{0: Event}>
     */
    public function validEventTypesDataProvider(): array
    {
        return [
            'single event' => [new SingleEvent()],
            'event date' => [new EventDate()],
        ];
    }

    /**
     * @test
     *
     * @dataProvider validEventTypesDataProvider
     */
    public function hasValidEventTypeWithValidEventTypeReturnsTrue(Event $event): void
    {
        $this->subject->setEvent($event);

        self::assertTrue($this->subject->hasValidEventType());
    }

    /**
     * @test
     */
    public function getUserInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getUser());
    }

    /**
     * @test
     */
    public function setUserSetsUser(): void
    {
        $model = new FrontendUser();
        $this->subject->setUser($model);

        self::assertSame($model, $this->subject->getUser());
    }

    /**
     * @test
     *
     * @dataProvider validEventTypesDataProvider
     */
    public function hasNecessaryAssociationsWithUserAndValidEventTypeReturnsTrue(Event $event): void
    {
        $this->subject->setUser(new FrontendUser());
        $this->subject->setEvent($event);

        self::assertTrue($this->subject->hasNecessaryAssociations());
    }

    /**
     * @test
     *
     * @dataProvider validEventTypesDataProvider
     */
    public function hasNecessaryAssociationsWithoutUserAndWithValidEventTypeReturnsFalse(Event $event): void
    {
        $this->subject->setEvent($event);

        self::assertFalse($this->subject->hasNecessaryAssociations());
    }

    /**
     * @test
     */
    public function hasNecessaryAssociationsWithUserAndEventTopicReturnsFalse(): void
    {
        $this->subject->setUser(new FrontendUser());
        $this->subject->setEvent(new EventTopic());

        self::assertFalse($this->subject->hasNecessaryAssociations());
    }

    /**
     * @test
     */
    public function hasNecessaryAssociationsWithUserAndWithoutEventReturnsFalse(): void
    {
        $this->subject->setUser(new FrontendUser());

        self::assertFalse($this->subject->hasNecessaryAssociations());
    }

    /**
     * @test
     */
    public function hasNecessaryAssociationsWithNeitherUserNorEventReturnsFalse(): void
    {
        self::assertFalse($this->subject->hasNecessaryAssociations());
    }
}
