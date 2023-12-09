<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\FrontEnd\TestingView;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\AbstractView
 */
final class AbstractViewTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var TestingView
     */
    private $subject;

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_seminars');
        $rootPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->changeRecord('pages', $rootPageUid, ['slug' => '/home']);
        $this->testingFramework->createFakeFrontEnd($rootPageUid);
        $this->subject = new TestingView(
            ['templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'],
            $this->getFrontEndController()->cObj
        );
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUp();

        parent::tearDown();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * @test
     */
    public function renderCanReturnAViewsContent(): void
    {
        self::assertSame('Hi, I am the testingFrontEndView!', $this->subject->render());
    }
}
