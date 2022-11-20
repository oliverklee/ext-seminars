<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use OliverKlee\Seminars\Configuration\LegacyRegistrationConfiguration;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use OliverKlee\Seminars\Service\RegistrationGuard;
use OliverKlee\Seminars\Service\RegistrationManager;
use OliverKlee\Seminars\Service\RegistrationProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Seminars\Service\RegistrationProcessor
 */
final class RegistrationProcessorTest extends UnitTestCase
{
    /**
     * @var RegistrationRepository&MockObject
     */
    private $registrationRepositoryMock;

    /**
     * @var FrontEndUserRepository&MockObject
     */
    private $frontendUserRepositoryMock;

    /**
     * @var RegistrationGuard&MockObject
     */
    private $registrationGuardMock;

    /**
     * @var RegistrationManager&MockObject
     */
    private $registrationManagerMock;

    /**
     * @var RegistrationProcessor
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new RegistrationProcessor();

        $this->registrationRepositoryMock = $this->createMock(RegistrationRepository::class);
        $this->subject->injectRegistrationRepository($this->registrationRepositoryMock);
        $this->frontendUserRepositoryMock = $this->createMock(FrontendUserRepository::class);
        $this->subject->injectFrontendUserRepository($this->frontendUserRepositoryMock);
        $this->registrationGuardMock = $this->createMock(RegistrationGuard::class);
        $this->subject->injectRegistrationGuard($this->registrationGuardMock);
        $this->registrationManagerMock = $this->createMock(RegistrationManager::class);
        $this->subject->injectRegistrationManager($this->registrationManagerMock);
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function isSingleton(): void
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function enrichWithMetadataSetsEvent(): void
    {
        $event = new SingleEvent();
        $registration = new Registration();
        $this->registrationGuardMock->method('getFrontEndUserUidFromSession')->willReturn(15);
        $this->frontendUserRepositoryMock->method('findByUid')->with(self::anything())->willReturn(new FrontendUser());

        $this->subject->enrichWithMetadata($registration, $event, []);

        self::assertSame($event, $registration->getEvent());
    }

    /**
     * @test
     */
    public function enrichWithMetadataSetsUserFromSession(): void
    {
        $userUid = 99;
        $registration = new Registration();
        $user = new FrontendUser();
        $this->registrationGuardMock->expects(self::once())->method('getFrontEndUserUidFromSession')
            ->willReturn($userUid);
        $this->frontendUserRepositoryMock->expects(self::once())->method('findByUid')
            ->with($userUid)->willReturn($user);

        $this->subject->enrichWithMetadata($registration, new SingleEvent(), []);

        self::assertSame($user, $registration->getUser());
    }

    /**
     * @test
     */
    public function enrichWithMetadataWithoutUserUidInSessionThrowsException(): void
    {
        $registration = new Registration();
        $this->registrationGuardMock->expects(self::once())->method('getFrontEndUserUidFromSession')
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1668865776);
        $this->expectExceptionMessage('No user UID found in the session.');

        $this->subject->enrichWithMetadata($registration, new SingleEvent(), []);
    }

    /**
     * @test
     */
    public function enrichWithMetadataWithUserUidInSessionForInexistentUserThrowsException(): void
    {
        $userUid = 99;
        $registration = new Registration();
        $this->registrationGuardMock->expects(self::once())->method('getFrontEndUserUidFromSession')
            ->willReturn($userUid);
        $this->frontendUserRepositoryMock->expects(self::once())->method('findByUid')
            ->with($userUid)->willReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1668865839);
        $this->expectExceptionMessage('User with UID ' . $userUid . ' not found.');

        $this->subject->enrichWithMetadata($registration, new SingleEvent(), []);
    }

    /**
     * @test
     */
    public function enrichWithMetadataSetsFolderFromConfiguration(): void
    {
        $folderUid = 21;
        $registration = new Registration();
        $this->registrationGuardMock->method('getFrontEndUserUidFromSession')->willReturn(15);
        $this->frontendUserRepositoryMock->method('findByUid')->with(self::anything())->willReturn(new FrontendUser());

        $this->subject->enrichWithMetadata(
            $registration,
            new SingleEvent(),
            ['registrationRecordsStorageFolder' => (string)$folderUid]
        );

        self::assertSame($folderUid, $registration->getPid());
    }

    /**
     * @test
     */
    public function persistPersistsRegistration(): void
    {
        $registration = new Registration();

        $this->registrationRepositoryMock->expects(self::once())->method('add')->with($registration);
        $this->registrationRepositoryMock->expects(self::once())->method('persistAll');

        $this->subject->persist($registration);
    }

    /**
     * @test
     */
    public function sendEmailsForRegistrationWithoutUidThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1668939288);
        $this->expectExceptionMessage('The registration has not been persisted yet.');

        $this->subject->sendEmails(new Registration());
    }

    /**
     * @test
     */
    public function sendEmailsForRegistrationWithZeroUidThrowsException(): void
    {
        $registration = $this->createMock(Registration::class);
        $registration->method('getUid')->willReturn(0);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1668939288);
        $this->expectExceptionMessage('The registration has not been persisted yet.');

        $this->subject->sendEmails($registration);
    }

    /**
     * @test
     */
    public function sendEmailsSendsEmailsWithLegacyRegistrationAndLegacyConfiguration(): void
    {
        $registrationUid = 15;
        $registration = $this->createMock(Registration::class);
        $registration->method('getUid')->willReturn($registrationUid);

        $legacyRegistrationMock = $this->createMock(LegacyRegistration::class);
        GeneralUtility::addInstance(LegacyRegistration::class, $legacyRegistrationMock);
        $configurationMock = $this->createMock(LegacyRegistrationConfiguration::class);
        GeneralUtility::addInstance(LegacyRegistrationConfiguration::class, $configurationMock);

        $this->registrationManagerMock->expects(self::once())->method('setRegistration')
            ->with($legacyRegistrationMock);
        $this->registrationManagerMock->expects(self::once())->method('sendEmailsForNewRegistration')
            ->with($configurationMock);

        $this->subject->sendEmails($registration);
    }
}
