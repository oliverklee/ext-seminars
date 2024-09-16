<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Service;

use OliverKlee\Seminars\Service\OneTimeAccountConnector;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Session\UserSession;
use TYPO3\CMS\Core\Session\UserSessionManager;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

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

    /**
     * @var UserSessionManager&MockObject
     */
    private $userSessionManagerMock;

    /**
     * @var UserSession&MockObject
     */
    private $userSessionMock;

    protected function setUp(): void
    {
        parent::setUp();

        $mockFrontEndController = $this->createMock(TypoScriptFrontendController::class);
        $this->frontEndUserAuthenticationMock = $this->createMock(FrontendUserAuthentication::class);
        $mockFrontEndController->fe_user = $this->frontEndUserAuthenticationMock;
        $GLOBALS['TSFE'] = $mockFrontEndController;

        $this->setUpSession();

        $this->subject = new OneTimeAccountConnector();
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();

        unset($GLOBALS['TSFE']);
        parent::tearDown();
    }

    private function setUpSession(): void {
        $this->userSessionMock = $this->createMock(UserSession::class);
        $this->userSessionManagerMock = $this->createMock(UserSessionManager::class);
        $this->userSessionManagerMock->method('createAnonymousSession')->willReturn($this->userSessionMock);
        $this->userSessionManagerMock->method('createFromRequestOrAnonymous')->willReturn($this->userSessionMock);
        GeneralUtility::addInstance(UserSessionManager::class, $this->userSessionManagerMock);
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
    public function getOneTimeAccountUserUidForNullUserUidReturnsNull(): void
    {
        $this->frontEndUserAuthenticationMock->method('getSessionData')->with('onetimeaccountUserUid')
            ->willReturn(null);
        $this->userSessionMock->method('get')->with('onetimeaccountUserUid')->willReturn(null);

        self::assertNull($this->subject->getOneTimeAccountUserUid());
    }

    /**
     * @test
     */
    public function getOneTimeAccountUserUidForEmptyUserUidReturnsNull(): void
    {
        $this->frontEndUserAuthenticationMock->method('getSessionData')->with('onetimeaccountUserUid')->willReturn('');
        $this->userSessionMock->method('get')->with('onetimeaccountUserUid')->willReturn('');

        self::assertNull($this->subject->getOneTimeAccountUserUid());
    }

    /**
     * @test
     */
    public function getOneTimeAccountUserUidForZeroUserUidReturnsNull(): void
    {
        $this->frontEndUserAuthenticationMock->method('getSessionData')->with('onetimeaccountUserUid')->willReturn(0);
        $this->userSessionMock->method('get')->with('onetimeaccountUserUid')->willReturn(0);

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
        $this->userSessionMock->method('get')->with('onetimeaccountUserUid')->willReturn($userUid);

        self::assertSame($userUid, $this->subject->getOneTimeAccountUserUid());
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
