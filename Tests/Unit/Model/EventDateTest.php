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
 * This test case holds all tests specific to event dates.
 *
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Model_EventDateTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_Model_Event
     */
    private $fixture;

    protected function setUp()
    {
        $this->fixture = new Tx_Seminars_Model_Event();
    }

    ////////////////////////////////
    // Tests concerning the title.
    ////////////////////////////////

    /**
     * @test
     */
    public function getTitleWithNonEmptyTopicTitleReturnsTopicTitle()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('title' => 'Superhero'));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
                'title' => 'Supervillain',
            )
        );

        self::assertSame(
            'Superhero',
            $this->fixture->getTitle()
        );
    }

    /**
     * @test
     */
    public function getRawTitleWithNonEmptyTopicTitleReturnsDateTitle()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('title' => 'Superhero'));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
                'title' => 'Supervillain',
            )
        );

        self::assertSame(
            'Supervillain',
            $this->fixture->getRawTitle()
        );
    }

    //////////////////////////////////
    // Tests regarding the subtitle.
    //////////////////////////////////

    /**
     * @test
     */
    public function getSubtitleForEventDateWithoutSubtitleReturnsAnEmptyString()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            '',
            $this->fixture->getSubtitle()
        );
    }

    /**
     * @test
     */
    public function getSubtitleForEventDateWithSubtitleReturnsSubtitle()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('subtitle' => 'sub title'));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            'sub title',
            $this->fixture->getSubtitle()
        );
    }

    /**
     * @test
     */
    public function setSubtitleForEventDateSetsSubtitle()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setSubtitle('sub title');

        self::assertEquals(
            'sub title',
            $topic->getSubtitle()
        );
    }

    /**
     * @test
     */
    public function hasSubtitleForEventDateWithoutSubtitleReturnsFalse()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertFalse(
            $this->fixture->hasSubtitle()
        );
    }

    /**
     * @test
     */
    public function hasSubtitleForEventDateWithSubtitleReturnsTrue()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('subtitle' => 'sub title'));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertTrue(
            $this->fixture->hasSubtitle()
        );
    }

    ////////////////////////////////
    // Tests regarding the teaser.
    ////////////////////////////////

    /**
     * @test
     */
    public function getTeaserForEventDateWithoutTeaserReturnsAnEmptyString()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            '',
            $this->fixture->getTeaser()
        );
    }

    /**
     * @test
     */
    public function getTeaserForEventDateWithTeaserReturnsTeaser()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('teaser' => 'wow, this is teasing'));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            'wow, this is teasing',
            $this->fixture->getTeaser()
        );
    }

    /**
     * @test
     */
    public function setTeaserForEventDateSetsTeaser()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setTeaser('wow, this is teasing');

        self::assertEquals(
            'wow, this is teasing',
            $topic->getTeaser()
        );
    }

    /**
     * @test
     */
    public function hasTeaserForEventDateWithoutTeaserReturnsFalse()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertFalse(
            $this->fixture->hasTeaser()
        );
    }

    /**
     * @test
     */
    public function hasTeaserForEventDateWithTeaserReturnsTrue()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('teaser' => 'wow, this is teasing'));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertTrue(
            $this->fixture->hasTeaser()
        );
    }

    /////////////////////////////////////
    // Tests regarding the description.
    /////////////////////////////////////

    /**
     * @test
     */
    public function getDescriptionForEventDateWithoutDescriptionReturnsAnEmptyString()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            '',
            $this->fixture->getDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionForEventDateWithDescriptionReturnsDescription()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(
                array('description' => 'this is a great event.')
            );
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            'this is a great event.',
            $this->fixture->getDescription()
        );
    }

    /**
     * @test
     */
    public function setDescriptionForEventDateSetsDescription()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setDescription('this is a great event.');

        self::assertEquals(
            'this is a great event.',
            $topic->getDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionForEventDateWithoutDescriptionReturnsFalse()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertFalse(
            $this->fixture->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionForEventDateWithDescriptionReturnsTrue()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(
                array('description' => 'this is a great event.')
            );
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertTrue(
            $this->fixture->hasDescription()
        );
    }

    ///////////////////////////////////////
    // Tests regarding the credit points.
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getCreditPointsForEventDateWithoutCreditPointsReturnsZero()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            0,
            $this->fixture->getCreditPoints()
        );
    }

    /**
     * @test
     */
    public function getCreditPointsForEventDateWithPositiveCreditPointsReturnsCreditPoints()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('credit_points' => 42));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            42,
            $this->fixture->getCreditPoints()
        );
    }

    /**
     * @test
     */
    public function setCreditPointsForEventDateWithZeroCreditPointsSetsCreditPoints()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setCreditPoints(0);

        self::assertEquals(
            0,
            $topic->getCreditPoints()
        );
    }

    /**
     * @test
     */
    public function setCreditPointsForEventDateWithPositiveCreditPointsSetsCreditPoints()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setCreditPoints(42);

        self::assertEquals(
            42,
            $topic->getCreditPoints()
        );
    }

    /**
     * @test
     */
    public function hasCreditPointsForEventDateWithoutCreditPointsReturnsFalse()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertFalse(
            $this->fixture->hasCreditPoints()
        );
    }

    /**
     * @test
     */
    public function hasCreditPointsForEventDateWithCreditPointsReturnsTrue()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('credit_points' => 42));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertTrue(
            $this->fixture->hasCreditPoints()
        );
    }

    ///////////////////////////////////////
    // Tests regarding the regular price.
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getRegularPriceForEventDateWithoutRegularPriceReturnsZero()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('price_regular' => 0.00));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            0.00,
            $this->fixture->getRegularPrice()
        );
    }

    /**
     * @test
     */
    public function getRegularPriceForEventDateWithPositiveRegularPriceReturnsRegularPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('price_regular' => 42.42));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            42.42,
            $this->fixture->getRegularPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularPriceForEventDateWithZeroRegularPriceSetsRegularPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setRegularPrice(0.00);

        self::assertEquals(
            0.00,
            $topic->getRegularPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularPriceForEventDateWithPositiveRegularPriceSetsRegularPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setRegularPrice(42.42);

        self::assertEquals(
            42.42,
            $topic->getRegularPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularPriceForEventDateWithoutRegularPriceReturnsFalse()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertFalse(
            $this->fixture->hasRegularPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularPriceForEventDateWithRegularPriceReturnsTrue()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('price_regular' => 42.42));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertTrue(
            $this->fixture->hasRegularPrice()
        );
    }

    //////////////////////////////////////////////////
    // Tests regarding the regular early bird price.
    //////////////////////////////////////////////////

    /**
     * @test
     */
    public function getRegularEarlyBirdPriceForEventDateWithoutRegularEarlyBirdPriceReturnsZero()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            0.00,
            $this->fixture->getRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function getRegularEarlyBirdPriceForEventDateWithPositiveRegularEarlyBirdPriceReturnsRegularEarlyBirdPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('price_regular_early' => 42.42));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            42.42,
            $this->fixture->getRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularEarlyBirdPriceForEventDateWithZeroRegularEarlyBirdPriceSetsRegularEarlyBirdPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setRegularEarlyBirdPrice(0.00);

        self::assertEquals(
            0.00,
            $topic->getRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularEarlyBirdPriceForEventDateWithPositiveRegularEarlyBirdPriceSetsRegularEarlyBirdPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setRegularEarlyBirdPrice(42.42);

        self::assertEquals(
            42.42,
            $topic->getRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularEarlyBirdPriceForEventDateWithoutRegularEarlyBirdPriceReturnsFalse()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertFalse(
            $this->fixture->hasRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularEarlyBirdPriceForEventDateWithRegularEarlyBirdPriceReturnsTrue()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('price_regular_early' => 42.42));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertTrue(
            $this->fixture->hasRegularEarlyBirdPrice()
        );
    }

    /////////////////////////////////////////////
    // Tests regarding the regular board price.
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function getRegularBoardPriceForEventDateWithoutRegularBoardPriceReturnsZero()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            0.00,
            $this->fixture->getRegularBoardPrice()
        );
    }

    /**
     * @test
     */
    public function getRegularBoardPriceForEventDateWithPositiveRegularBoardPriceReturnsRegularBoardPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('price_regular_board' => 42.42));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            42.42,
            $this->fixture->getRegularBoardPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularBoardPriceForEventDateWithZeroRegularBoardPriceSetsRegularBoardPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setRegularBoardPrice(0.00);

        self::assertEquals(
            0.00,
            $topic->getRegularBoardPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularBoardPriceForEventDateWithPositiveRegularBoardPriceSetsRegularBoardPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setRegularBoardPrice(42.42);

        self::assertEquals(
            42.42,
            $topic->getRegularBoardPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularBoardPriceForEventDateWithoutRegularBoardPriceReturnsFalse()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertFalse(
            $this->fixture->hasRegularBoardPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularBoardPriceForEventDateWithRegularBoardPriceReturnsTrue()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('price_regular_board' => 42.42));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertTrue(
            $this->fixture->hasRegularBoardPrice()
        );
    }

    ///////////////////////////////////////
    // Tests regarding the special price.
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getSpecialPriceForEventDateWithoutSpecialPriceReturnsZero()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            0.00,
            $this->fixture->getSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function getSpecialPriceForEventDateWithSpecialPriceReturnsSpecialPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('price_special' => 42.42));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            42.42,
            $this->fixture->getSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialPriceForEventDateWithZeroSpecialPriceSetsSpecialPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setSpecialPrice(0.00);

        self::assertEquals(
            0.00,
            $topic->getSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialPriceForEventDateWithPositiveSpecialPriceSetsSpecialPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setSpecialPrice(42.42);

        self::assertEquals(
            42.42,
            $topic->getSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialPriceForEventDateWithoutSpecialPriceReturnsFalse()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertFalse(
            $this->fixture->hasSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialPriceForEventDateWithSpecialPriceReturnsTrue()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('price_special' => 42.42));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertTrue(
            $this->fixture->hasSpecialPrice()
        );
    }

    //////////////////////////////////////////////////
    // Tests regarding the special early bird price.
    //////////////////////////////////////////////////

    /**
     * @test
     */
    public function getSpecialEarlyBirdPriceForEventDateWithoutSpecialEarlyBirdPriceReturnsZero()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            0.00,
            $this->fixture->getSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function getSpecialEarlyBirdPriceForEventDateWithPositiveSpecialEarlyBirdPriceReturnsSpecialEarlyBirdPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('price_special_early' => 42.42));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            42.42,
            $this->fixture->getSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialEarlyBirdPriceForEventDateWithZeroSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setSpecialEarlyBirdPrice(0.00);

        self::assertEquals(
            0.00,
            $topic->getSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialEarlyBirdPriceForEventDateWithPositiveSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setSpecialEarlyBirdPrice(42.42);

        self::assertEquals(
            42.42,
            $topic->getSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialEarlyBirdPriceForEventDateWithoutSpecialEarlyBirdPriceReturnsFalse()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertFalse(
            $this->fixture->hasSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialEarlyBirdPriceForEventDateWithSpecialEarlyBirdPriceReturnsTrue()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('price_special_early' => 42.42));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertTrue(
            $this->fixture->hasSpecialEarlyBirdPrice()
        );
    }

    /////////////////////////////////////////////
    // Tests regarding the special board price.
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function getSpecialBoardPriceForEventDateWithoutSpecialBoardPriceReturnsZero()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            0.00,
            $this->fixture->getSpecialBoardPrice()
        );
    }

    /**
     * @test
     */
    public function getSpecialBoardPriceForEventDateWithSpecialBoardPriceReturnsSpecialBoardPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('price_special_board' => 42.42));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            42.42,
            $this->fixture->getSpecialBoardPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialBoardPriceForEventDateWithZeroSpecialBoardPriceSetsSpecialBoardPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setSpecialBoardPrice(0.00);

        self::assertEquals(
            0.00,
            $topic->getSpecialBoardPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialBoardPriceForEventDateWithPositiveSpecialBoardPriceSetsSpecialBoardPrice()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setSpecialBoardPrice(42.42);

        self::assertEquals(
            42.42,
            $topic->getSpecialBoardPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialBoardPriceForEventDateWithoutSpecialBoardPriceReturnsFalse()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertFalse(
            $this->fixture->hasSpecialBoardPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialBoardPriceForEventDateWithSpecialBoardPriceReturnsTrue()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('price_special_board' => 42.42));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertTrue(
            $this->fixture->hasSpecialBoardPrice()
        );
    }

    ////////////////////////////////////////////////
    // Tests regarding the additional information.
    ////////////////////////////////////////////////

    /**
     * @test
     */
    public function getAdditionalInformationForEventDateWithoutAdditionalInformationReturnsEmptyString()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            '',
            $this->fixture->getAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function getAdditionalInformationForEventDateWithAdditionalInformationReturnsAdditionalInformation()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(
                array('additional_information' => 'this is good to know')
            );
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            'this is good to know',
            $this->fixture->getAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function setAdditionalInformationForEventDateSetsAdditionalInformation()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setAdditionalInformation('this is good to know');

        self::assertEquals(
            'this is good to know',
            $topic->getAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function hasAdditionalInformationForEventDateWithoutAdditionalInformationReturnsFalse()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertFalse(
            $this->fixture->hasAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function hasAdditionalInformationForEventDateWithAdditionalInformationReturnsTrue()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(
                array('additional_information' => 'this is good to know')
            );
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertTrue(
            $this->fixture->hasAdditionalInformation()
        );
    }

    //////////////////////////////////////////////////
    // Tests regarding allowsMultipleRegistration().
    //////////////////////////////////////////////////

    /**
     * @test
     */
    public function allowsMultipleRegistrationForEventDateWithUnsetAllowsMultipleRegistrationReturnsFalse()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertFalse(
            $this->fixture->allowsMultipleRegistrations()
        );
    }

    /**
     * @test
     */
    public function allowsMultipleRegistrationForEventDateWithSetAllowsMultipleRegistrationReturnsTrue()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(
                array('allows_multiple_registrations' => true)
            );
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertTrue(
            $this->fixture->allowsMultipleRegistrations()
        );
    }

    //////////////////////////////////
    // Tests regarding usesTerms2().
    //////////////////////////////////

    /**
     * @test
     */
    public function usesTerms2ForEventDateWithUnsetUseTerms2ReturnsFalse()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertFalse(
            $this->fixture->usesTerms2()
        );
    }

    /**
     * @test
     */
    public function usesTerms2ForEventDateWithSetUseTerms2ReturnsTrue()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('use_terms_2' => true));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertTrue(
            $this->fixture->usesTerms2()
        );
    }

    ///////////////////////////////
    // Tests regarding the notes.
    ///////////////////////////////

    /**
     * @test
     */
    public function getNotesForEventDateWithoutNotesReturnsEmptyString()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            '',
            $this->fixture->getNotes()
        );
    }

    /**
     * @test
     */
    public function getNotesForEventDateWithNotesReturnsNotes()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('notes' => 'Don\'t forget this.'));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            'Don\'t forget this.',
            $this->fixture->getNotes()
        );
    }

    /**
     * @test
     */
    public function setNotesForEventDateSetsNotes()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setNotes('Don\'t forget this.');

        self::assertEquals(
            'Don\'t forget this.',
            $topic->getNotes()
        );
    }

    /**
     * @test
     */
    public function hasNotesForEventDateWithoutNotesReturnsFalse()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertFalse(
            $this->fixture->hasNotes()
        );
    }

    /**
     * @test
     */
    public function hasNotesForEventDateWithNotesReturnsTrue()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('notes' => 'Don\'t forget this.'));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertTrue(
            $this->fixture->hasNotes()
        );
    }

    ///////////////////////////////
    // Tests regarding the image.
    ///////////////////////////////

    /**
     * @test
     */
    public function getImageForEventDateWithoutImageReturnsEmptyString()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            '',
            $this->fixture->getImage()
        );
    }

    /**
     * @test
     */
    public function getImageForEventDateWithImageReturnsImage()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('image' => 'file.jpg'));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertEquals(
            'file.jpg',
            $this->fixture->getImage()
        );
    }

    /**
     * @test
     */
    public function setImageForEventDateSetsImage()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );
        $this->fixture->setImage('file.jpg');

        self::assertEquals(
            'file.jpg',
            $topic->getImage()
        );
    }

    /**
     * @test
     */
    public function hasImageForEventDateWithoutImageReturnsFalse()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array());
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertFalse(
            $this->fixture->hasImage()
        );
    }

    /**
     * @test
     */
    public function hasImageForEventDateWithImageReturnsTrue()
    {
        $topic = Tx_Oelib_MapperRegistry::get(Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(array('image' => 'file.jpg'));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertTrue(
            $this->fixture->hasImage()
        );
    }

    /////////////////////////////////////////
    // Tests concerning the payment methods
    /////////////////////////////////////////

    /**
     * @test
     */
    public function getPaymentMethodsReturnsPaymentMethodsFromTopic()
    {
        $paymentMethods = new Tx_Oelib_List();
        $topic = new Tx_Seminars_Model_Event();
        $topic->setData(array('payment_methods' => $paymentMethods));
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        self::assertSame(
            $paymentMethods,
            $this->fixture->getPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function setPaymentMethodsThrowsException()
    {
        $this->setExpectedException(
            'BadMethodCallException',
            'setPaymentMethods may only be called on single events and event ' .
                'topics, but not on event dates.'
        );

        $topic = new Tx_Seminars_Model_Event();
        $this->fixture->setData(
            array(
                'object_type' => Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            )
        );

        $this->fixture->setPaymentMethods(new Tx_Oelib_List());
    }
}
