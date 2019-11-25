<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\Hooks;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Hooks\DataHandlerHook;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class DataHandlerHookTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/seminars'];

    private function getDataMapperConfigurationForSeminars(): string
    {
        $dataMapperConfiguration = (array)$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php'];

        return (string)$dataMapperConfiguration['processDatamapClass']['seminars'];
    }

    /**
     * @test
     */
    public function tceMainHookReferencesExistingClass()
    {
        $reference = $this->getDataMapperConfigurationForSeminars();

        self::assertSame(DataHandlerHook::class, $reference);
    }
}
