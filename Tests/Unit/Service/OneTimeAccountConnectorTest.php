<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Service;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Service\OneTimeAccountConnector;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\Service\OneTimeAccountConnector
 */
final class OneTimeAccountConnectorTest extends UnitTestCase
{
    /**
     * @var OneTimeAccountConnector
     */
    private $subject;

    /**
     * @var FrontendUserAuthentication&MockObject
     */
    private $frontEndUserAuthenticationMock;

    protected function setUp(): void
    {
        parent::setUp();

        $mockFrontEndController = $this->createMock(TypoScriptFrontendController::class);
        $this->frontEndUserAuthenticationMock = $this->createMock(FrontendUserAuthentication::class);
        $mockFrontEndController->fe_user = $this->frontEndUserAuthenticationMock;
        $GLOBALS['TSFE'] = $mockFrontEndController;

        $this->subject = new OneTimeAccountConnector();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TSFE']);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function constructionWithoutFrontEndThrowsException(): void
    {
        unset($GLOBALS['TSFE']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1668702167);
        $this->expectDeprecationMessage('No frontend found.');

        new OneTimeAccountConnector();
    }

    /**
     * @test
     */
    public function constructionWithFrontEndWithoutFrontEndUserAuthenticationThrowsException(): void
    {
        $GLOBALS['TSFE']->fe_user = '';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionCode(1668702517);
        $this->expectDeprecationMessage('Frontend found, but without a FE user authentication.');

        new OneTimeAccountConnector();
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
    public function existsOneTimeAccountLoginSessionForNoSessionDataReturnsFalse(): void
    {
        $this->frontEndUserAuthenticationMock->method('getKey')->with('user', 'onetimeaccount')->willReturn(null);

        self::assertFalse($this->subject->existsOneTimeAccountLoginSession());
    }

    /**
     * @test
     */
    public function existsOneTimeAccountLoginSessionForSessionDataReturnsTrue(): void
    {
        $this->frontEndUserAuthenticationMock->method('getKey')->with('user', 'onetimeaccount')->willReturn(true);

        self::assertTrue($this->subject->existsOneTimeAccountLoginSession());
    }

    /**
     * @test
     */
    public function getOneTimeAccountUserUidForNullUserUidReturnsNull(): void
    {
        $this->frontEndUserAuthenticationMock->method('getSessionData')->with('onetimeaccountUserUid')
            ->willReturn(null);

        self::assertNull($this->subject->getOneTimeAccountUserUid());
    }

    /**
     * @test
     */
    public function getOneTimeAccountUserUidForEmptyUserUidReturnsNull(): void
    {
        $this->frontEndUserAuthenticationMock->method('getSessionData')->with('onetimeaccountUserUid')->willReturn('');

        self::assertNull($this->subject->getOneTimeAccountUserUid());
    }

    /**
     * @test
     */
    public function getOneTimeAccountUserUidForZeroUserUidReturnsNull(): void
    {
        $this->frontEndUserAuthenticationMock->method('getSessionData')->with('onetimeaccountUserUid')->willReturn(0);

        self::assertNull($this->subject->getOneTimeAccountUserUid());
    }

    /**
     * @test
     */
    public function getOneTimeAccountUserUidForPositiveUserUidReturnsUserUid(): void
    {
        $userUid = 63;
        $this->frontEndUserAuthenticationMock->method('getSessionData')
            ->with('onetimeaccountUserUid')->willReturn($userUid);

        self::assertSame($userUid, $this->subject->getOneTimeAccountUserUid());
    }

    /**
     * @test
     */
    public function destroyOneTimeSessionForRegularLoginDoesNotLogUserOut(): void
    {
        $this->frontEndUserAuthenticationMock->method('getKey')->with('user', 'onetimeaccount')->willReturn(null);

        $this->frontEndUserAuthenticationMock->expects(self::never())->method('logoff');

        $this->subject->destroyOneTimeSession();
    }

    /**
     * @test
     */
    public function destroyOneTimeSessionForRegularLoginDoesSetAnySessionDate(): void
    {
        $this->frontEndUserAuthenticationMock->method('getKey')->with('user', 'onetimeaccount')->willReturn(null);

        $this->frontEndUserAuthenticationMock->expects(self::never())->method('setAndSaveSessionData');

        $this->subject->destroyOneTimeSession();
    }

    /**
     * @test
     */
    public function destroyOneTimeSessionForOneTimeLoginLogsUserOut(): void
    {
        $this->frontEndUserAuthenticationMock->method('getKey')->with('user', 'onetimeaccount')->willReturn(true);

        $this->frontEndUserAuthenticationMock->expects(self::once())->method('logoff');

        $this->subject->destroyOneTimeSession();
    }

    /**
     * @test
     */
    public function destroyOneTimeSessionForOneTimeSessionWithoutLoginRemovesUserUidFromSession(): void
    {
        $this->frontEndUserAuthenticationMock->method('getKey')->with('user', 'onetimeaccount')->willReturn(null);
        $this->frontEndUserAuthenticationMock->method('getSessionData')->with('onetimeaccountUserUid')
            ->willReturn(5);

        $this->frontEndUserAuthenticationMock->expects(self::once())->method('setAndSaveSessionData')
            ->with('onetimeaccountUserUid', null);

        $this->subject->destroyOneTimeSession();
    }

    /**
     * @test
     */
    public function destroyOneTimeSessionForRegularLoginAndOneTimeSessionDoesNotLogUserOut(): void
    {
        $this->frontEndUserAuthenticationMock->method('getKey')->with('user', 'onetimeaccount')->willReturn(null);
        $this->frontEndUserAuthenticationMock->method('getSessionData')->with('onetimeaccountUserUid')
            ->willReturn(5);

        $this->frontEndUserAuthenticationMock->expects(self::never())->method('logoff');

        $this->subject->destroyOneTimeSession();
    }

    /**
     * @test
     */
    public function destroyOneTimeSessionForRegularLoginAndOneTimeSessionRemovesUserUidFromSession(): void
    {
        $this->frontEndUserAuthenticationMock->method('getKey')->with('user', 'onetimeaccount')->willReturn(true);
        $this->frontEndUserAuthenticationMock->method('getSessionData')->with('onetimeaccountUserUid')
            ->willReturn(5);

        $this->frontEndUserAuthenticationMock->expects(self::once())->method('setAndSaveSessionData')
            ->with('onetimeaccountUserUid', null);

        $this->subject->destroyOneTimeSession();
    }
}
