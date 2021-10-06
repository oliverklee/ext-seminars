<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\UpgradeWizards;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Tests\Functional\Traits\FalHelper;
use OliverKlee\Seminars\UpgradeWizards\CategoryIconToFalUpgradeWizard;

/**
 * @covers \OliverKlee\Seminars\UpgradeWizards\CategoryIconToFalUpgradeWizard
 */
final class CategoryIconToFalUpgradeWizardTest extends FunctionalTestCase
{
    use FalHelper;

    /**
     * @var array<int, string>
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var CategoryIconToFalUpgradeWizard
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provideAdminBackEndUserForFal();
        $this->subject = new CategoryIconToFalUpgradeWizard();
    }

    /**
     * @test
     */
    public function isRegistered(): void
    {
        self::assertSame(
            CategoryIconToFalUpgradeWizard::class,
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['seminars_migrateCategoryIconsToFal']
        );
    }

    /**
     * @test
     */
    public function canCheckForUpdateNecessary(): void
    {
        self::assertIsBool($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function canBeRun(): void
    {
        self::assertIsBool($this->subject->executeUpdate());
    }
}
