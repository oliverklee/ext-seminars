<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Rendering;

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
class NullRenderingContext implements RenderingContextInterface
{
    public function getErrorHandler()
    {
        throw new \BadMethodCallException('Not implemented.', 1645902075);
    }

    public function setErrorHandler(ErrorHandlerInterface $errorHandler)
    {
    }

    /**
     * @return void
     */
    public function setVariableProvider(VariableProviderInterface $variableProvider)
    {
    }

    /**
     * @return void
     */
    public function setViewHelperVariableContainer(ViewHelperVariableContainer $viewHelperVariableContainer)
    {
    }

    public function getVariableProvider()
    {
        throw new \BadMethodCallException('Not implemented.', 1645902096);
    }

    public function getViewHelperVariableContainer()
    {
        throw new \BadMethodCallException('Not implemented.', 1645902107);
    }

    public function getViewHelperResolver()
    {
        throw new \BadMethodCallException('Not implemented.', 1645902122);
    }

    public function setViewHelperResolver(ViewHelperResolver $viewHelperResolver)
    {
    }

    public function getViewHelperInvoker()
    {
        throw new \BadMethodCallException('Not implemented.', 1645902138);
    }

    public function setViewHelperInvoker(ViewHelperInvoker $viewHelperInvoker)
    {
    }

    public function setTemplateParser(TemplateParser $templateParser)
    {
    }

    public function getTemplateParser()
    {
        throw new \BadMethodCallException('Not implemented.', 1645902147);
    }

    public function setTemplateCompiler(TemplateCompiler $templateCompiler)
    {
    }

    public function getTemplateCompiler()
    {
        throw new \BadMethodCallException('Not implemented.', 1645902156);
    }

    public function getTemplatePaths()
    {
        throw new \BadMethodCallException('Not implemented.', 1645902162);
    }

    public function setTemplatePaths(TemplatePaths $templatePaths)
    {
    }

    public function setCache(FluidCacheInterface $cache)
    {
    }

    public function getCache()
    {
        throw new \BadMethodCallException('Not implemented.', 1645902170);
    }

    public function isCacheEnabled()
    {
        throw new \BadMethodCallException('Not implemented.', 1645902176);
    }

    public function setTemplateProcessors(array $templateProcessors)
    {
    }

    public function getTemplateProcessors()
    {
        throw new \BadMethodCallException('Not implemented.', 1645902182);
    }

    /**
     * @return array<int, class-string>
     */
    public function getExpressionNodeTypes()
    {
        throw new \BadMethodCallException('Not implemented.', 1645902192);
    }

    /**
     * @param array<int, class-string> $expressionNodeTypes
     */
    public function setExpressionNodeTypes(array $expressionNodeTypes)
    {
    }

    public function buildParserConfiguration()
    {
        throw new \BadMethodCallException('Not implemented.', 1645902199);
    }

    public function getControllerName()
    {
        throw new \BadMethodCallException('Not implemented.', 1645902211);
    }

    public function setControllerName($controllerName)
    {
    }

    public function getControllerAction()
    {
        throw new \BadMethodCallException('Not implemented.', 1645902216);
    }

    public function setControllerAction($action)
    {
    }
}
