<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BackEnd;

use Doctrine\DBAL\Driver\Statement;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Email\EmailCollector;
use OliverKlee\Oelib\Email\MailerFactory;
use OliverKlee\Seminars\BackEnd\CancelEventMailForm;
use OliverKlee\Seminars\Tests\Functional\BackEnd\Fixtures\TestingHookImplementor;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class CancelEventMailFormTest extends FunctionalTestCase
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

    protected function setUp()
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'] = [];
        $this->setUpBackendUserFromFixture(1);
        $this->initializeBackEndLanguage();

        /** @var MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(MailerFactory::class);
        $mailerFactory->enableTestMode();
        /** @var EmailCollector $mailer */
        $mailer = $mailerFactory->getMailer();
        $this->mailer = $mailer;
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
    public function sendEmailSetsEventStatusToCanceled()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $subject = new CancelEventMailForm(3);

        $subject->setPostData(
            [
                'action' => 'cancelEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'some message body',
            ]
        );
        $subject->render();

        /** @var Statement $statement */
        $statement = $this->getDatabaseConnection()->select('cancelled', 'tx_seminars_seminars', 'uid = 3');
        self::assertSame(\Tx_Seminars_Model_Event::STATUS_CANCELED, $statement->fetchColumn(0));
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

        $subject = new CancelEventMailForm(1);

        $subject->setPostData(
            [
                'action' => 'cancelEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'some message body',
            ]
        );
        $subject->render();

        self::assertSame(1, $hook->getCountCallForCancelEmail());
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

        $subject = new CancelEventMailForm(2);

        $subject->setPostData(
            [
                'action' => 'cancelEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'some message body',
            ]
        );
        $subject->render();

        self::assertSame(2, $hook->getCountCallForCancelEmail());
    }

    /**
     * @test
     */
    public function sendEmailSendsEmailWithNameOfRegisteredUserInSalutationMarker()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $subject = new CancelEventMailForm(1);

        $messageBody = '%salutation' . $this->getLanguageService()->getLL('cancelMailForm_prefillField_messageBody');
        $subject->setPostData(
            [
                'action' => 'cancelEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => $messageBody,
            ]
        );
        $subject->render();

        self::assertStringContainsString('Joe Johnson', $this->mailer->getFirstSentEmail()->getBody());
    }
}
