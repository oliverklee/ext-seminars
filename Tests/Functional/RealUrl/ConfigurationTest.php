<?php
namespace OliverKlee\Seminars\Tests\Functional\RealUrl;

use OliverKlee\Seminars\RealUrl\Configuration;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ConfigurationTest extends \Tx_Phpunit_TestCase
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
    public function autoConfigurationReferencesExistingClass()
    {
        $reference = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration']['seminars'];
        $className = $this->extractClassNameFromUserFunction($reference);

        self::assertTrue(class_exists($className));
        self::assertSame(Configuration::class, $className);
    }

    /**
     * @test
     */
    public function autoConfigurationReferencesExistingMethod()
    {
        $reference = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/realurl/class.tx_realurl_autoconfgen.php']['extensionConfiguration']['seminars'];
        $methodName = $this->extractMethodNameFromUserFunction($reference);

        $instance = new Configuration();

        self::assertTrue(method_exists($instance, $methodName));
    }
}
