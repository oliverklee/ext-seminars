<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BagBuilder;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Bag\AbstractBag;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\BagBuilder\BrokenBagBuilder;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\BagBuilder\TestingBagBuilder;

final class AbstractBagBuilderTest extends TestCase
{
    /**
     * @var TestingBagBuilder
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var int PID of a dummy system folder
     */
    private $dummySysFolderPid = 0;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->testingFramework = new TestingFramework('tx_seminars');

        $this->subject = new TestingBagBuilder();
        $this->subject->setTestMode();

        $this->dummySysFolderPid = $this->testingFramework->createSystemFolder();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    ///////////////////////////////////////////
    // Tests for the basic builder functions.
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function builderThrowsExceptionForEmptyTableName()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The attribute $this->tableName must not be empty.');

        new BrokenBagBuilder();
    }

    /**
     * @test
     */
    public function builderBuildsAnObject()
    {
        $bag = $this->subject->build();

        self::assertIsObject($bag);
    }

    /**
     * @test
     */
    public function builderBuildsABag()
    {
        $bag = $this->subject->build();

        self::assertInstanceOf(AbstractBag::class, $bag);
    }

    /**
     * @test
     */
    public function builderBuildsBagSortedAscendingByUid()
    {
        $eventUid1 = $this->testingFramework->createRecord('tx_seminars_test');
        $eventUid2 = $this->testingFramework->createRecord('tx_seminars_test');

        $testBag = $this->subject->build();
        self::assertEquals(
            2,
            $testBag->count()
        );

        self::assertEquals(
            $eventUid1,
            $testBag->current()->getUid()
        );
        self::assertEquals(
            $eventUid2,
            $testBag->next()->getUid()
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function builderWithAdditionalTableNameDoesNotProduceSqlError()
    {
        $this->subject->addAdditionalTableName('tx_seminars_seminars');

        $this->subject->build();
    }

    ///////////////////////////////////
    // Tests concerning source pages.
    ///////////////////////////////////

    /**
     * @test
     */
    public function builderInitiallyHasNoSourcePages()
    {
        self::assertFalse(
            $this->subject->hasSourcePages()
        );
    }

    /**
     * @test
     */
    public function builderHasSourcePagesWithOnePage()
    {
        $this->subject->setSourcePages((string)$this->dummySysFolderPid);

        self::assertTrue(
            $this->subject->hasSourcePages()
        );
    }

    /**
     * @test
     */
    public function builderHasSourcePagesWithTwoPages()
    {
        $this->subject->setSourcePages($this->dummySysFolderPid . ',' . ($this->dummySysFolderPid + 1));

        self::assertTrue(
            $this->subject->hasSourcePages()
        );
    }

    /**
     * @test
     */
    public function builderHasNoSourcePagesWithEvilSql()
    {
        $this->subject->setSourcePages('; DROP TABLE tx_seminars_test;');

        self::assertFalse(
            $this->subject->hasSourcePages()
        );
    }

    /**
     * @test
     */
    public function builderSelectsRecordsFromAllPagesByDefault()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid]
        );
        // Puts this record on a non-existing page. This is intentional.
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid + 1]
        );
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function builderSelectsRecordsFromAllPagesWithEmptySourcePages()
    {
        $this->subject->setSourcePages('');
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid]
        );
        // Puts this record on a non-existing page. This is intentional.
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid + 1]
        );
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function builderSelectsRecordsFromAllPagesWithEmptyAfterNonEmptySourcePages()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid]
        );
        // Puts this record on a non-existing page. This is intentional.
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid + 1]
        );

        $this->subject->setSourcePages((string)$this->dummySysFolderPid);
        $this->subject->setSourcePages('');
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function builderSelectsRecordsFromAllPagesWithEmptySourcePagesAndZeroRecursion()
    {
        $this->subject->setSourcePages('');
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid]
        );
        // Puts this record on a non-existing page. This is intentional.
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid + 1]
        );
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function builderSelectsRecordsFromAllPagesWithEmptySourcePagesAndNonZeroRecursion()
    {
        $this->subject->setSourcePages('', 1);
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid]
        );
        // Puts this record on a non-existing page. This is intentional.
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid + 1]
        );
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function builderSelectsRecordsFromOnePage()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid]
        );
        // Puts this record on a non-existing page. This is intentional.
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid + 1]
        );

        $this->subject->setSourcePages((string)$this->dummySysFolderPid);
        $bag = $this->subject->build();

        self::assertEquals(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function builderSelectsRecordsFromTwoPages()
    {
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid]
        );
        // Puts this record on a non-existing page. This is intentional.
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $this->dummySysFolderPid + 1]
        );

        $this->subject->setSourcePages($this->dummySysFolderPid . ',' . ($this->dummySysFolderPid + 1));
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function builderIgnoresRecordsOnSubpageWithoutRecursion()
    {
        $subPagePid = $this->testingFramework->createSystemFolder(
            $this->dummySysFolderPid
        );

        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $subPagePid]
        );

        $this->subject->setSourcePages((string)$this->dummySysFolderPid);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function builderSelectsRecordsOnSubpageWithRecursion()
    {
        $subPagePid = $this->testingFramework->createSystemFolder(
            $this->dummySysFolderPid
        );

        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $subPagePid]
        );

        $this->subject->setSourcePages((string)$this->dummySysFolderPid, 1);
        $bag = $this->subject->build();

        self::assertEquals(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function builderSelectsRecordsOnTwoSubpagesWithRecursion()
    {
        $subPagePid1 = $this->testingFramework->createSystemFolder(
            $this->dummySysFolderPid
        );
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $subPagePid1]
        );

        $subPagePid2 = $this->testingFramework->createSystemFolder(
            $this->dummySysFolderPid
        );
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $subPagePid2]
        );

        $this->subject->setSourcePages((string)$this->dummySysFolderPid, 1);
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function builderSelectsRecordsOnSubpageFromTwoParentsWithRecursion()
    {
        $subPagePid1 = $this->testingFramework->createSystemFolder(
            $this->dummySysFolderPid
        );
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $subPagePid1]
        );

        $parentPid2 = $this->testingFramework->createSystemFolder();
        $subPagePid2 = $this->testingFramework->createSystemFolder($parentPid2);
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $subPagePid2]
        );

        $this->subject->setSourcePages($this->dummySysFolderPid . ',' . $parentPid2, 1);
        $bag = $this->subject->build();

        self::assertEquals(
            2,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function builderIgnoresRecordsOnSubpageWithTooShallowRecursion()
    {
        $subPagePid = $this->testingFramework->createSystemFolder(
            $this->dummySysFolderPid
        );
        $subSubPagePid = $this->testingFramework->createSystemFolder(
            $subPagePid
        );

        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['pid' => $subSubPagePid]
        );

        $this->subject->setSourcePages((string)$this->dummySysFolderPid, 1);
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    ////////////////////////////////////////////////////////
    // Tests concerning hidden/deleted/timed etc. records.
    ////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function whereClauseInitiallyIsNotEmpty()
    {
        self::assertNotEquals(
            '',
            $this->subject->getWhereClause()
        );
    }

    /**
     * @test
     */
    public function whereClauseCanSelectPids()
    {
        $this->subject->setSourcePages((string)$this->dummySysFolderPid);

        // We're using assertContains here because the PID in the WHERE clause
        // may be prefixed with the table name.
        self::assertStringContainsString(
            'pid IN (' . $this->dummySysFolderPid . ')',
            $this->subject->getWhereClause()
        );
    }

    /**
     * @test
     */
    public function whereClausePartGetKeyMustNotBeEmpty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The parameter $key must not be empty.');

        $this->subject->getWhereClausePart('');
    }

    /**
     * @test
     */
    public function whereClausePartSetKeyMustNotBeEmpty()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The parameter $key must not be empty.');

        $this->subject->setWhereClausePart('', '');
    }

    /**
     * @test
     */
    public function whereClausePartInitiallyIsEmpty()
    {
        self::assertSame('', $this->subject->getWhereClausePart('testpart'));
    }

    /**
     * @test
     */
    public function whereClausePartCanBeSetAndCanBeRetrieved()
    {
        $this->subject->setWhereClausePart('testpart', 'testpart IN (1,2,3)');

        self::assertSame('testpart IN (1,2,3)', $this->subject->getWhereClausePart('testpart'));
    }

    /**
     * @test
     */
    public function whereClausePartSettingToEmptyCompletelyRemovesIt()
    {
        $this->subject->setWhereClausePart('testpart', 'testpart IN (1,2,3)');
        $this->subject->setWhereClausePart('testpart', '');

        // We're using assertNotContains here because the WHERE clause always
        // contains a test-specific prefix
        self::assertStringNotContainsString(' AND ', $this->subject->getWhereClause());
    }

    /**
     * @test
     */
    public function whereClausePartCanBeSetAndGetsAddedToWhereClause()
    {
        $this->subject->setWhereClausePart('testpart', 'testpart IN (1,2,3)');

        // We're using assertContains here because the WHERE clause always
        // contains a test-specific prefix
        self::assertStringContainsString(' AND testpart IN (1,2,3)', $this->subject->getWhereClause());
    }

    /////////////////////////////////
    // Test concerning limitToTitle
    /////////////////////////////////

    /**
     * @test
     */
    public function limitToTitleFindsRecordWithThatTitle()
    {
        $this->subject->limitToTitle('foo');
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['title' => 'foo']
        );
        $bag = $this->subject->build();

        self::assertEquals(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToTitleIgnoresRecordWithOtherTitle()
    {
        $this->subject->limitToTitle('foo');
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['title' => 'bar']
        );
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    ///////////////////////////////////////////////////
    // Test concerning the combination of limitations
    ///////////////////////////////////////////////////

    /**
     * @test
     */
    public function limitToTitleAndPagesFindsRecordThatMatchesBoth()
    {
        $this->subject->setSourcePages((string)$this->dummySysFolderPid);
        $this->subject->limitToTitle('foo');
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['title' => 'foo', 'pid' => $this->dummySysFolderPid]
        );
        $bag = $this->subject->build();

        self::assertEquals(
            1,
            $bag->count()
        );
    }

    /**
     * @test
     */
    public function limitToTitleAndPagesExcludesRecordThatMatchesOnlyTheTitle()
    {
        $this->subject->setSourcePages((string)$this->dummySysFolderPid);
        $this->subject->limitToTitle('foo');
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['title' => 'foo']
        );
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    /**
     * @test
     */
    public function limitToTitleAndPagesExcludesRecordThatMatchesOnlyThePage()
    {
        $this->subject->setSourcePages((string)$this->dummySysFolderPid);
        $this->subject->limitToTitle('foo');
        $this->testingFramework->createRecord(
            'tx_seminars_test',
            ['title' => 'bar', 'pid' => $this->dummySysFolderPid]
        );
        $bag = $this->subject->build();

        self::assertTrue(
            $bag->isEmpty()
        );
    }

    //////////////////////////////////////////////
    // Tests concerning addAdditionalTableName()
    //////////////////////////////////////////////

    /**
     * @test
     */
    public function addAdditionalTableNameWithEmptyTableNameThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $additionalTableName must not be empty.'
        );

        $this->subject->addAdditionalTableName('');
    }

    /**
     * @test
     */
    public function addAdditionalTableNameWithTableNameAddsAdditionalTableName()
    {
        $this->subject->addAdditionalTableName('tx_seminars_seminars');

        self::assertContains(
            'tx_seminars_seminars',
            $this->subject->getAdditionalTableNames()
        );
    }

    /////////////////////////////////////////////////
    // Tests concerning removeAdditionalTableName()
    /////////////////////////////////////////////////

    /**
     * @test
     */
    public function removeAdditionalTableNameWithEmptyTableNameThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $additionalTableName must not be empty.'
        );

        $this->subject->removeAdditionalTableName('');
    }

    /**
     * @test
     */
    public function removeAdditionalTableNameWithNotSetTableNameThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given additional table name does not exist in the list ' .
            'of additional table names.'
        );

        $this->subject->removeAdditionalTableName('tx_seminars_seminars');
    }

    /**
     * @test
     */
    public function removeAdditionalTableNameWithSetTableNameRemovesAdditionalTableName()
    {
        $this->subject->addAdditionalTableName('tx_seminars_seminars');
        $this->subject->removeAdditionalTableName('tx_seminars_seminars');

        self::assertNotContains(
            'tx_seminars_seminars',
            $this->subject->getAdditionalTableNames()
        );
    }

    //////////////////////////////////
    // Tests concerning setOrderBy()
    //////////////////////////////////

    /**
     * @test
     */
    public function setOrderByWithOrderBySetsOrderBy()
    {
        $this->subject->setOrderBy('field ASC');

        self::assertEquals(
            'field ASC',
            $this->subject->getOrderBy()
        );
    }

    /**
     * @test
     */
    public function setOrderByWithEmptyStringRemovesOrderBy()
    {
        $this->subject->setOrderBy('');

        self::assertEquals(
            '',
            $this->subject->getOrderBy()
        );
    }

    /**
     * @test
     */
    public function setOrderByWithOrderByActuallySortsTheBag()
    {
        $this->subject->setOrderBy('uid DESC');
        $eventUid1 = $this->testingFramework->createRecord('tx_seminars_test');
        $eventUid2 = $this->testingFramework->createRecord('tx_seminars_test');

        $testBag = $this->subject->build();
        self::assertEquals(
            2,
            $testBag->count()
        );

        self::assertEquals(
            $eventUid2,
            $testBag->current()->getUid()
        );
        self::assertEquals(
            $eventUid1,
            $testBag->next()->getUid()
        );
    }

    ////////////////////////////////
    // Tests concerning setLimit()
    ////////////////////////////////

    /**
     * @test
     */
    public function setLimitWithNonEmptyLimitSetsLimit()
    {
        $this->subject->setLimit('0, 30');

        self::assertEquals(
            '0, 30',
            $this->subject->getLimit()
        );
    }

    /**
     * @test
     */
    public function setLimitWithEmptyStringRemovesLimit()
    {
        $this->subject->setLimit('');

        self::assertEquals(
            '',
            $this->subject->getLimit()
        );
    }

    /**
     * @test
     */
    public function setLimitWithLimitActuallyLimitsTheBag()
    {
        $this->testingFramework->createRecord('tx_seminars_test');
        $this->testingFramework->createRecord('tx_seminars_test');
        $this->subject->setLimit('0, 1');
        $bag = $this->subject->build();

        self::assertEquals(
            1,
            $bag->count()
        );
    }

    ///////////////////////////////////
    // Tests concerning setTestMode()
    ///////////////////////////////////

    /**
     * @test
     */
    public function setTestModeAddsTheTableNameBeforeIsDummy()
    {
        self::assertStringContainsString(
            'tx_seminars_test.is_dummy_record = 1',
            $this->subject->getWhereClause()
        );
    }
}
