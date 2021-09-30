<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\FrontEnd;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Session\FakeSession;
use OliverKlee\Oelib\Session\Session;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \Tx_Seminars_FrontEnd_RegistrationForm
 */
final class RegistrationFormTest extends UnitTestCase
{
    /**
     * @var int
     */
    private const PAGE_AFTER_REGISTRATION = 2;

    /**
     * @var int
     */
    private const PAGE_AFTER_UNREGISTRATION = 3;

    /**
     * @var array
     */
    private const CONFIGURATION = [
        'thankYouAfterRegistrationPID' => self::PAGE_AFTER_REGISTRATION,
        'pageToShowAfterUnregistrationPID' => self::PAGE_AFTER_UNREGISTRATION,
    ];

    /**
     * @var FakeSession
     */
    private $session = null;

    /**
     * @var ObjectProphecy
     */
    private $contentObjectProphecy = null;

    /**
     * @var ContentObjectRenderer
     */
    private $contentObject = null;

    /**
     * @var ObjectProphecy
     */
    private $userProphecy = null;

    /**
     * @var ObjectProphecy
     */
    private $eventProphecy = null;

    /**
     * @var LegacyEvent
     */
    private $event = null;

    protected function setUp(): void
    {
        $frontEndProphecy = $this->prophesize(TypoScriptFrontendController::class);
        /** @var TypoScriptFrontendController $frontEnd */
        $frontEnd = $frontEndProphecy->reveal();
        $GLOBALS['TSFE'] = $frontEnd;

        $this->contentObjectProphecy = $this->prophesize(ContentObjectRenderer::class);
        $this->contentObject = $this->contentObjectProphecy->reveal();
        $frontEnd->cObj = $this->contentObject;

        $this->userProphecy = $this->prophesize(FrontendUserAuthentication::class);
        /** @var FrontendUserAuthentication $user */
        $user = $this->userProphecy->reveal();
        $frontEnd->fe_user = $user;

        $this->session = new FakeSession();
        Session::setInstance(Session::TYPE_USER, $this->session);

        $this->eventProphecy = $this->prophesize(LegacyEvent::class);
        $this->event = $this->eventProphecy->reveal();
    }

