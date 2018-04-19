<?php
namespace OliverKlee\Seminars\Tests\Functional\FrontEnd;

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

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class DefaultControllerTest extends \Tx_Phpunit_TestCase
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
    public function defaultContentRenderingIsGenerated()
    {
        $configuration = $GLOBALS['TYPO3_CONF_VARS']['FE']['defaultTypoScript_setup.']['defaultContentRendering'];

        self::assertContains('TypoScript added by extension "seminars"', $configuration);
        self::assertContains('tt_content.list.20.seminars_pi1 = < plugin.tx_seminars_pi1', $configuration);
    }

    /**
     * @test
     */
    public function pluginUserFuncPointsToExistingMethodInExistingDefaultControllerClass()
    {
        $configuration = $GLOBALS['TYPO3_CONF_VARS']['FE']['defaultTypoScript_setup.']['defaultContentRendering'];

        $matches = [];
        preg_match('/plugin\\.tx_seminars_pi1\\.userFunc = ([^\\s]+)/', $configuration, $matches);
        $className = $this->extractClassNameFromUserFunction($matches[1]);
        $methodName = $this->extractMethodNameFromUserFunction($matches[1]);

        self::assertTrue(class_exists($className), 'Class ' . $className . ' does not exist.');
        self::assertSame(\Tx_Seminars_FrontEnd_DefaultController::class, $className);

        $instance = new \Tx_Seminars_FrontEnd_DefaultController();
        self::assertTrue(
            method_exists($instance, $methodName),
            'Method ' . $methodName . ' does not exist in class ' . $className
        );
    }
}
