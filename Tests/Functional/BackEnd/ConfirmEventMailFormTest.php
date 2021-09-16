<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BackEnd;

use Doctrine\DBAL\Driver\Statement;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\BackEnd\ConfirmEventMailForm;
use OliverKlee\Seminars\Tests\Functional\BackEnd\Fixtures\TestingHookImplementor;
use OliverKlee\Seminars\Tests\Unit\Traits\EmailTrait;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use OliverKlee\Seminars\Tests\Unit\Traits\MakeInstanceTrait;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ConfirmEventMailFormTest extends FunctionalTestCase
{
    use LanguageHelper;

    use EmailTrait;

    use MakeInstanceTrait;

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var MockObject|MailMessage|null
     */
    private $email = null;

    protected function setUp()
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['backEndModule'] = [];
        $this->setUpBackendUserFromFixture(1);
        $this->initializeBackEndLanguage();

        $this->email = $this->createEmailMock();
        GeneralUtility::addInstance(MailMessage::class, $this->email);
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
    public function sendEmailSetsEventStatusToConfirmed()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $subject = new ConfirmEventMailForm(3);

        $subject->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'some message body',
            ]
        );
        $subject->render();

        /** @var Statement $statement */
        $statement = $this->getDatabaseConnection()->select('cancelled', 'tx_seminars_seminars', 'uid = 3');
        self::assertSame(\Tx_Seminars_Model_Event::STATUS_CONFIRMED, $statement->fetchColumn(0));
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

        $subject = new ConfirmEventMailForm(1);

        $subject->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'some message body',
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $subject->render();

        self::assertSame(1, $hook->getCountCallForConfirmEmail());
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

        $subject = new ConfirmEventMailForm(2);

        $subject->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => 'some message body',
            ]
        );

        $this->email->expects(self::exactly(2))->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $subject->render();

        self::assertSame(2, $hook->getCountCallForConfirmEmail());
    }

    /**
     * @test
     */
    public function sendEmailSendsEmailWithNameOfRegisteredUserInSalutationMarker()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $subject = new ConfirmEventMailForm(1);

        $messageBody = '%salutation' . $this->getLanguageService()->getLL('cancelMailForm_prefillField_messageBody');
        $subject->setPostData(
            [
                'action' => 'confirmEvent',
                'isSubmitted' => '1',
                'subject' => 'foo',
                'messageBody' => $messageBody,
            ]
        );

        $this->email->expects(self::once())->method('send');
        $this->addMockedInstance(MailMessage::class, $this->email);

        $subject->render();

        self::assertStringContainsString('Joe Johnson', $this->email->getBody());
    }
}
