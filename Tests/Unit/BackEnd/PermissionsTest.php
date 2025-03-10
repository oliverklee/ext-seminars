<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\BackEnd;

use OliverKlee\Seminars\BackEnd\Permissions;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\BackEnd\Permissions
 */
final class PermissionsTest extends UnitTestCase
{
    /**
     * @var non-empty-string
     */
    private const EVENTS_TABLE_NAME = 'tx_seminars_seminars';

    /**
     * @var non-empty-string
     */
    private const REGISTRATIONS_TABLE_NAME = 'tx_seminars_attendances';

    /**
     * @var non-empty-string
     */
    private const USERS_TABLE_NAME = 'fe_users';

    /**
     * @var BackendUserAuthentication&MockObject
     */
    private $backendUserMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->backendUserMock = $this->createMock(BackendUserAuthentication::class);
        $GLOBALS['BE_USER'] = $this->backendUserMock;
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['BE_USER']);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function classIsSingleton(): void
    {
        self::assertInstanceOf(SingletonInterface::class, new Permissions());
    }

    /**
     * @test
     */
    public function constructionWithoutBackEndUserSessionThrowsException(): void
    {
        unset($GLOBALS['BE_USER']);

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('No BE user session found.');
        $this->expectExceptionCode(1670069568);

        new Permissions();
    }

    /**
     * @test
     */
    public function hasReadAccessToEventsForNoAccessReturnsFalse(): void
    {
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_select', self::USERS_TABLE_NAME, false],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::USERS_TABLE_NAME, false],
            ]
        );
        $subject = new Permissions();

        self::assertFalse($subject->hasReadAccessToEvents());
    }

    /**
     * @test
     */
    public function hasReadAccessToEventsForAccessReturnsTrue(): void
    {
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, true],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_select', self::USERS_TABLE_NAME, false],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::USERS_TABLE_NAME, false],
            ]
        );
        $subject = new Permissions();

        self::assertTrue($subject->hasReadAccessToEvents());
    }

    /**
     * @test
     */
    public function hasReadAccessToRegistrationsForNoAccessReturnsFalse(): void
    {
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_select', self::USERS_TABLE_NAME, false],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::USERS_TABLE_NAME, false],
            ]
        );
        $subject = new Permissions();

        self::assertFalse($subject->hasReadAccessToRegistrations());
    }

    /**
     * @test
     */
    public function hasReadAccessToRegistrationsForAccessReturnsTrue(): void
    {
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, true],
                ['tables_select', self::USERS_TABLE_NAME, false],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::USERS_TABLE_NAME, false],
            ]
        );
        $subject = new Permissions();

        self::assertTrue($subject->hasReadAccessToRegistrations());
    }

    /**
     * @test
     */
    public function hasReadAccessToFrontEndUsersForNoAccessReturnsFalse(): void
    {
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_select', self::USERS_TABLE_NAME, false],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::USERS_TABLE_NAME, false],
            ]
        );
        $subject = new Permissions();

        self::assertFalse($subject->hasReadAccessToFrontEndUsers());
    }

    /**
     * @test
     */
    public function hasReadAccessToFrontEndUsersForAccessReturnsTrue(): void
    {
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_select', self::USERS_TABLE_NAME, true],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::USERS_TABLE_NAME, false],
            ]
        );
        $subject = new Permissions();

        self::assertTrue($subject->hasReadAccessToFrontEndUsers());
    }

    /**
     * @test
     */
    public function hasWriteAccessToEventsForNoAccessReturnsFalse(): void
    {
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_select', self::USERS_TABLE_NAME, false],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::USERS_TABLE_NAME, false],
            ]
        );
        $subject = new Permissions();

        self::assertFalse($subject->hasWriteAccessToEvents());
    }

    /**
     * @test
     */
    public function hasWriteAccessToEventsForAccessReturnsTrue(): void
    {
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_select', self::USERS_TABLE_NAME, false],
                ['tables_modify', self::EVENTS_TABLE_NAME, true],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::USERS_TABLE_NAME, false],
            ]
        );
        $subject = new Permissions();

        self::assertTrue($subject->hasWriteAccessToEvents());
    }

    /**
     * @test
     */
    public function hasWriteAccessToRegistrationsForNoAccessReturnsFalse(): void
    {
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_select', self::USERS_TABLE_NAME, false],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::USERS_TABLE_NAME, false],
            ]
        );
        $subject = new Permissions();

        self::assertFalse($subject->hasWriteAccessToRegistrations());
    }

    /**
     * @test
     */
    public function hasWriteAccessToRegistrationsForAccessReturnsTrue(): void
    {
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_select', self::USERS_TABLE_NAME, false],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, true],
                ['tables_modify', self::USERS_TABLE_NAME, false],
            ]
        );
        $subject = new Permissions();

        self::assertTrue($subject->hasWriteAccessToRegistrations());
    }

    /**
     * @test
     */
    public function hasWriteAccessToFrontEndUsersForNoAccessReturnsFalse(): void
    {
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_select', self::USERS_TABLE_NAME, false],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::USERS_TABLE_NAME, false],
            ]
        );
        $subject = new Permissions();

        self::assertFalse($subject->hasWriteAccessToFrontEndUsers());
    }

    /**
     * @test
     */
    public function hasWriteAccessToFrontEndUsersForAccessReturnsTrue(): void
    {
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_select', self::USERS_TABLE_NAME, true],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::USERS_TABLE_NAME, true],
            ]
        );
        $subject = new Permissions();

        self::assertTrue($subject->hasWriteAccessToFrontEndUsers());
    }
}
