<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Rendering;

use Psr\Http\Message\ServerRequestInterface;
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
 * Dummy rendering context to pass into the `HtmlViewHelper`.
 *
 * Its methods are not intended to get called.
 */
final class NullRenderingContext implements RenderingContextInterface
{
    /**
     * @return never
     */
    public function getErrorHandler(): ErrorHandlerInterface
    {
        throw new \BadMethodCallException('Not implemented.', 1645902075);
    }

    public function setErrorHandler(ErrorHandlerInterface $errorHandler): void
    {
    }

    /**
     * @return never
     */
    public function getVariableProvider(): VariableProviderInterface
    {
        throw new \BadMethodCallException('Not implemented.', 164590206);
    }

    public function setVariableProvider(VariableProviderInterface $variableProvider): void
    {
    }

    /**
     * @return never
     */
    public function getViewHelperVariableContainer(): ViewHelperVariableContainer
    {
        throw new \BadMethodCallException('Not implemented.', 1645902107);
    }

    public function setViewHelperVariableContainer(ViewHelperVariableContainer $viewHelperVariableContainer): void
    {
    }

    /**
     * @return never
     */
    public function getViewHelperResolver(): ViewHelperResolver
    {
        throw new \BadMethodCallException('Not implemented.', 1645902122);
    }

    public function setViewHelperResolver(ViewHelperResolver $viewHelperResolver): void
    {
    }

    /**
     * @return never
     */
    public function getViewHelperInvoker(): ViewHelperInvoker
    {
        throw new \BadMethodCallException('Not implemented.', 1645902138);
    }

    public function setViewHelperInvoker(ViewHelperInvoker $viewHelperInvoker): void
    {
    }

    /**
     * @return never
     */
    public function getTemplateParser(): TemplateParser
    {
        throw new \BadMethodCallException('Not implemented.', 1645902147);
    }

    public function setTemplateParser(TemplateParser $templateParser): void
    {
    }

    /**
     * @return never
     */
    public function getTemplateCompiler(): TemplateCompiler
    {
        throw new \BadMethodCallException('Not implemented.', 1645902156);
    }

    public function setTemplateCompiler(TemplateCompiler $templateCompiler): void
    {
    }

    /**
     * @return never
     */
    public function getTemplatePaths(): TemplatePaths
    {
        throw new \BadMethodCallException('Not implemented.', 1645902162);
    }

    public function setTemplatePaths(TemplatePaths $templatePaths): void
    {
    }

    /**
     * @return never
     */
    public function getCache(): FluidCacheInterface
    {
        throw new \BadMethodCallException('Not implemented.', 1645902170);
    }

    public function setCache(FluidCacheInterface $cache): void
    {
    }

    public function isCacheEnabled(): bool
    {
        return false;
    }

    /**
     * @return never
     */
    public function getTemplateProcessors(): array
    {
        throw new \BadMethodCallException('Not implemented.', 1645902182);
    }

    public function setTemplateProcessors(array $templateProcessors): void
    {
    }

    /**
     * @return never
     */
    public function getExpressionNodeTypes(): array
    {
        throw new \BadMethodCallException('Not implemented.', 1645902192);
    }

    /**
     * @param array<int, class-string> $expressionNodeTypes
     */
    public function setExpressionNodeTypes(array $expressionNodeTypes): void
    {
    }

    /**
     * @return never
     */
    public function buildParserConfiguration(): void
    {
        throw new \BadMethodCallException('Not implemented.', 1645902199);
    }

    /**
     * @return never
     */
    public function getControllerName(): string
    {
        throw new \BadMethodCallException('Not implemented.', 1645902211);
    }

    /**
     * @param string $controllerName
     */
    public function setControllerName($controllerName): void
    {
    }

    /**
     * @return never
     */
    public function getControllerAction(): string
    {
        throw new \BadMethodCallException('Not implemented.', 1645902216);
    }

    /**
     * @param string $action
     */
    public function setControllerAction($action): void
    {
    }

    public function getRequest(): ServerRequestInterface
    {
        return new NullRequest();
    }

    /**
     * @return never
     */
    public function getAttribute(string $name): object
    {
        throw new \BadMethodCallException('Not implemented.', 1701345822);
    }

    /**
     * @return never
     */
    public function withAttribute(string $name, object $value): RenderingContextInterface
    {
        throw new \BadMethodCallException('Not implemented.', 1701345830);
    }

    /**
     * @return never
     */
    public function setAttribute(string $className, object $value): void
    {
        throw new \BadMethodCallException('Not implemented.', 1721808769);
    }

    public function hasAttribute(string $className): bool
    {
        return false;
    }
}
