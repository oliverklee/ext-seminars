<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\FrontEnd\RegistrationForm;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\Service\RegistrationManager;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\AbstractEditor
 * @covers \OliverKlee\Seminars\FrontEnd\RegistrationForm
 */
final class RegistrationFormTest extends TestCase
{
    use LanguageHelper;

    /**
     * @var RegistrationForm
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var int the UID of the event the fixture relates to
     */
    private $seminarUid = 0;

    /**
     * @var LegacyEvent
     */
    private $seminar;

    protected function setUp(): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 11) {
            self::markTestSkipped('Skipping because this code will be removed before adding 11LTS compatibility.');
        }

        $this->testingFramework = new TestingFramework('tx_seminars');
        $rootPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->changeRecord('pages', $rootPageUid, ['slug' => '/home']);
        $this->testingFramework->createFakeFrontEnd($rootPageUid);

        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configuration = new DummyConfiguration();
        $configuration->setAsString('currency', 'EUR');
        $configurationRegistry->set('plugin.tx_seminars', $configuration);
        $infoTablesConfiguration = new DummyConfiguration();
        $configurationRegistry->set('plugin.tx_staticinfotables_pi1', $infoTablesConfiguration);

        $this->seminar = new LegacyEvent(
            $this->testingFramework->createRecord(
                'tx_seminars_seminars',
                ['payment_methods' => '1']
            )
        );
        $this->seminarUid = $this->seminar->getUid();

        $this->subject = new RegistrationForm(
            [
                'pageToShowAfterUnregistrationPID' => $rootPageUid,
                'sendParametersToThankYouAfterRegistrationPageUrl' => 1,
                'thankYouAfterRegistrationPID' => $rootPageUid,
                'sendParametersToPageToShowAfterUnregistrationUrl' => 1,
                'templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html',
                'form.' => [
                    'unregistration.' => [],
                    'registration.' => [
                        'step1.' => [],
                        'step2.' => [],
                    ],
                ],
            ],
            $this->getFrontEndController()->cObj
        );
        $this->subject->setAction('register');
        $this->subject->setSeminar($this->seminar);
        $this->subject->setTestMode();
    }

    protected function tearDown(): void
    {
        if ($this->testingFramework instanceof TestingFramework) {
            $this->testingFramework->cleanUp();
        }

        RegistrationManager::purgeInstance();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    // Tests concerning getSeminar and getEvent

    /**
     * @test
     */
    public function getSeminarReturnsSeminarFromSetSeminar(): void
    {
        self::assertSame(
            $this->seminar,
            $this->subject->getSeminar()
        );
    }

    /**
     * @test
     */
    public function getEventReturnsEventWithSeminarUid(): void
    {
        $event = $this->subject->getEvent();
        self::assertInstanceOf(
            Event::class,
            $event
        );

        self::assertSame(
            $this->seminarUid,
            $event->getUid()
        );
    }
}
