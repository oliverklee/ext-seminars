<?php
namespace OliverKlee\Seminars\Tests\Functional\Configuration;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TypoScriptSetupTest extends \Tx_Phpunit_TestCase
{
    /**
     * Extracts the class name from something like '...->foo'.
     *
     * @param string $reference
     *
     * @return string class name
     */
    private function extractClassNameFromUserFunction($reference)
    {
        $parts = explode('->', $reference);

        return array_shift($parts);
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
        $parts = explode('->', $reference);

        return array_pop($parts);
    }

    /**
     * @test
     */
    public function userFunctionsPointToExistingMethodInExistingClass()
    {
        $typoScriptSetup = file_get_contents(
            ExtensionManagementUtility::extPath('seminars') . 'Configuration/TypoScript/setup.txt'
        );

        /** @var string[] $matches */
        $matches = [];
        preg_match('/userFunc += +([^\\s]+)/', $typoScriptSetup, $matches);
        array_shift($matches);

        foreach ($matches as $match) {
            $className = $this->extractClassNameFromUserFunction($match);
            $methodName = $this->extractMethodNameFromUserFunction($match);

            self::assertTrue(class_exists($className), 'Class ' . $className . ' does not exist.');

            $instance = new $className();
            self::assertTrue(
                method_exists($instance, $methodName),
                'Method ' . $methodName . ' does not exist in class ' . $className
            );
        }
    }
}
