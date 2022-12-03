<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\BackEnd;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\BackEnd\Permissions;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

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
            ]
        );
        $subject = new Permissions();

        self::assertTrue($subject->hasReadAccessToRegistrations());
    }
}
