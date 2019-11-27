<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BackEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\BackEnd\GeneralEventMailForm;
use OliverKlee\Seminars\Tests\Functional\BackEnd\Fixtures\TestingHookImplementor;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class GeneralEventMailFormTest extends FunctionalTestCase
{
    use LanguageHelper;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Oelib_EmailCollector
     */
    private $mailer = null;

    protected function setUp()
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'] = [];
        $this->setUpBackendUserFromFixture(1);
        $this->initializeBackEndLanguage();

        /** @var \Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(\Tx_Oelib_MailerFactory::class);
        $mailerFactory->enableTestMode();
        $this->mailer = $mailerFactory->getMailer();
    }

    protected function tearDown()
    {
        GeneralUtility::purgeInstances();
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule']);

        parent::tearDown();
    }

    /**
     * @test
     */
    public function sendEmailCallsHookWithRegistration()
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
        $subject->render();

        self::assertSame(1, $hook->getCountCallForGeneralEmail());
    }

    /**
     * @test
     */
    public function sendEmailForTwoRegistrationsCallsHookTwice()
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
        $subject->render();

        self::assertSame(2, $hook->getCountCallForGeneralEmail());
    }

    /**
     * @test
     */
    public function sendEmailSendsEmailWithNameOfRegisteredUserInSalutationMarker()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $subject = new GeneralEventMailForm(1);

        $messageBody = '%salutation' . $this->getLanguageService()->getLL('cancelMailForm_prefillField_messageBody');
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
}
