<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\UpgradeWizards;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Seminars\UpgradeWizards\SeparateBillingAddressUpgradeWizard;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Seminars\UpgradeWizards\SeparateBillingAddressUpgradeWizard
 */
class SeparateBillingAddressUpgradeWizardTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var SeparateBillingAddressUpgradeWizard
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = GeneralUtility::makeInstance(SeparateBillingAddressUpgradeWizard::class);
    }

    /**
     * @test
     */
    public function updateNecessaryForEmptyDatabaseReturnsFalse(): void
    {
        self::assertFalse($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function updateNecessaryForRegistrationWithoutBillingAddressReturnsFalse(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithoutBillingAddress.xml');

        self::assertFalse($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function updateNecessaryForRegistrationWithMarkedBillingAddressReturnsFalse(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithMarkedBillingAddress.xml');

        self::assertFalse($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function updateNecessaryForRegistrationWithUnmarkedBillingAddressCityReturnsTrue(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithUnmarkedBillingAddressCity.xml');

        self::assertTrue($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function executeUpdateKeepsRegistrationWithoutBillingAddressUnmarked(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithoutBillingAddress.xml');

        $wizardResult = $this->subject->executeUpdate();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_attendances');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_attendances WHERE uid = :uid', ['uid' => 1]);
        if (\method_exists($result, 'fetchAssociative')) {
            $databaseRow = $result->fetchAssociative();
        } else {
            $databaseRow = $result->fetch();
        }

        self::assertIsArray($databaseRow);
        self::assertSame(0, $databaseRow['separate_billing_address']);
        self::assertTrue($wizardResult);
    }

    /**
     * @test
     */
    public function executeUpdateMarksRegistrationWithUnmarkedBillingAddress(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/RegistrationWithUnmarkedBillingAddressCity.xml');

        $wizardResult = $this->subject->executeUpdate();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_seminars_attendances');
        $result = $connection
            ->executeQuery('SELECT * FROM tx_seminars_attendances WHERE uid = :uid', ['uid' => 1]);
        if (\method_exists($result, 'fetchAssociative')) {
            $databaseRow = $result->fetchAssociative();
        } else {
            $databaseRow = $result->fetch();
        }

        self::assertIsArray($databaseRow);
        self::assertSame(1, $databaseRow['separate_billing_address']);
        self::assertTrue($wizardResult);
    }
}
