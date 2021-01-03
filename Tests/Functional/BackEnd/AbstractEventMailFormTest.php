<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BackEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Oelib\Email\EmailCollector;
use OliverKlee\Oelib\Email\MailerFactory;
use OliverKlee\Oelib\Http\HeaderCollector;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Seminars\BackEnd\AbstractEventMailForm;
use OliverKlee\Seminars\Tests\Functional\BackEnd\Fixtures\TestingEventMailForm;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class AbstractEventMailFormTest extends FunctionalTestCase
{
    use LanguageHelper;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var EmailCollector
     */
    private $mailer = null;

    /**
     * @var HeaderCollector
     */
    private $headerProxy = null;

    /**
     * @var string[][]
     */
    protected $configurationToUseInTestInstance = [
        'MAIL' => [
            'defaultMailFromAddress' => 'system-foo@example.com',
            'defaultMailFromName' => 'Mr. Default',
        ],
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->setUpBackendUserFromFixture(1);
        $this->initializeBackEndLanguage();

        /** @var MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(MailerFactory::class);
        $mailerFactory->enableTestMode();
        $this->mailer = $mailerFactory->getMailer();

        $headerProxyFactory = HeaderProxyFactory::getInstance();
        $headerProxyFactory->enableTestMode();
        $this->headerProxy = $headerProxyFactory->getHeaderProxy();
    }

    protected function tearDown()
    {
        GeneralUtility::purgeInstances();

        parent::tearDown();
    }

    /**
     * Returns the URL to a given module.
     *
     * @param string $moduleName name of the module
     * @param array $urlParameters URL parameters that should be added as key-value pairs
     *
     * @return string calculated URL
     */
    private function getRouteUrl(string $moduleName, array $urlParameters = []): string
    {
        $uriBuilder = $this->getUriBuilder();
        try {
            $uri = $uriBuilder->buildUriFromRoute($moduleName, $urlParameters);
        } catch (\TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException $e) {
            // no route registered, use the fallback logic to check for a module
            $uri = $uriBuilder->buildUriFromModule($moduleName, $urlParameters);
        }

        return (string)$uri;
    }

    private function getUriBuilder(): UriBuilder
    {
        return GeneralUtility::makeInstance(UriBuilder::class);
    }

    /**
     * @test
     */
    public function sendEmailForTwoRegistrationsSendsTwoEmails()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $subject = new TestingEventMailForm(2);

        $subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'some message body',
            ]
        );
        $subject->render();

        self::assertSame(2, $this->mailer->getNumberOfSentEmails());
    }

    /**
     * @test
     */
    public function sendEmailSendsEmailWithNameOfRegisteredUserInSalutationMarker()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $subject = new TestingEventMailForm(1);

        $messageBody = '%salutation';
        $subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => $messageBody,
            ]
        );
        $subject->render();

        self::assertContains('Joe Johnson', $this->mailer->getFirstSentEmail()->getBody());
    }

    /**
     * @test
     */
    public function sendEmailUsesTypo3DefaultFromAddressAsSender()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $subject = new TestingEventMailForm(2);

        $subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'Hello!',
            ]
        );
        $subject->render();

        self::assertArrayHasKey('system-foo@example.com', $this->mailer->getFirstSentEmail()->getFrom());
    }

    /**
     * @test
     */
    public function sendEmailUsesFirstOrganizerAsSender()
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL'] = [];

        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $subject = new TestingEventMailForm(2);

        $subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'Hello!',
            ]
        );
        $subject->render();

        self::assertArrayHasKey('oliver@example.com', $this->mailer->getFirstSentEmail()->getFrom());
    }

    /**
     * @test
     */
    public function sendEmailUsesFirstOrganizerAsReplyTo()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $subject = new TestingEventMailForm(2);

        $subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'Hello!',
            ]
        );
        $subject->render();

        self::assertArrayHasKey('oliver@example.com', $this->mailer->getFirstSentEmail()->getReplyTo());
    }

    /**
     * @test
     */
    public function sendEmailAppendsFirstOrganizerFooterToMessageBody()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $subject = new TestingEventMailForm(2);

        $subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'Hello!',
            ]
        );
        $subject->render();

        self::assertContains("\n-- \nThe one and only", $this->mailer->getFirstSentEmail()->getBody());
    }

    /**
     * @test
     */
    public function sendEmailUsesProvidedEmailSubject()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $emailSubject = 'Thank you for your registration.';
        $subject = new TestingEventMailForm(1);
        $subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => $emailSubject,
                'messageBody' => 'Hello!',
            ]
        );
        $subject->render();

        self::assertSame($emailSubject, $this->mailer->getFirstSentEmail()->getSubject());
    }

    /**
     * @test
     */
    public function sendEmailNotSendsEmailToUserWithoutEmailAddress()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $subject = new TestingEventMailForm(4);
        $subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'Hello!',
                'messageBody' => 'Hello!',
            ]
        );
        $subject->render();

        self::assertNull($this->mailer->getFirstSentEmail());
    }

    /**
     * @test
     */
    public function redirectsToListViewAfterSendingEmail()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $pageUid = 3;
        PageFinder::getInstance()->setPageUid($pageUid);

        $subject = new TestingEventMailForm(4);
        $subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'Hello!',
                'messageBody' => 'Hello!',
            ]
        );
        $subject->render();

        $url = $this->getRouteUrl(AbstractEventMailForm::MODULE_NAME, ['id' => $pageUid]);
        self::assertSame('Location: ' . $url, $this->headerProxy->getLastAddedHeader());
    }
}
