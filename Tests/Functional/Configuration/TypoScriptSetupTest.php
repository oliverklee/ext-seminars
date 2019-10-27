<?php
namespace OliverKlee\Seminars\Tests\Functional\Configuration;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TypoScriptSetupTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/seminars'];

    /**
     * Extracts the class name from something like '...->foo'.
     *
     * @param string $reference
     *
     * @return string class name
     */
    private function extractClassNameFromUserFunction($reference)
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
    private function extractMethodNameFromUserFunction($reference)
    {
        $parts = \explode('->', $reference);

        return \array_pop($parts);
    }

    /**
     * @test
     */
    public function userFunctionsPointToExistingMethodInExistingClass()
    {
        $extensionPath = ExtensionManagementUtility::extPath('seminars');
        $typoScriptSetup = \file_get_contents($extensionPath . 'Configuration/TypoScript/CsvExport.txt') . "\n" .
            \file_get_contents($extensionPath . 'Configuration/TypoScript/Publication.txt');

        /** @var string[] $matches */
        $matches = [];
        \preg_match('/userFunc += +([^\\s]+)/', $typoScriptSetup, $matches);
        \array_shift($matches);

        foreach ($matches as $match) {
            $className = $this->extractClassNameFromUserFunction($match);
            $methodName = $this->extractMethodNameFromUserFunction($match);

            self::assertTrue(\class_exists($className), 'Class ' . $className . ' does not exist.');

            $instance = new $className();
            self::assertTrue(
                \method_exists($instance, $methodName),
                'Method ' . $methodName . ' does not exist in class ' . $className
            );
        }
    }
}
