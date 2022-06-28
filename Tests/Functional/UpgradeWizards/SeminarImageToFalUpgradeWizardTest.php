<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\UpgradeWizards;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\Tests\Functional\Traits\FalHelper;
use OliverKlee\Seminars\UpgradeWizards\SeminarImageToFalUpgradeWizard;

/**
 * @covers \OliverKlee\Seminars\UpgradeWizards\SeminarImageToFalUpgradeWizard
 */
final class SeminarImageToFalUpgradeWizardTest extends FunctionalTestCase
{
    use FalHelper;

    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/seminars'];

    /**
     * @var SeminarImageToFalUpgradeWizard
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provideAdminBackEndUserForFal();
        $this->subject = new SeminarImageToFalUpgradeWizard();
    }

    /**
     * @test
     */
    public function isRegistered(): void
    {
        self::assertSame(
            SeminarImageToFalUpgradeWizard::class,
            $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['seminars_migrateSeminarImagesToFal']
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
