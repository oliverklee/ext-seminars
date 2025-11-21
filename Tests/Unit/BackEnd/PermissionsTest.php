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
        $this->backendUserMock->method('isAdmin')->willReturn(false);
        $this->backendUserMock->method('check')->willReturn(true);
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
        $this->backendUserMock->method('isAdmin')->willReturn(false);
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
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
        $this->backendUserMock->method('isAdmin')->willReturn(false);
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, true],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
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
        $this->backendUserMock->method('isAdmin')->willReturn(false);
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
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
        $this->backendUserMock->method('isAdmin')->willReturn(false);
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, true],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
            ]
        );
        $subject = new Permissions();

        self::assertTrue($subject->hasReadAccessToRegistrations());
    }

    /**
     * @test
     */
    public function hasWriteAccessToEventsForNoAccessReturnsFalse(): void
    {
        $this->backendUserMock->method('isAdmin')->willReturn(false);
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
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
        $this->backendUserMock->method('isAdmin')->willReturn(false);
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::EVENTS_TABLE_NAME, true],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
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
        $this->backendUserMock->method('isAdmin')->willReturn(false);
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, false],
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
        $this->backendUserMock->method('isAdmin')->willReturn(false);
        $this->backendUserMock->method('check')->willReturnMap(
            [
                ['tables_select', self::EVENTS_TABLE_NAME, false],
                ['tables_select', self::REGISTRATIONS_TABLE_NAME, false],
                ['tables_modify', self::EVENTS_TABLE_NAME, false],
                ['tables_modify', self::REGISTRATIONS_TABLE_NAME, true],
            ]
        );
        $subject = new Permissions();

        self::assertTrue($subject->hasWriteAccessToRegistrations());
    }

    /**
     * @return array<non-empty-string, array{0: bool}>
     */
    public static function booleanDataProvider(): array
    {
        return [
            'true' => [true],
            'false' => [false],
        ];
    }

    /**
     * @test
     *
     * @dataProvider booleanDataProvider
     */
    public function isAdminReturnsAdminStatusOfBackendUser(bool $isAdmin): void
    {
        $this->backendUserMock->method('check')->willReturn(true);
        $this->backendUserMock->method('isAdmin')->willReturn($isAdmin);

        $subject = new Permissions();

        self::assertSame($isAdmin, $subject->isAdmin());
    }
}
