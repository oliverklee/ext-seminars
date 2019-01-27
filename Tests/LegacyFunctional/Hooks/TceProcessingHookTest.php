<?php
namespace OliverKlee\Seminars\Tests\LegacyFunctional\Hooks;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TceProcessingHookTest extends \Tx_Phpunit_TestCase
{
    /**
     * @test
     */
    public function tceMainHookReferencesExistingClass()
    {
        $reference = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['seminars'];
        $instance = GeneralUtility::getUserObj($reference);

        self::assertInstanceOf(\Tx_Seminars_Hooks_TceProcessingHook::class, $instance);
    }
}
