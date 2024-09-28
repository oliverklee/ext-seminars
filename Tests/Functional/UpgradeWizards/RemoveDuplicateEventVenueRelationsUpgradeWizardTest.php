<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\UpgradeWizards;

use OliverKlee\Seminars\UpgradeWizards\RemoveDuplicateEventVenueRelationsUpgradeWizard;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Seminars\UpgradeWizards\RemoveDuplicateEventVenueRelationsUpgradeWizard
 */
class RemoveDuplicateEventVenueRelationsUpgradeWizardTest extends FunctionalTestCase
{
    /**
     * @var non-empty-string
     */
    private $fixturesPrefix = __DIR__ . '/Fixtures/RemoveDuplicateEventVenueRelationsUpgradeWizard/';

    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    /**
     * @var RemoveDuplicateEventVenueRelationsUpgradeWizard
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = GeneralUtility::makeInstance(RemoveDuplicateEventVenueRelationsUpgradeWizard::class);
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
    public function updateNecessaryForNoRelationsReturnsFalse(): void
    {
        $this->importCSVDataSet($this->fixturesPrefix . 'NoRelations.csv');

        self::assertFalse($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function updateNecessaryForOneUniqueRelationReturnsFalse(): void
    {
        $this->importCSVDataSet($this->fixturesPrefix . 'OneUniqueRelation.csv');

        self::assertFalse($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function updateNecessaryForTwoUniqueRelationsFromTwoEventsToTheSameVenueReturnsFalse(): void
    {
        $this->importCSVDataSet($this->fixturesPrefix . 'TwoUniqueRelationsFromTwoEventsToTheSameVenue.csv');

        self::assertFalse($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function updateNecessaryForTwoUniqueRelationsFromOneEventToTwoVenusReturnsFalse(): void
    {
        $this->importCSVDataSet($this->fixturesPrefix . 'TwoUniqueRelationsFromOneEventToTwoVenues.csv');

        self::assertFalse($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function updateNecessaryForTwoDuplicatedRelationsReturnsTrue(): void
    {
        $this->importCSVDataSet($this->fixturesPrefix . 'TwoDuplicateRelations.csv');

        self::assertTrue($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function updateNecessaryForOneDuplicatedRelationAndOneUniqueRelationReturnsTrue(): void
    {
        $this->importCSVDataSet($this->fixturesPrefix . 'OneDuplicateAndOneUniqueRelationFromOneEventToTwoVenues.csv');

        self::assertTrue($this->subject->updateNecessary());
    }

    /**
     * @test
     */
    public function executeUpdateForNoRelationsDoesNotAddAnyRelation(): void
    {
        $this->importCSVDataSet($this->fixturesPrefix . 'NoRelations.csv');

        $this->subject->executeUpdate();

        $this->assertCSVDataSet($this->fixturesPrefix . 'NoRelations.csv');
    }

    /**
     * @test
     */
    public function executeUpdateForOneUniqueRelationKeepsUniqueRelation(): void
    {
        $this->importCSVDataSet($this->fixturesPrefix . 'OneUniqueRelation.csv');

        $this->subject->executeUpdate();

        $this->assertCSVDataSet($this->fixturesPrefix . 'OneUniqueRelation.csv');
    }

    /**
     * @test
     */
    public function executeUpdateForTwoUniqueRelationsFromTwoEventsToTheSameVenueKeepsBothUniqueRelations(): void
    {
        $this->importCSVDataSet($this->fixturesPrefix . 'TwoUniqueRelationsFromTwoEventsToTheSameVenue.csv');

        $this->subject->executeUpdate();

        $this->assertCSVDataSet($this->fixturesPrefix . 'TwoUniqueRelationsFromTwoEventsToTheSameVenue.csv');
    }

    /**
     * @test
     */
    public function executeUpdateForTwoUniqueRelationsFromOneEventToTwoVenuesKeepsBothUniqueRelations(): void
    {
        $this->importCSVDataSet($this->fixturesPrefix . 'TwoUniqueRelationsFromOneEventToTwoVenues.csv');

        $this->subject->executeUpdate();

        $this->assertCSVDataSet($this->fixturesPrefix . 'TwoUniqueRelationsFromOneEventToTwoVenues.csv');
    }

    /**
     * @test
     */
    public function executeUpdateForTwoDuplicatedRelationsDeletesOneOfThem(): void
    {
        $this->importCSVDataSet($this->fixturesPrefix . 'TwoDuplicateRelations.csv');

        $this->subject->executeUpdate();

        $this->assertCSVDataSet($this->fixturesPrefix . 'OneUniqueRelation.csv');
    }

    /**
     * @test
     */
    public function executeUpdateForThreeDuplicatedRelationDeletesTwoOfThem(): void
    {
        $this->importCSVDataSet($this->fixturesPrefix . 'ThreeDuplicateRelations.csv');

        $this->subject->executeUpdate();

        $this->assertCSVDataSet($this->fixturesPrefix . 'OneUniqueRelation.csv');
    }

    /**
     * @test
     */
    public function executeUpdateForOneDuplicatedRelationAndOneUniqueRelationRemovesDuplicate(): void
    {
        $this->importCSVDataSet($this->fixturesPrefix . 'OneDuplicateAndOneUniqueRelationFromOneEventToTwoVenues.csv');

        $this->subject->executeUpdate();

        $this->assertCSVDataSet($this->fixturesPrefix . 'TwoUniqueRelationsFromOneEventToTwoVenues.csv');
    }
}
