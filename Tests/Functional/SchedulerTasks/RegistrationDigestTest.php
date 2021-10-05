<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\SchedulerTasks;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\SchedulerTasks\RegistrationDigest;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

final class RegistrationDigestTest extends FunctionalTestCase
{
    /**
     * @var array<int, string>
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    protected function tearDown(): void
    {
        MapperRegistry::purgeInstance();
        ConfigurationRegistry::purgeInstance();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function objectManagerInitializesObject(): void
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $subject = $objectManager->get(RegistrationDigest::class);

        self::assertTrue($subject->isInitialized());
    }
}
