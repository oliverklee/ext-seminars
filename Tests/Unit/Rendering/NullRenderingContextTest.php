<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Rendering;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Seminars\Rendering\NullRenderingContext;
use OliverKlee\Seminars\Rendering\NullRequest;
use TYPO3Fluid\Fluid\Core\Cache\FluidCacheInterface;
use TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler;
use TYPO3Fluid\Fluid\Core\ErrorHandler\ErrorHandlerInterface;
use TYPO3Fluid\Fluid\Core\Parser\TemplateParser;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInvoker;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperResolver;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer;
use TYPO3Fluid\Fluid\View\TemplatePaths;

/**
 * @covers \OliverKlee\Seminars\Rendering\NullRenderingContext
 */
final class NullRenderingContextTest extends UnitTestCase
{
    /**
     * @var NullRenderingContext
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new NullRenderingContext();
    }

    /**
     * @test
     */
    public function implementsRenderingContextInterface(): void
    {
        self::assertInstanceOf(RenderingContextInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function getErrorHandlerMustNotBeCalled(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Not implemented.');
        $this->expectExceptionCode(1645902075);

        $this->subject->getErrorHandler();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function setErrorHandlerCanBeCalled(): void
    {
        $this->subject->setErrorHandler($this->createMock(ErrorHandlerInterface::class));
    }

    /**
     * @test
     */
    public function getVariableProviderMustNotBeCalled(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Not implemented.');
        $this->expectExceptionCode(164590206);

        $this->subject->getVariableProvider();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function setVariableProviderCanBeCalled(): void
    {
        $this->subject->setVariableProvider($this->createMock(VariableProviderInterface::class));
    }

    /**
     * @test
     */
    public function getViewHelperVariableContainerMustNotBeCalled(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Not implemented.');
        $this->expectExceptionCode(1645902107);

        $this->subject->getViewHelperVariableContainer();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function setViewHelperVariableContainerCanBeCalled(): void
    {
        $this->subject->setViewHelperVariableContainer($this->createMock(ViewHelperVariableContainer::class));
    }

    /**
     * @test
     */
    public function getViewHelperResolverMustNotBeCalled(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Not implemented.');
        $this->expectExceptionCode(1645902122);

        $this->subject->getViewHelperResolver();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function setViewHelperResolverCanBeCalled(): void
    {
        $this->subject->setViewHelperResolver($this->createMock(ViewHelperResolver::class));
    }

    /**
     * @test
     */
    public function getViewHelperInvokerMustNotBeCalled(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Not implemented.');
        $this->expectExceptionCode(1645902138);

        $this->subject->getViewHelperInvoker();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function setViewHelperInvokerCanBeCalled(): void
    {
        $this->subject->setViewHelperInvoker($this->createMock(ViewHelperInvoker::class));
    }

    /**
     * @test
     */
    public function getTemplateParserMustNotBeCalled(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Not implemented.');
        $this->expectExceptionCode(1645902147);

        $this->subject->getTemplateParser();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function setTemplateParserCanBeCalled(): void
    {
        $this->subject->setTemplateParser($this->createMock(TemplateParser::class));
    }

    /**
     * @test
     */
    public function getTemplateCompilerMustNotBeCalled(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Not implemented.');
        $this->expectExceptionCode(1645902156);

        $this->subject->getTemplateCompiler();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function setTemplateCompilerCanBeCalled(): void
    {
        $this->subject->setTemplateCompiler($this->createMock(TemplateCompiler::class));
    }

    /**
     * @test
     */
    public function getTemplatePathsMustNotBeCalled(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Not implemented.');
        $this->expectExceptionCode(1645902162);

        $this->subject->getTemplatePaths();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function setTemplatePathsCanBeCalled(): void
    {
        $this->subject->setTemplatePaths($this->createMock(TemplatePaths::class));
    }

    /**
     * @test
     */
    public function getCacheMustNotBeCalled(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Not implemented.');
        $this->expectExceptionCode(1645902170);

        $this->subject->getCache();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function setCacheCanBeCalled(): void
    {
        $this->subject->setCache($this->createMock(FluidCacheInterface::class));
    }

    /**
     * @test
     */
    public function isCacheEnabledAlwaysReturnsFalse(): void
    {
        self::assertFalse($this->subject->isCacheEnabled());
    }

    /**
     * @test
     */
    public function getTemplateProcessorsMustNotBeCalled(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Not implemented.');
        $this->expectExceptionCode(1645902182);

        $this->subject->getTemplateProcessors();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function setTemplateProcessorsCanBeCalled(): void
    {
        $this->subject->setTemplateProcessors([]);
    }

    /**
     * @test
     */
    public function getExpressionNodeTypesMustNotBeCalled(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Not implemented.');
        $this->expectExceptionCode(1645902192);

        $this->subject->getExpressionNodeTypes();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function setExpressionNodeTypesCanBeCalled(): void
    {
        $this->subject->setExpressionNodeTypes([]);
    }

    /**
     * @test
     */
    public function buildParserConfigurationMustNotBeCalled(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Not implemented.');
        $this->expectExceptionCode(1645902199);

        $this->subject->buildParserConfiguration();
    }

    /**
     * @test
     */
    public function getControllerNameMustNotBeCalled(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Not implemented.');
        $this->expectExceptionCode(1645902211);

        $this->subject->getControllerName();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function setControllerNameTypesCanBeCalled(): void
    {
        $this->subject->setControllerName('');
    }

    /**
     * @test
     */
    public function getControllerActionMustNotBeCalled(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Not implemented.');
        $this->expectExceptionCode(1645902216);

        $this->subject->getControllerAction();
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function setControllerActionCanBeCalled(): void
    {
        $this->subject->setControllerAction('');
    }

    /**
     * @test
     */
    public function getRequestReturnsNullRequest(): void
    {
        self::assertInstanceOf(NullRequest::class, $this->subject->getRequest());
    }
}
