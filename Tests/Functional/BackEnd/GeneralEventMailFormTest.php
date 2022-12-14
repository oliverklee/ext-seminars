<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BackEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\BackEnd\GeneralEventMailForm;
use OliverKlee\Seminars\Tests\Functional\BackEnd\Fixtures\TestingHookImplementor;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use OliverKlee\Seminars\Tests\Unit\Traits\EmailTrait;
use OliverKlee\Seminars\Tests\Unit\Traits\MakeInstanceTrait;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Seminars\BackEnd\GeneralEventMailForm
 */
final class GeneralEventMailFormTest extends FunctionalTestCase
{
    use LanguageHelper;
    use EmailTrait;
    use MakeInstanceTrait;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if ((new Typo3Version())->getMajorVersion() >= 11) {
            self::markTestSkipped('Skipping because this code will be removed before adding 11LTS compatibility.');
        }

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'] = [];
        $this->setUpBackendUserFromFixture(1);
        $this->initializeBackEndLanguage();

        $this->email = $this->createEmailMock();
    }

    protected function tearDown(): void
    {
        // Manually purge the TYPO3 FIFO queue
        GeneralUtility::makeInstance(MailMessage::class);
        GeneralUtility::makeInstance(MailMessage::class);
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule']);

        parent::tearDown();
    }

    /**
     * @test
     */
    public function sendEmailCallsHookWithRegistration(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $hook = GeneralUtility::makeInstance(TestingHookImplementor::class);
        $hookClassName = TestingHookImplementor::class;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'][$hookClassName] = $hookClassName;

        $subject = new GeneralEventMailForm(1);

        $subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'some message body',
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $subject->render();

        self::assertSame(1, $hook->getCountCallForGeneralEmail());
    }

    /**
     * @test
     */
    public function sendEmailForTwoRegistrationsCallsHookTwice(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $hook = GeneralUtility::makeInstance(TestingHookImplementor::class);
        $hookClassName = TestingHookImplementor::class;
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'][$hookClassName] = $hookClassName;

        $subject = new GeneralEventMailForm(2);

        $subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'some message body',
            ]
        );

        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);
        $this->addMockedInstance(MailMessage::class, $this->email);

        $subject->render();

        self::assertSame(2, $hook->getCountCallForGeneralEmail());
    }

    /**
     * @test
     */
    public function sendEmailSendsEmailWithNameOfRegisteredUserInSalutationMarker(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $subject = new GeneralEventMailForm(1);

        $messageBody = '%salutation' . $this->translate('cancelMailForm_prefillField_messageBody');
        $subject->setPostData(
            [
                'action' => 'sendEmail',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => $messageBody,
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $subject->render();

        self::assertStringContainsString('Joe Johnson', $this->email->getTextBody());
    }
}
