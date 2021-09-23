<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\FrontEnd\TestingView;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \Tx_Seminars_FrontEnd_AbstractView
 */
final class AbstractViewTest extends TestCase
{
    /**
     * @var TestingView
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        $this->testingFramework = new TestingFramework('tx_seminars');
        $this->testingFramework->createFakeFrontEnd();
        $this->subject = new TestingView(
            ['templateFile' => 'EXT:seminars/Resources/Private/Templates/FrontEnd/FrontEnd.html'],
            $this->getFrontEndController()->cObj
        );
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * @test
     */
    public function renderCanReturnAViewsContent()
    {
        self::assertSame('Hi, I am the testingFrontEndView!', $this->subject->render());
    }
}
