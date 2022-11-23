<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Service;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use OliverKlee\Seminars\Service\RegistrationProcessor;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * @covers \OliverKlee\Seminars\Service\RegistrationProcessor
 */
final class RegistrationProcessorTest extends FunctionalTestCase
{
    use LanguageHelper;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var RegistrationProcessor
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeBackEndLanguage();

        $this->subject = new RegistrationProcessor();
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
    public function createTitlePutsFullUserNameInTitle(): void
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
