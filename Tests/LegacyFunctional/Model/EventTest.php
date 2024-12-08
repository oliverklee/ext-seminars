<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyFunctional\Model;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\LanguageMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\Mapper\RegistrationMapper;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Model\Registration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\DateTimeAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\Model\AbstractTimeSpan
 * @covers \OliverKlee\Seminars\Model\Event
 */
final class EventTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    private Event $subject;

    protected function setUp(): void
    {
        parent::setUp();

        GeneralUtility::makeInstance(Context::class)
            ->setAspect('date', new DateTimeAspect(new \DateTimeImmutable('2018-04-26 12:42:23')));

        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configuration = new DummyConfiguration();
        $configurationRegistry->set('plugin.tx_seminars', $configuration);

        $this->subject = new Event();
    }

    protected function tearDown(): void
    {
        ConfigurationRegistry::purgeInstance();

        parent::tearDown();
    }

    /////////////////////////////////////
    // Tests regarding isSingleEvent().
    /////////////////////////////////////

    /**
     * @test
     */
    public function getLanguageWithLanguageReturnsLanguage(): void
    {
        $this->subject->setData(['language' => 'DE']);

        self::assertSame(
            MapperRegistry::get(LanguageMapper::class)->findByIsoAlpha2Code('DE'),
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function setLanguageSetsLanguage(): void
    {
        $language = MapperRegistry::get(LanguageMapper::class)->findByIsoAlpha2Code('DE');
        $this->subject->setLanguage($language);

        self::assertSame(
            $language,
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function hasLanguageWithLanguageReturnsTrue(): void
    {
        $language = MapperRegistry::get(LanguageMapper::class)->findByIsoAlpha2Code('DE');
        $this->subject->setLanguage($language);

        self::assertTrue(
            $this->subject->hasLanguage()
        );
    }

    /**
     * @test
     */
    public function getRegularRegistrationsReturnsRegularRegistrations(): void
    {
        /** @var Collection<Registration> $registrations */
        $registrations = new Collection();
        $registration = MapperRegistry::get(RegistrationMapper::class)
            ->getLoadedTestingModel(['registration_queue' => 0]);
        $registrations->add($registration);
        $this->subject->setRegistrations($registrations);

        self::assertEquals(
            $registration->getUid(),
            $this->subject->getRegularRegistrations()->getUids()
        );
    }

    /**
     * @test
     */
    public function getRegularRegistrationsNotReturnsQueueRegistrations(): void
    {
        /** @var Collection<Registration> $registrations */
        $registrations = new Collection();
        $registration = MapperRegistry::get(RegistrationMapper::class)
            ->getLoadedTestingModel(['registration_queue' => 1]);
        $registrations->add($registration);
        $this->subject->setRegistrations($registrations);

        self::assertTrue(
            $this->subject->getRegularRegistrations()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getQueueRegistrationsReturnsQueueRegistrations(): void
    {
        /** @var Collection<Registration> $registrations */
        $registrations = new Collection();
        $registration = MapperRegistry::get(RegistrationMapper::class)
            ->getLoadedTestingModel(['registration_queue' => 1]);
        $registrations->add($registration);
        $this->subject->setRegistrations($registrations);

        self::assertEquals(
            $registration->getUid(),
            $this->subject->getQueueRegistrations()->getUids()
        );
    }

    public function getQueueRegistrationsNotReturnsRegularRegistrations(): void
    {
        /** @var Collection<Registration> $registrations */
        $registrations = new Collection();
        $registration = MapperRegistry::get(RegistrationMapper::class)
            ->getLoadedTestingModel(['registration_queue' => 0]);
        $registrations->add($registration);
        $this->subject->setRegistrations($registrations);

        self::assertTrue(
            $this->subject->getQueueRegistrations()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getRegisteredSeatsCountsSingleSeatRegularRegistrations(): void
    {
        $registrations = new Collection();
        $registration = MapperRegistry::get(RegistrationMapper::class)
            ->getLoadedTestingModel(['seats' => 1]);
        $registrations->add($registration);
        $event = $this->createPartialMock(
            Event::class,
            ['getRegularRegistrations']
        );
        $event->setData([]);
        $event->method('getRegularRegistrations')
            ->willReturn($registrations);

        self::assertEquals(
            1,
            $event->getRegisteredSeats()
        );
    }

    /**
     * @test
     */
    public function getRegisteredSeatsCountsMultiSeatRegularRegistrations(): void
    {
        $registrations = new Collection();
        $registration = MapperRegistry::get(RegistrationMapper::class)
            ->getLoadedTestingModel(['seats' => 2]);
        $registrations->add($registration);
        $event = $this->createPartialMock(
            Event::class,
            ['getRegularRegistrations']
        );
        $event->setData([]);
        $event->method('getRegularRegistrations')
            ->willReturn($registrations);

        self::assertEquals(
            2,
            $event->getRegisteredSeats()
        );
    }

    /**
     * @test
     */
    public function getRegisteredSeatsNotCountsQueueRegistrations(): void
    {
        $queueRegistrations = new Collection();
        $registration = MapperRegistry::get(RegistrationMapper::class)
            ->getLoadedTestingModel(['seats' => 1]);
        $queueRegistrations->add($registration);
        $event = $this->createPartialMock(
            Event::class,
            ['getRegularRegistrations', 'getQueueRegistrations']
        );
        $event->setData([]);
        $event->method('getQueueRegistrations')
            ->willReturn($queueRegistrations);
        $event->method('getRegularRegistrations')
            ->willReturn(new Collection());

        self::assertEquals(
            0,
            $event->getRegisteredSeats()
        );
    }
}
