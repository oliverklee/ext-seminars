<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BackEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Oelib\Http\HeaderCollector;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Oelib\System\Typo3Version;
use OliverKlee\Seminars\BackEnd\AbstractEventMailForm;
use OliverKlee\Seminars\Tests\Functional\BackEnd\Fixtures\TestingEventMailForm;
use OliverKlee\Seminars\Tests\Unit\Traits\EmailTrait;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use OliverKlee\Seminars\Tests\Unit\Traits\MakeInstanceTrait;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class AbstractEventMailFormTest extends FunctionalTestCase
{
    use LanguageHelper;

    use EmailTrait;

    use MakeInstanceTrait;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

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

        $this->email = $this->createEmailMock();

        $headerProxyFactory = HeaderProxyFactory::getInstance();
        $headerProxyFactory->enableTestMode();
        $this->headerProxy = $headerProxyFactory->getHeaderCollector();
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
        if (Typo3Version::isNotHigherThan(8)) {
            try {
                $uri = $uriBuilder->buildUriFromRoute($moduleName, $urlParameters);
            } catch (\TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException $e) {
                // no route registered, use the fallback logic to check for a module
                $uri = $uriBuilder->buildUriFromModule($moduleName, $urlParameters);
            }
        } else {
            $uri = $uriBuilder->buildUriFromRoute($moduleName, $urlParameters);
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

        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);

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
    }

    /**
     * @test
     */
    public function sendEmailSendsEmailWithNameOfRegisteredUserInSalutationMarker()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

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

        self::assertStringContainsString('Joe Johnson', $this->email->getBody());
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

        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);
        $subject->render();

        self::assertArrayHasKey('system-foo@example.com', $this->email->getFrom());
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
        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);
        $subject->render();

        self::assertArrayHasKey('oliver@example.com', $this->email->getFrom());
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
        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);
        $subject->render();

        self::assertArrayHasKey('oliver@example.com', $this->email->getReplyTo());
    }

    /**
     * @test
     */
    public function sendEmailAppendsFirstOrganizerFooterToMessageBody()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);

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

        self::assertStringContainsString("\n-- \nThe one and only", $this->email->getBody());
    }

    /**
     * @test
     */
    public function sendEmailUsesProvidedEmailSubject()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

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

        self::assertSame($emailSubject, $this->email->getSubject());
    }

    /**
     * @test
     */
    public function sendEmailNotSendsEmailToUserWithoutEmailAddress()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $this->email->expects(self::never())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

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
