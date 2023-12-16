<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\SchedulerTasks;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\SchedulerTasks\RegistrationDigest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

final class RegistrationDigestTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    protected function tearDown(): void
    {
        MapperRegistry::purgeInstance();
        ConfigurationRegistry::purgeInstance();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function canBeBuildViaTheDependencyInjectionContainer(): void
    {
        $subject = $this->get(RegistrationDigest::class);

        self::assertInstanceOf(RegistrationDigest::class, $subject);
        self::assertTrue($subject->isInitialized());
    }
}
