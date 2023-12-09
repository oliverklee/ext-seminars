<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Configuration;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * @coversNothing
 */
final class TypoScriptSetupTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * Extracts the class name from something like '...->foo'.
     *
     * @param string $reference
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
     * @param string $reference
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
    public function userFunctionsPointToExistingMethodInExistingClass(): void
    {
        $extensionPath = ExtensionManagementUtility::extPath('seminars');
        $typoScriptSetup = \file_get_contents($extensionPath . 'Configuration/TypoScript/CsvExport.typoscript') . "\n" .
            \file_get_contents($extensionPath . 'Configuration/TypoScript/Publication.typoscript');

        /** @var string[] $matches */
        $matches = [];
        \preg_match('/userFunc += +([^\\s]+)/', $typoScriptSetup, $matches);
        \array_shift($matches);

        foreach ($matches as $match) {
            $className = $this->extractClassNameFromUserFunction($match);
            $methodName = $this->extractMethodNameFromUserFunction($match);

            self::assertTrue(\class_exists($className), 'Class "' . $className . '"" does not exist.');
            self::assertTrue(
                \method_exists($className, $methodName),
                'Method "' . $className . ':' . $methodName . ' "does not exist.'
            );
        }
    }
}
