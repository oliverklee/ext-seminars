<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BackEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Seminars\BackEnd\AbstractList;
use OliverKlee\Seminars\BackEnd\EventsList;
use OliverKlee\Seminars\Tests\Functional\Traits\LanguageHelper;
use OliverKlee\Seminars\Tests\LegacyUnit\BackEnd\Fixtures\DummyModule;
use TYPO3\CMS\Backend\Template\DocumentTemplate;

/**
 * @covers \OliverKlee\Seminars\BackEnd\EventsList
 */
final class EventsListTest extends FunctionalTestCase
{
    use LanguageHelper;

    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var EventsList
     */
    private $subject;

    /**
     * @var DummyModule
     */
    private $backEndModule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpBackendUserFromFixture(1);
        $this->initializeBackEndLanguage();

        $this->backEndModule = new DummyModule();
        $this->backEndModule->doc = new DocumentTemplate();

        $this->subject = new EventsList($this->backEndModule);
    }

    /**
     * @test
     */
    public function showForEventWithoutRegistrationsNotContainsShowRegistrationsLink(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $pageUid = 2;
        PageFinder::getInstance()->setPageUid($pageUid);
        $this->backEndModule->id = $pageUid;
        $this->backEndModule->setPageData(['uid' => $pageUid, 'doktype' => AbstractList::SYSFOLDER_TYPE]);

        $result = $this->subject->show();

        $label = $this->translate('label_show_event_registrations');
        self::assertStringNotContainsString($label, $result);
    }

    /**
     * @test
     */
    public function showForEventWithoutRegistrationsNotContainsEmailButton(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/Records.xml');

        $pageUid = 2;
        PageFinder::getInstance()->setPageUid($pageUid);
        $this->backEndModule->id = $pageUid;
        $this->backEndModule->setPageData(['uid' => $pageUid, 'doktype' => AbstractList::SYSFOLDER_TYPE]);

        $result = $this->subject->show();

        $label = $this->translate('label_email_button');
        self::assertStringNotContainsString('<button><p>' . $label . '</p></button>', $result);
    }
}
