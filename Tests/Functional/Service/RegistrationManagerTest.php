<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Service;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Email\EmailCollector;
use OliverKlee\Seminars\Tests\Unit\Traits\LanguageHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \Tx_Seminars_Service_RegistrationManager
 */
final class RegistrationManagerTest extends FunctionalTestCase
{
    use LanguageHelper;

    /**
     * @var string
     */
    const EMAIL_TEMPLATE_PATH = 'EXT:seminars/Resources/Private/Templates/Mail/e-mail.html';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Seminars_Service_RegistrationManager
     */
    private $subject = null;

    /**
     * @var EmailCollector
     */
    private $mailer = null;

    protected function setUp()
    {
        parent::setUp();

        $this->initializeBackEndLanguage();

        /** @var \Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(\Tx_Oelib_MailerFactory::class);
        $mailerFactory->enableTestMode();
        /** @var EmailCollector $mailer */
        $mailer = $mailerFactory->getMailer();
        $this->mailer = $mailer;

        $this->subject = \Tx_Seminars_Service_RegistrationManager::getInstance();
    }

    protected function tearDown()
    {
        \Tx_Seminars_Service_RegistrationManager::purgeCachedConfigurations();
        \Tx_Seminars_Service_RegistrationManager::purgeInstance();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function notifyOrganizersForEventWithOneVacancyShowsVacanciesLabelWithVacancyNumber()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationManagerRecords.xml');
        $registration = \Tx_Seminars_OldModel_Registration::fromUid(1);

        $this->subject->setConfigurationValue('sendNotification', true);
        $this->subject->setConfigurationValue('showSeminarFieldsInNotificationMail', 'vacancies');
        $this->subject->setConfigurationValue('templateFile', self::EMAIL_TEMPLATE_PATH);

        $this->subject->notifyOrganizers($registration);

        $expectedExpression = '/' . $this->getLanguageService()->getLL('label_vacancies') . ': 1$/';
        self::assertRegExp($expectedExpression, $this->mailer->getFirstSentEmail()->getBody());
    }
}
