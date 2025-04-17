<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Service;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use OliverKlee\Seminars\Domain\Repository\Event\EventRepository;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use OliverKlee\Seminars\Service\RegistrationGuard;
use OliverKlee\Seminars\Service\RegistrationManager;
use OliverKlee\Seminars\Service\RegistrationProcessor;
use OliverKlee\Seminars\Tests\Support\LanguageHelper;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Service\RegistrationProcessor
 */
final class RegistrationProcessorTest extends FunctionalTestCase
{
    use LanguageHelper;

    protected array $testExtensionsToLoad = [
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/seminars',
    ];

    private RegistrationProcessor $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeBackEndLanguage();

        $this->subject = new RegistrationProcessor(
            $this->createStub(RegistrationRepository::class),
            $this->createStub(EventRepository::class),
            $this->createStub(FrontendUserRepository::class),
            $this->createStub(RegistrationGuard::class),
            $this->createStub(RegistrationManager::class)
        );
    }

    /**
     * @test
     */
    public function createTitleForRegistrationWithoutUserThrowsException(): void
    {
        $registration = new Registration();
        $registration->setEvent(new SingleEvent());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1669023125);
        $this->expectExceptionMessage('The registration has no associated user.');

        $this->subject->createTitle($registration);
    }

    /**
     * @test
     */
    public function createTitleForRegistrationWithoutEventThrowsException(): void
    {
        $registration = new Registration();
        $registration->setUser(new FrontendUser());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1669023165);
        $this->expectExceptionMessage('The registration has no associated event.');

        $this->subject->createTitle($registration);
    }

    /**
     * @test
     */
    public function createTitleForUserWithFullNamePutsFullUserNameInTitle(): void
    {
        $registration = new Registration();
        $registration->setEvent(new SingleEvent());

        $user = new FrontendUser();
        $fullUserName = 'Saskia Doe';
        $user->setName($fullUserName);
        $registration->setUser($user);

        $this->subject->createTitle($registration);

        self::assertStringContainsString($fullUserName, $registration->getTitle());
    }

    /**
     * @test
     */
    public function createTitleForUserWithFirstAndLastNameOnlyPutsLastAndFirstNameInTitle(): void
    {
        $registration = new Registration();
        $registration->setEvent(new SingleEvent());

        $user = new FrontendUser();
        $firstName = 'Saskia ';
        $lastName = 'Doe';
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail('saskia@example.com');
        $registration->setUser($user);

        $this->subject->createTitle($registration);

        self::assertStringContainsString($lastName . ', ' . $firstName, $registration->getTitle());
    }

    /**
     * @test
     */
    public function createTitleForUserWithNoNamePutsEmailInTitle(): void
    {
        $registration = new Registration();
        $registration->setEvent(new SingleEvent());

        $user = new FrontendUser();
        $email = 'saskia@example.com';
        $user->setEmail($email);
        $registration->setUser($user);

        $this->subject->createTitle($registration);

        self::assertStringContainsString($email, $registration->getTitle());
    }

    /**
     * @test
     */
    public function createTitlePutsEventDisplayTitleInTitle(): void
    {
        $registration = new Registration();
        $registration->setUser(new FrontendUser());

        $event = new SingleEvent();
        $displayTitle = 'Test event';
        $event->setInternalTitle($displayTitle);
        $registration->setEvent($event);

        $this->subject->createTitle($registration);

        self::assertStringContainsString($displayTitle, $registration->getTitle());
    }

    /**
     * @test
     */
    public function createTitlePutsEventDateInTitle(): void
    {
        $registration = new Registration();
        $registration->setUser(new FrontendUser());

        $event = new SingleEvent();
        $eventStart = new \DateTime('2020-01-01 10:00:00');
        $event->setStart($eventStart);
        $registration->setEvent($event);

        $this->subject->createTitle($registration);

        $dateFormat = LocalizationUtility::translate('dateFormat', 'seminars');
        $expectedDate = $eventStart->format($dateFormat);
        self::assertStringContainsString($expectedDate, $registration->getTitle());
    }
}
