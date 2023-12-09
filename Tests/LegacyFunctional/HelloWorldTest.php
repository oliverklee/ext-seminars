<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyFunctional;

use OliverKlee\Seminars\Tests\Support\LanguageHelper;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @coversNothing
 */
final class HelloWorldTest extends FunctionalTestCase
{
    use LanguageHelper;

    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    protected $initializeDatabase = false;

    /**
     * @test
     */
    public function theUniverseWorksFine(): void
    {
        self::assertTrue(true);
    }
}
