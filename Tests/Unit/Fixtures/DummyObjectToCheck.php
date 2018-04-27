<?php
namespace OliverKlee\Seminars\Tests\Unit\Fixtures;

/**
 * This a class to test the configuration check class.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class DummyObjectToCheck extends \Tx_Oelib_TemplateHelper implements \Tx_Oelib_Interface_ConfigurationCheckable
{
    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->init($configuration);
    }

    /**
     * Returns the prefix for the configuration to check, e.g. "plugin.tx_seminars_pi1.".
     *
     * @return string the namespace prefix, will end with a dot
     */
    public function getTypoScriptNamespace()
    {
        return 'plugin.tx_seminars_test.';
    }
}
