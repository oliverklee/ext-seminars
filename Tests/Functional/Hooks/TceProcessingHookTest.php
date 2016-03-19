<?php
namespace OliverKlee\Seminars\Tests\Functional\Hooks;

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
