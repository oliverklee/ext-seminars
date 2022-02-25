<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\FrontEnd\DefaultController;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\FrontEnd\DefaultController;

/**
 * @covers \OliverKlee\Seminars\FrontEnd\DefaultController
 */
final class PluginDefinitionTest extends FunctionalTestCase
{
    /**
     * @var array<int, string>
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    private function getContentRenderingConfiguration(): string
    {
        return (string)$GLOBALS['TYPO3_CONF_VARS']['FE']['defaultTypoScript_setup.']['defaultContentRendering'];
    }

    /**
     * Extracts the class name from something like '...->foo'.
     *
     * @return class-string
     */
    private function extractClassNameFromUserFunction(string $reference): string
    {
        $parts = \explode('->', $reference);

        return \array_shift($parts);
    }

    /**
     * Extracts the method name from something like '...->foo'.
     *
     * @return string method name
     */
    private function extractMethodNameFromUserFunction(string $reference): string
    {
        $parts = \explode('->', $reference);

        return \array_pop($parts);
    }

    /**
     * @test
     */
    public function defaultContentRenderingIsGenerated(): void
    {
        $configuration = $this->getContentRenderingConfiguration();

        self::assertStringContainsString('TypoScript added by extension "seminars"', $configuration);
        self::assertStringContainsString('tt_content.list.20.seminars_pi1 = < plugin.tx_seminars_pi1', $configuration);
    }

    /**
     * @test
     */
    public function pluginUserFuncPointsToExistingMethodInExistingDefaultControllerClass(): void
    {
        $configuration = $this->getContentRenderingConfiguration();

        $matches = [];
        \preg_match('/plugin\\.tx_seminars_pi1\\.userFunc = ([^\\s]+)/', $configuration, $matches);
        $className = $this->extractClassNameFromUserFunction($matches[1]);
        $methodName = $this->extractMethodNameFromUserFunction($matches[1]);

        self::assertSame(DefaultController::class, $className);

        self::assertTrue(
            \method_exists(DefaultController::class, $methodName),
            'Method ' . $methodName . ' does not exist in class ' . $className
        );
    }
}
