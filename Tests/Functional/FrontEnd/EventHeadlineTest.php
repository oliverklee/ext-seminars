<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\System\Typo3Version;
use OliverKlee\Seminars\FrontEnd\EventHeadline;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Service\RegistrationManager;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\EventHeadline
 */
final class EventHeadlineTest extends FunctionalTestCase
{
    /**
     * @var string
     */
    private const DATE_FORMAT = '%d.%m.%Y';

    /**
     * @var array{templateFile: non-empty-string}
     */
    private const CONFIGURATION = ['templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'];

    /**
     * @var array<int, non-empty-string>
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var EventHeadline
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $con = new DummyConfiguration(['dateFormatYMD' => self::DATE_FORMAT]);
        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin.tx_seminars', $con);

        /** @var ObjectProphecy<TypoScriptFrontendController> $frontEndProphecy */
        $frontEndProphecy = $this->prophesize(TypoScriptFrontendController::class);
        if (Typo3Version::isAtLeast(10)) {
            $siteLanguage = new SiteLanguage(0, 'en_US.UTF-8', new Uri('/'), []);
            $frontEndProphecy->getLanguage()->wilLReturn($siteLanguage);
        }
        $frontEnd = $frontEndProphecy->reveal();
        $GLOBALS['TSFE'] = $frontEnd;

        $contentObject = new ContentObjectRenderer();
        $contentObject->setLogger(new NullLogger());
        $this->subject = new EventHeadline(self::CONFIGURATION, $contentObject);

        $mapper = new EventMapper();
        $this->subject->injectEventMapper($mapper);
    }

    protected function tearDown(): void
    {
        RegistrationManager::purgeInstance();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function renderWithoutInjectedEventMapperThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionCode(1333614794);

        $contentObject = new ContentObjectRenderer();
        $contentObject->setLogger(new NullLogger());
        $subject = new EventHeadline([], $contentObject);

        $subject->render();
    }

    /**
     * @test
     */
    public function renderWithUidOfExistingEventReturnsTitleOfSelectedEvent(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventHeadline.xml');
        $this->subject->piVars['showUid'] = '1';

        $result = $this->subject->render();

        self::assertStringContainsString('Code sprint', $result);
    }

    /**
     * @test
     */
    public function renderEncodesEventTitle(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/EventHeadline.xml');
        $this->subject->piVars['showUid'] = '2';

        $result = $this->subject->render();

        self::assertStringContainsString('Food &amp; drink', $result);
    }

    /**
     * @test
     */
    public function renderWithUidOfExistingEventReturnsDateOfSelectedEvent(): void
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
    public function renderWithoutProvidedEventUidReturnsEmptyString(): void
    {
        $result = $this->subject->render();

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function renderWithInexistentUidReturnsEmptyString(): void
    {
        $this->subject->piVars['showUid'] = '1';

        $result = $this->subject->render();

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function renderWithNonNumericUidReturnsEmptyString(): void
    {
        $this->subject->piVars['showUid'] = 'foo';

        $result = $this->subject->render();

        self::assertSame('', $result);
    }
}
