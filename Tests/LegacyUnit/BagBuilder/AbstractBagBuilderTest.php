<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\BagBuilder;

use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Bag\AbstractBag;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\BagBuilder\BrokenBagBuilder;
use OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\BagBuilder\TestingBagBuilder;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class AbstractBagBuilderTest extends TestCase
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

    public function testBuilderThrowsExceptionForEmptyTableName()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The attribute $this->tableName must not be empty.');

        new BrokenBagBuilder();
    }

    public function testBuilderBuildsAnObject()
    {
        $bag = $this->subject->build();

        self::assertIsObject($bag);
    }

    public function testBuilderBuildsABag()
    {
        $bag = $this->subject->build();

        self::assertInstanceOf(AbstractBag::class, $bag);
    }

    public function testBuilderBuildsBagSortedAscendingByUid()
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

    public function testBuilderInitiallyHasNoSourcePages()
    {
        self::assertFalse(
            $this->subject->hasSourcePages()
        );
    }

    public function testBuilderHasSourcePagesWithOnePage()
    {
        $this->subject->setSourcePages((string)$this->dummySysFolderPid);

        self::assertTrue(
            $this->subject->hasSourcePages()
        );
    }

    public function testBuilderHasSourcePagesWithTwoPages()
    {
        $this->subject->setSourcePages($this->dummySysFolderPid . ',' . ($this->dummySysFolderPid + 1));

        self::assertTrue(
            $this->subject->hasSourcePages()
        );
    }

    public function testBuilderHasNoSourcePagesWithEvilSql()
    {
        $this->subject->setSourcePages('; DROP TABLE tx_seminars_test;');

        self::assertFalse(
            $this->subject->hasSourcePages()
        );
    }

    public function testBuilderSelectsRecordsFromAllPagesByDefault()
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

    public function testBuilderSelectsRecordsFromAllPagesWithEmptySourcePages()
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

    public function testBuilderSelectsRecordsFromAllPagesWithEmptyAfterNonEmptySourcePages()
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

    public function testBuilderSelectsRecordsFromAllPagesWithEmptySourcePagesAndZeroRecursion()
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

    public function testBuilderSelectsRecordsFromAllPagesWithEmptySourcePagesAndNonZeroRecursion()
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

    public function testBuilderSelectsRecordsFromOnePage()
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

    public function testBuilderSelectsRecordsFromTwoPages()
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

    public function testBuilderIgnoresRecordsOnSubpageWithoutRecursion()
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

    public function testBuilderSelectsRecordsOnSubpageWithRecursion()
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

    public function testBuilderSelectsRecordsOnTwoSubpagesWithRecursion()
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

    public function testBuilderSelectsRecordsOnSubpageFromTwoParentsWithRecursion()
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

    public function testBuilderIgnoresRecordsOnSubpageWithTooShallowRecursion()
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

    public function testWhereClauseInitiallyIsNotEmpty()
    {
        self::assertNotEquals(
            '',
            $this->subject->getWhereClause()
        );
    }

    public function testWhereClauseCanSelectPids()
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

    public function testLimitToTitleFindsRecordWithThatTitle()
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

    public function testLimitToTitleIgnoresRecordWithOtherTitle()
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

    public function testLimitToTitleAndPagesFindsRecordThatMatchesBoth()
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

    public function testLimitToTitleAndPagesExcludesRecordThatMatchesOnlyTheTitle()
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

    public function testLimitToTitleAndPagesExcludesRecordThatMatchesOnlyThePage()
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

    public function testAddAdditionalTableNameWithEmptyTableNameThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $additionalTableName must not be empty.'
        );

        $this->subject->addAdditionalTableName('');
    }

    public function testAddAdditionalTableNameWithTableNameAddsAdditionalTableName()
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

    public function testRemoveAdditionalTableNameWithEmptyTableNameThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $additionalTableName must not be empty.'
        );

        $this->subject->removeAdditionalTableName('');
    }

    public function testRemoveAdditionalTableNameWithNotSetTableNameThrowsException()
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

    public function testRemoveAdditionalTableNameWithSetTableNameRemovesAdditionalTableName()
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

    public function testSetOrderByWithOrderBySetsOrderBy()
    {
        $this->subject->setOrderBy('field ASC');

        self::assertEquals(
            'field ASC',
            $this->subject->getOrderBy()
        );
    }

    public function testSetOrderByWithEmptyStringRemovesOrderBy()
    {
        $this->subject->setOrderBy('');

        self::assertEquals(
            '',
            $this->subject->getOrderBy()
        );
    }

    public function testSetOrderByWithOrderByActuallySortsTheBag()
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

    public function testSetLimitWithNonEmptyLimitSetsLimit()
    {
        $this->subject->setLimit('0, 30');

        self::assertEquals(
            '0, 30',
            $this->subject->getLimit()
        );
    }

    public function testSetLimitWithEmptyStringRemovesLimit()
    {
        $this->subject->setLimit('');

        self::assertEquals(
            '',
            $this->subject->getLimit()
        );
    }

    public function testSetLimitWithLimitActuallyLimitsTheBag()
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

    public function testSetTestModeAddsTheTableNameBeforeIsDummy()
    {
        self::assertStringContainsString(
            'tx_seminars_test.is_dummy_record = 1',
            $this->subject->getWhereClause()
        );
    }
}
