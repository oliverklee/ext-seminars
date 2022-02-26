<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\FrontEnd;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Session\FakeSession;
use OliverKlee\Oelib\Session\Session;
use OliverKlee\Oelib\System\Typo3Version;
use OliverKlee\Seminars\FrontEnd\RegistrationForm;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\RegistrationForm
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
     * @var ObjectProphecy<ContentObjectRenderer>
     */
    private $contentObjectProphecy = null;

    /**
     * @var ContentObjectRenderer
     */
    private $contentObject = null;

    /**
     * @var ObjectProphecy<FrontendUserAuthentication>
     */
    private $userProphecy = null;

    /**
     * @var ObjectProphecy<LegacyEvent>
     */
    private $eventProphecy = null;

    /**
     * @var LegacyEvent
     */
    private $event = null;

    protected function setUp(): void
    {
        /** @var ObjectProphecy<TypoScriptFrontendController> $frontEndProphecy */
        $frontEndProphecy = $this->prophesize(TypoScriptFrontendController::class);
        if (Typo3Version::isAtLeast(10)) {
            $siteLanguage = new SiteLanguage(0, 'en_US.UTF-8', new Uri('/'), []);
            $frontEndProphecy->getLanguage()->wilLReturn($siteLanguage);
        }

        $frontEnd = $frontEndProphecy->reveal();
        $GLOBALS['TSFE'] = $frontEnd;

        $this->contentObjectProphecy = $this->prophesize(ContentObjectRenderer::class);
        $this->contentObject = $this->contentObjectProphecy->reveal();
        $frontEnd->cObj = $this->contentObject;

        $this->userProphecy = $this->prophesize(FrontendUserAuthentication::class);
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
        $subject = new RegistrationForm(self::CONFIGURATION, $this->contentObject);

        $linkConfiguration = ['parameter' => self::PAGE_AFTER_REGISTRATION];
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
        $subject = new RegistrationForm($configuration, $this->contentObject);

        $linkConfiguration = ['parameter' => self::PAGE_AFTER_REGISTRATION];
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
        $subject = new RegistrationForm($configuration, $this->contentObject);

        $eventUid = 42;
        $this->eventProphecy->getUid()->willReturn($eventUid);
        $subject->setSeminar($this->event);

        $additionalParameters = '&tx_seminars_pi1%5BshowUid%5D=' . $eventUid;
        $linkConfiguration = [
            'parameter' => self::PAGE_AFTER_REGISTRATION,
            'additionalParams' => $additionalParameters,
        ];
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
        $subject = new RegistrationForm($configuration, $this->contentObject);

        $eventUid = 42;
        $this->eventProphecy->getUid()->willReturn($eventUid);
        $subject->setSeminar($this->event);

        $additionalParameters = '&tx_seminars_pi1%5BshowUid%5D=' . $eventUid;
        $linkConfiguration = [
            'parameter' => self::PAGE_AFTER_REGISTRATION,
            'additionalParams' => $additionalParameters,
        ];
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
        $subject = new RegistrationForm($configuration, $this->contentObject);

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
        $subject = new RegistrationForm($configuration, $this->contentObject);

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
        $subject = new RegistrationForm($configuration, $this->contentObject);

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
        $subject = new RegistrationForm($configuration, $this->contentObject);

        $this->userProphecy->logoff()->shouldBeCalled();

        $subject->getThankYouAfterRegistrationUrl();
    }

    /**
     * @test
     */
    public function getPageToShowAfterUnregistrationUrlReturnsUrlStartingWithHttp(): void
    {
        $subject = new RegistrationForm(self::CONFIGURATION, $this->contentObject);

        $linkConfiguration = ['parameter' => self::PAGE_AFTER_UNREGISTRATION];
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
        $subject = new RegistrationForm($configuration, $this->contentObject);

        $linkConfiguration = ['parameter' => self::PAGE_AFTER_UNREGISTRATION];
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
        $subject = new RegistrationForm($configuration, $this->contentObject);

        $eventUid = 42;
        $this->eventProphecy->getUid()->willReturn($eventUid);
        $subject->setSeminar($this->event);

        $additionalParameters = '&tx_seminars_pi1%5BshowUid%5D=' . $eventUid;
        $linkConfiguration = [
            'parameter' => self::PAGE_AFTER_UNREGISTRATION,
            'additionalParams' => $additionalParameters,
        ];
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
        $subject = new RegistrationForm($configuration, $this->contentObject);

        $eventUid = 42;
        $this->eventProphecy->getUid()->willReturn($eventUid);
        $subject->setSeminar($this->event);

        $additionalParameters = '&tx_seminars_pi1%5BshowUid%5D=' . $eventUid;
        $linkConfiguration = [
            'parameter' => self::PAGE_AFTER_UNREGISTRATION,
            'additionalParams' => $additionalParameters,
        ];
        $this->contentObjectProphecy->typoLink_URL($linkConfiguration)
            ->willReturn('/?id=' . self::PAGE_AFTER_UNREGISTRATION . $additionalParameters);

        $result = $subject->getPageToShowAfterUnregistrationUrl();

        self::assertStringContainsString('%5BshowUid%5D', $result);
        self::assertStringNotContainsString('[showUid]', $result);
    }
}