    protected function tearDown(): void
    {
        Session::purgeInstances();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getThankYouAfterRegistrationUrlReturnsUrlStartingWithHttp(): void
    {
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(self::CONFIGURATION, $this->contentObject);

        $linkConfiguration = ['parameter' => self::PAGE_AFTER_REGISTRATION];
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->contentObjectProphecy->typoLink_URL($linkConfiguration)
            ->willReturn('/?id=' . self::PAGE_AFTER_REGISTRATION);

        $result = $subject->getThankYouAfterRegistrationUrl();

        self::assertRegExp('/^http:\\/\\/./', $result);
    }

    /**
     * @test
     */
    public function getThankYouAfterRegistrationUrlWithoutSendParametersNotContainsShowSeminarUid(): void
    {
        $configuration = self::CONFIGURATION;
        $configuration['sendParametersToThankYouAfterRegistrationPageUrl'] = false;
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm($configuration, $this->contentObject);

        $linkConfiguration = ['parameter' => self::PAGE_AFTER_REGISTRATION];
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->contentObjectProphecy->typoLink_URL($linkConfiguration)
            ->willReturn('/?id=' . self::PAGE_AFTER_REGISTRATION);

        $result = $subject->getThankYouAfterRegistrationUrl();

        self::assertStringNotContainsString('showUid', $result);
    }

    /**
     * @test
     */
    public function getThankYouAfterRegistrationUrlWithSendParametersContainsShowSeminarUid(): void
    {
        $configuration = self::CONFIGURATION;
        $configuration['sendParametersToThankYouAfterRegistrationPageUrl'] = true;
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm($configuration, $this->contentObject);

        $eventUid = 42;
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventProphecy->getUid()->willReturn($eventUid);
        $subject->setSeminar($this->event);

        $additionalParameters = '&tx_seminars_pi1%5BshowUid%5D=' . $eventUid;
        $linkConfiguration = [
            'parameter' => self::PAGE_AFTER_REGISTRATION,
            'additionalParams' => $additionalParameters,
        ];
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->contentObjectProphecy->typoLink_URL($linkConfiguration)
            ->willReturn('/?id=' . self::PAGE_AFTER_REGISTRATION . $additionalParameters);

        $result = $subject->getThankYouAfterRegistrationUrl();

        self::assertStringContainsString('showUid', $result);
        self::assertStringContainsString('=' . $eventUid, $result);
    }

    /**
     * @test
     */
    public function getThankYouAfterRegistrationUrlWithSendParametersEncodesBracketsInUrl(): void
    {
        $configuration = self::CONFIGURATION;
        $configuration['sendParametersToThankYouAfterRegistrationPageUrl'] = true;
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm($configuration, $this->contentObject);

        $eventUid = 42;
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventProphecy->getUid()->willReturn($eventUid);
        $subject->setSeminar($this->event);

        $additionalParameters = '&tx_seminars_pi1%5BshowUid%5D=' . $eventUid;
        $linkConfiguration = [
            'parameter' => self::PAGE_AFTER_REGISTRATION,
            'additionalParams' => $additionalParameters,
        ];
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->contentObjectProphecy->typoLink_URL($linkConfiguration)
            ->willReturn('/?id=' . self::PAGE_AFTER_REGISTRATION . $additionalParameters);

        $result = $subject->getThankYouAfterRegistrationUrl();

        self::assertStringContainsString('%5BshowUid%5D', $result);
        self::assertStringNotContainsString('[showUid]', $result);
    }

    /**
     * @test
     */
    public function getThankYouAfterRegistrationUrlWithoutOneTimeAccountAndLogOutEnabledNotLogsUserOff(): void
    {
        $configuration = self::CONFIGURATION;
        $configuration['logOutOneTimeAccountsAfterRegistration'] = true;
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm($configuration, $this->contentObject);

        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->userProphecy->logoff()->shouldNotBeCalled();

        $subject->getThankYouAfterRegistrationUrl();
    }

    /**
     * @test
     */
    public function getThankYouAfterRegistrationUrlWithOneTimeAccountAndLogOutDisabledNotLogsUserOff(): void
    {
        $this->session->setAsBoolean('onetimeaccount', true);

        $configuration = self::CONFIGURATION;
        $configuration['logOutOneTimeAccountsAfterRegistration'] = false;
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm($configuration, $this->contentObject);

        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->userProphecy->logoff()->shouldNotBeCalled();

        $subject->getThankYouAfterRegistrationUrl();
    }

    /**
     * @test
     */
    public function getThankYouAfterRegistrationUrlWithoutOneTimeAccountAndLogOutDisabledNotLogsUserOff(): void
    {
        $configuration = self::CONFIGURATION;
        $configuration['logOutOneTimeAccountsAfterRegistration'] = false;
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm($configuration, $this->contentObject);

        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->userProphecy->logoff()->shouldNotBeCalled();

        $subject->getThankYouAfterRegistrationUrl();
    }

    /**
     * @test
     */
    public function getThankYouAfterRegistrationUrlWithOneTimeAccountAndLogOutEnabledLogsUserOff(): void
    {
        $this->session->setAsBoolean('onetimeaccount', true);

        $configuration = self::CONFIGURATION;
        $configuration['logOutOneTimeAccountsAfterRegistration'] = true;
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm($configuration, $this->contentObject);

        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->userProphecy->logoff()->shouldBeCalled();

        $subject->getThankYouAfterRegistrationUrl();
    }

    /**
     * @test
     */
    public function getPageToShowAfterUnregistrationUrlReturnsUrlStartingWithHttp(): void
    {
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm(self::CONFIGURATION, $this->contentObject);

        $linkConfiguration = ['parameter' => self::PAGE_AFTER_UNREGISTRATION];
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->contentObjectProphecy->typoLink_URL($linkConfiguration)
            ->willReturn('/?id=' . self::PAGE_AFTER_UNREGISTRATION);

        $result = $subject->getPageToShowAfterUnregistrationUrl();

        self::assertRegExp('/^http:\\/\\/./', $result);
    }

    /**
     * @test
     */
    public function getPageToShowAfterUnregistrationUrlWithoutSendParametersNotContainsShowSeminarUid(): void
    {
        $configuration = self::CONFIGURATION;
        $configuration['sendParametersToPageToShowAfterUnregistrationUrl'] = false;
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm($configuration, $this->contentObject);

        $linkConfiguration = ['parameter' => self::PAGE_AFTER_UNREGISTRATION];
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->contentObjectProphecy->typoLink_URL($linkConfiguration)
            ->willReturn('/?id=' . self::PAGE_AFTER_UNREGISTRATION);

        $result = $subject->getPageToShowAfterUnregistrationUrl();

        self::assertStringNotContainsString('showUid', $result);
    }

    /**
     * @test
     */
    public function getPageToShowAfterUnregistrationUrlWithSendParametersContainsShowSeminarUid(): void
    {
        $configuration = self::CONFIGURATION;
        $configuration['sendParametersToPageToShowAfterUnregistrationUrl'] = true;
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm($configuration, $this->contentObject);

        $eventUid = 42;
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventProphecy->getUid()->willReturn($eventUid);
        $subject->setSeminar($this->event);

        $additionalParameters = '&tx_seminars_pi1%5BshowUid%5D=' . $eventUid;
        $linkConfiguration = [
            'parameter' => self::PAGE_AFTER_UNREGISTRATION,
            'additionalParams' => $additionalParameters,
        ];
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->contentObjectProphecy->typoLink_URL($linkConfiguration)
            ->willReturn('/?id=' . self::PAGE_AFTER_UNREGISTRATION . $additionalParameters);

        $result = $subject->getPageToShowAfterUnregistrationUrl();

        self::assertStringContainsString('showUid', $result);
        self::assertStringContainsString('=' . $eventUid, $result);
    }

    /**
     * @test
     */
    public function getPageToShowAfterUnregistrationUrlWithSendParametersEncodesBracketsInUrl(): void
    {
        $configuration = self::CONFIGURATION;
        $configuration['sendParametersToPageToShowAfterUnregistrationUrl'] = true;
        $subject = new \Tx_Seminars_FrontEnd_RegistrationForm($configuration, $this->contentObject);

        $eventUid = 42;
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->eventProphecy->getUid()->willReturn($eventUid);
        $subject->setSeminar($this->event);

        $additionalParameters = '&tx_seminars_pi1%5BshowUid%5D=' . $eventUid;
        $linkConfiguration = [
            'parameter' => self::PAGE_AFTER_UNREGISTRATION,
            'additionalParams' => $additionalParameters,
        ];
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->contentObjectProphecy->typoLink_URL($linkConfiguration)
            ->willReturn('/?id=' . self::PAGE_AFTER_UNREGISTRATION . $additionalParameters);

        $result = $subject->getPageToShowAfterUnregistrationUrl();

        self::assertStringContainsString('%5BshowUid%5D', $result);
        self::assertStringNotContainsString('[showUid]', $result);
    }
}
