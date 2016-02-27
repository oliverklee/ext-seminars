<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case.
 *
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Unit_ViewHelper_CommaSeparatedTitlesTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_ViewHelper_CommaSeparatedTitles
     */
    private $fixture;

    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * @var Tx_Oelib_List
     */
    private $list;

    /**
     * @var string
     */
    const TIME_FORMAT = '%H:%M';

    protected function setUp()
    {
        $this->testingFramework    = new Tx_Oelib_TestingFramework('tx_seminars');
        $this->list = new Tx_Oelib_List();
        $this->fixture = new Tx_Seminars_ViewHelper_CommaSeparatedTitles();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    /**
     * @test
     */
    public function renderWithEmptyListReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->fixture->render($this->list)
        );
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage All elements in $list must implement the interface Tx_Seminars_Interface_Titled.
     * @expectedExceptionCode 1333658899
     */
    public function renderWithElementsInListWithoutGetTitleMethodThrowsBadMethodCallException()
    {
        $model = new Tx_Seminars_Tests_Unit_Fixtures_Model_UntitledTestingModel();
        $model->setData(array());

        $this->list->add($model);

        $this->fixture->render($this->list);
    }

    /**
     * @test
     */
    public function renderWithOneElementListReturnsOneElementsTitle()
    {
        $model = new Tx_Seminars_Tests_Unit_Fixtures_Model_TitledTestingModel();
        $model->setData(array('title' => 'Testing model'));

        $this->list->add($model);

        self::assertSame(
            $model->getTitle(),
            $this->fixture->render($this->list)
        );
    }

    /**
     * @test
     */
    public function renderWithTwoElementsListReturnsTwoElementTitlesSeparatedByComma()
    {
        $firstModel = new Tx_Seminars_Tests_Unit_Fixtures_Model_TitledTestingModel();
        $firstModel->setData(array('title' => 'First testing model'));
        $secondModel = new Tx_Seminars_Tests_Unit_Fixtures_Model_TitledTestingModel();
        $secondModel->setData(array('title' => 'Second testing model'));

        $this->list->add($firstModel);
        $this->list->add($secondModel);

        self::assertSame(
            $firstModel->getTitle() . ', ' . $secondModel->getTitle(),
            $this->fixture->render($this->list)
        );
    }

    /**
     * @test
     */
    public function renderWithOneElementListReturnsOneElementsTitleHtmlspecialchared()
    {
        $model = new Tx_Seminars_Tests_Unit_Fixtures_Model_TitledTestingModel();
        $model->setData(array('title' => '<test>Testing model</test>'));

        $this->list->add($model);

        self::assertSame(
            htmlspecialchars($model->getTitle()),
            $this->fixture->render($this->list)
        );
    }
}
