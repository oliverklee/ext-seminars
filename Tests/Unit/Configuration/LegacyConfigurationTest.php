<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Configuration;

use OliverKlee\Oelib\Templating\TemplateHelper;
use OliverKlee\Seminars\Configuration\LegacyConfiguration;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Configuration\LegacyConfiguration
 */
final class LegacyConfigurationTest extends UnitTestCase
{
    protected $resetSingletonInstances = true;

    /**
     * @var ContentObjectRenderer
     */
    private $contentObjectMock;

    /**
     * @var LegacyConfiguration
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $frontEndControllerMock = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()->getMock();
        $this->contentObjectMock = $this->getMockBuilder(ContentObjectRenderer::class)
            ->disableOriginalConstructor()->getMock();
        $frontEndControllerMock->cObj = $this->contentObjectMock;
        $GLOBALS['TSFE'] = $frontEndControllerMock;

        $this->subject = new LegacyConfiguration();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TSFE']);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function extendsTemplateHelper(): void
    {
        self::assertInstanceOf(TemplateHelper::class, $this->subject);
    }

    /**
     * @test
     */
    public function constructionWithoutFrontEndThrowsException(): void
    {
        unset($GLOBALS['TSFE']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No front end found.');
        $this->expectExceptionCode(1668938450);

        $this->subject = new LegacyConfiguration();
    }

    /**
     * @test
     */
    public function hasContentObjectFromFrontEnd(): void
    {
        self::assertSame($this->contentObjectMock, $this->subject->cObj);
    }
}
