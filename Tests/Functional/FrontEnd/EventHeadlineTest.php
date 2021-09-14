<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\Configuration;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class EventHeadlineTest extends FunctionalTestCase
{
    /**
     * @var string
     */
    const DATE_FORMAT = '%d.%m.%Y';

    /**
     * @var string[]
     */
    const CONFIGURATION = ['templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'];

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var \Tx_Seminars_FrontEnd_EventHeadline
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $pluginConfiguration = new Configuration();
        $pluginConfiguration->setAsString('dateFormatYMD', self::DATE_FORMAT);
        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin.tx_seminars', $pluginConfiguration);
        $GLOBALS['TSFE'] = $this->prophesize(TypoScriptFrontendController::class)->reveal();

        $this->subject = new \Tx_Seminars_FrontEnd_EventHeadline(self::CONFIGURATION, new ContentObjectRenderer());

        $mapper = new \Tx_Seminars_Mapper_Event();
        $this->subject->injectEventMapper($mapper);
    }

    protected function tearDown()
    {
        \Tx_Seminars_Service_RegistrationManager::purgeInstance();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function renderWithoutInjectedEventMapperThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionCode(1333614794);

        $subject = new \Tx_Seminars_FrontEnd_EventHeadline([], new ContentObjectRenderer());

        $subject->render();
    }

    /**
     * @test
     */
    public function renderWithUidOfExistingEventReturnsTitleOfSelectedEvent()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventHeadline.xml');
        $this->subject->piVars['showUid'] = '1';

        $result = $this->subject->render();

        self::assertContains('Code sprint', $result);
    }

    /**
     * @test
     */
    public function renderEncodesEventTitle()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventHeadline.xml');
        $this->subject->piVars['showUid'] = '2';

        $result = $this->subject->render();

        self::assertContains('Food &amp; drink', $result);
    }

    /**
     * @test
     */
    public function renderWithUidOfExistingEventReturnsDateOfSelectedEvent()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventHeadline.xml');
        $this->subject->piVars['showUid'] = '1';

        $result = $this->subject->render();

        $expectedDate = \strftime(self::DATE_FORMAT, 978303600);
        self::assertContains($expectedDate, $result);
    }

    /**
     * @test
     */
    public function renderWithoutProvidedEventUidReturnsEmptyString()
    {
        $result = $this->subject->render();

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function renderWithInexistentUidReturnsEmptyString()
    {
        $this->subject->piVars['showUid'] = '1';

        $result = $this->subject->render();

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function renderWithNonNumericUidReturnsEmptyString()
    {
        $this->subject->piVars['showUid'] = 'foo';

        $result = $this->subject->render();

        self::assertSame('', $result);
    }
}
