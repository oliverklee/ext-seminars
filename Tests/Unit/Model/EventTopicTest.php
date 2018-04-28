<?php

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Tests_Unit_Model_EventTopicTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Seminars_Model_Event
     */
    private $fixture;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->fixture = new Tx_Seminars_Model_Event();
    }

    ////////////////////////////////
    // Tests concerning the title.
    ////////////////////////////////

    /**
     * @test
     */
    public function getTitleWithNonEmptyTitleReturnsTitle()
    {
        $this->fixture->setData(['title' => 'Superhero']);

        self::assertSame(
            'Superhero',
            $this->fixture->getTitle()
        );
    }

    /**
     * @test
     */
    public function getRawTitleWithNonEmptyTitleReturnsTitle()
    {
        $this->fixture->setData(['title' => 'Superhero']);

        self::assertSame(
            'Superhero',
            $this->fixture->getRawTitle()
        );
    }

    //////////////////////////////////
    // Tests regarding the subtitle.
    //////////////////////////////////

    /**
     * @test
     */
    public function getSubtitleForEventTopicWithoutSubtitleReturnsAnEmptyString()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertEquals(
            '',
            $this->fixture->getSubtitle()
        );
    }

    /**
     * @test
     */
    public function getSubtitleForEventTopicWithSubtitleReturnsSubtitle()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'subtitle' => 'sub title',
            ]
        );

        self::assertEquals(
            'sub title',
            $this->fixture->getSubtitle()
        );
    }

    /**
     * @test
     */
    public function setSubtitleForEventTopicSetsSubtitle()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setSubtitle('sub title');

        self::assertEquals(
            'sub title',
            $this->fixture->getSubtitle()
        );
    }

    /**
     * @test
     */
    public function hasSubtitleForEventTopicWithoutSubtitleReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->fixture->hasSubtitle()
        );
    }

    /**
     * @test
     */
    public function hasSubtitleForEventTopicWithSubtitleReturnsTrue()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setSubtitle('sub title');

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
    public function getTeaserForEventTopicWithoutTeaserReturnsAnEmptyString()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertEquals(
            '',
            $this->fixture->getTeaser()
        );
    }

    /**
     * @test
     */
    public function getTeaserForEventTopicWithTeaserReturnsTeaser()
    {
        $this->fixture->setData(
            [
                'teaser' => 'wow, this is teasing',
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
            ]
        );

        self::assertEquals(
            'wow, this is teasing',
            $this->fixture->getTeaser()
        );
    }

    /**
     * @test
     */
    public function setTeaserForEventTopicSetsTeaser()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setTeaser('wow, this is teasing');

        self::assertEquals(
            'wow, this is teasing',
            $this->fixture->getTeaser()
        );
    }

    /**
     * @test
     */
    public function hasTeaserForEventTopicWithoutTeaserReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->fixture->hasTeaser()
        );
    }

    /**
     * @test
     */
    public function hasTeaserForEventTopicWithTeaserReturnsTrue()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'teaser' => 'wow, this is teasing',
            ]
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
    public function getDescriptionForEventTopicWithoutDescriptionReturnsAnEmptyString()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertEquals(
            '',
            $this->fixture->getDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionForEventTopicWithDescriptionReturnsDescription()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'description' => 'this is a great event.',
            ]
        );

        self::assertEquals(
            'this is a great event.',
            $this->fixture->getDescription()
        );
    }

    /**
     * @test
     */
    public function setDescriptionForEventTopicSetsDescription()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setDescription('this is a great event.');

        self::assertEquals(
            'this is a great event.',
            $this->fixture->getDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionForEventTopicWithoutDescriptionReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->fixture->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionForEventTopicWithDescriptionReturnsTrue()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setDescription('this is a great event.');

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
    public function getCreditPointsForEventTopicWithoutCreditPointsReturnsZero()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertEquals(
            0,
            $this->fixture->getCreditPoints()
        );
    }

    /**
     * @test
     */
    public function getCreditPointsForEventTopicWithPositiveCreditPointsReturnsCreditPoints()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'credit_points' => 42,
            ]
        );

        self::assertEquals(
            42,
            $this->fixture->getCreditPoints()
        );
    }

    /**
     * @test
     */
    public function setCreditPointsForEventTopicWithZeroCreditPointsSetsCreditPoints()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setCreditPoints(0);

        self::assertEquals(
            0,
            $this->fixture->getCreditPoints()
        );
    }

    /**
     * @test
     */
    public function setCreditPointsForEventTopicWithPositiveCreditPointsSetsCreditPoints()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setCreditPoints(42);

        self::assertEquals(
            42,
            $this->fixture->getCreditPoints()
        );
    }

    /**
     * @test
     */
    public function hasCreditPointsForEventTopicWithoutCreditPointsReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->fixture->hasCreditPoints()
        );
    }

    /**
     * @test
     */
    public function hasCreditPointsForEventTopicWithCreditPointsReturnsTrue()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'credit_points' => 42,
            ]
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
    public function getRegularPriceForEventTopicWithoutRegularPriceReturnsZero()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'price_regular' => 0.00,
            ]
        );

        self::assertEquals(
            0.00,
            $this->fixture->getRegularPrice()
        );
    }

    /**
     * @test
     */
    public function getRegularPriceForEventTopicWithPositiveRegularPriceReturnsRegularPrice()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'price_regular' => 42.42,
            ]
        );

        self::assertEquals(
            42.42,
            $this->fixture->getRegularPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularPriceForEventTopicWithZeroRegularPriceSetsRegularPrice()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setRegularPrice(0.00);

        self::assertEquals(
            0.00,
            $this->fixture->getRegularPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularPriceForEventTopicWithPositiveRegularPriceSetsRegularPrice()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setRegularPrice(42.42);

        self::assertEquals(
            42.42,
            $this->fixture->getRegularPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularPriceForEventTopicWithoutRegularPriceReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->fixture->hasRegularPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularPriceForEventTopicWithRegularPriceReturnsTrue()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'price_regular' => 42.42,
            ]
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
    public function getRegularEarlyBirdPriceForEventTopicWithoutRegularEarlyBirdPriceReturnsZero()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertEquals(
            0.00,
            $this->fixture->getRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function getRegularEarlyBirdPriceForEventTopicWithPositiveRegularEarlyBirdPriceReturnsRegularEarlyBirdPrice()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'price_regular_early' => 42.42,
            ]
        );

        self::assertEquals(
            42.42,
            $this->fixture->getRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularEarlyBirdPriceForEventTopicWithNegativeRegularEarlyBirdPriceThrowsException()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The parameter $price must be >= 0.00.'
        );

        $this->fixture->setRegularEarlyBirdPrice(-1.00);
    }

    /**
     * @test
     */
    public function setRegularEarlyBirdPriceForEventTopicWithZeroRegularEarlyBirdPriceSetsRegularEarlyBirdPrice()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setRegularEarlyBirdPrice(0.00);

        self::assertEquals(
            0.00,
            $this->fixture->getRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularEarlyBirdPriceForEventTopicWithPositiveRegularEarlyBirdPriceSetsRegularEarlyBirdPrice()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setRegularEarlyBirdPrice(42.42);

        self::assertEquals(
            42.42,
            $this->fixture->getRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularEarlyBirdPriceForEventTopicWithoutRegularEarlyBirdPriceReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->fixture->hasRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularEarlyBirdPriceForEventTopicWithRegularEarlyBirdPriceReturnsTrue()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'price_regular_early' => 42.42,
            ]
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
    public function getRegularBoardPriceForEventTopicWithoutRegularBoardPriceReturnsZero()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertEquals(
            0.00,
            $this->fixture->getRegularBoardPrice()
        );
    }

    /**
     * @test
     */
    public function getRegularBoardPriceForEventTopicWithPositiveRegularBoardPriceReturnsRegularBoardPrice()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'price_regular_board' => 42.42,
            ]
        );

        self::assertEquals(
            42.42,
            $this->fixture->getRegularBoardPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularBoardPriceForEventTopicWithZeroRegularBoardPriceSetsRegularBoardPrice()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setRegularBoardPrice(0.00);

        self::assertEquals(
            0.00,
            $this->fixture->getRegularBoardPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularBoardPriceForEventTopicWithPositiveRegularBoardPriceSetsRegularBoardPrice()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setRegularBoardPrice(42.42);

        self::assertEquals(
            42.42,
            $this->fixture->getRegularBoardPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularBoardPriceForEventTopicWithoutRegularBoardPriceReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->fixture->hasRegularBoardPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularBoardPriceForEventTopicWithRegularBoardPriceReturnsTrue()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'price_regular_board' => 42.42,
            ]
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
    public function getSpecialPriceForEventTopicWithoutSpecialPriceReturnsZero()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertEquals(
            0.00,
            $this->fixture->getSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function getSpecialPriceForEventTopicWithSpecialPriceReturnsSpecialPrice()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'price_special' => 42.42,
            ]
        );

        self::assertEquals(
            42.42,
            $this->fixture->getSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialPriceForEventTopicWithZeroSpecialPriceSetsSpecialPrice()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setSpecialPrice(0.00);

        self::assertEquals(
            0.00,
            $this->fixture->getSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialPriceForEventTopicWithPositiveSpecialPriceSetsSpecialPrice()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setSpecialPrice(42.42);

        self::assertEquals(
            42.42,
            $this->fixture->getSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialPriceForEventTopicWithoutSpecialPriceReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->fixture->hasSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialPriceForEventTopicWithSpecialPriceReturnsTrue()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setSpecialPrice(42.42);

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
    public function getSpecialEarlyBirdPriceForEventTopicWithoutSpecialEarlyBirdPriceReturnsZero()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertEquals(
            0.00,
            $this->fixture->getSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function getSpecialEarlyBirdPriceForEventTopicWithPositiveSpecialEarlyBirdPriceReturnsSpecialEarlyBirdPrice()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'price_special_early' => 42.42,
            ]
        );

        self::assertEquals(
            42.42,
            $this->fixture->getSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialEarlyBirdPriceForEventTopicWithZeroSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setSpecialEarlyBirdPrice(0.00);

        self::assertEquals(
            0.00,
            $this->fixture->getSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialEarlyBirdPriceForEventTopicWithPositiveSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setSpecialEarlyBirdPrice(42.42);

        self::assertEquals(
            42.42,
            $this->fixture->getSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialEarlyBirdPriceForEventTopicWithoutSpecialEarlyBirdPriceReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->fixture->hasSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialEarlyBirdPriceForEventTopicWithSpecialEarlyBirdPriceReturnsTrue()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'price_special_early' => 42.42,
            ]
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
    public function getSpecialBoardPriceForEventTopicWithoutSpecialBoardPriceReturnsZero()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertEquals(
            0.00,
            $this->fixture->getSpecialBoardPrice()
        );
    }

    /**
     * @test
     */
    public function getSpecialBoardPriceForEventTopicWithSpecialBoardPriceReturnsSpecialBoardPrice()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'price_special_board' => 42.42,
            ]
        );

        self::assertEquals(
            42.42,
            $this->fixture->getSpecialBoardPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialBoardPriceForEventTopicWithZeroSpecialBoardPriceSetsSpecialBoardPrice()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setSpecialBoardPrice(0.00);

        self::assertEquals(
            0.00,
            $this->fixture->getSpecialBoardPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialBoardPriceForEventTopicWithPositiveSpecialBoardPriceSetsSpecialBoardPrice()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setSpecialBoardPrice(42.42);

        self::assertEquals(
            42.42,
            $this->fixture->getSpecialBoardPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialBoardPriceForEventTopicWithoutSpecialBoardPriceReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->fixture->hasSpecialBoardPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialBoardPriceForEventTopicWithSpecialBoardPriceReturnsTrue()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setSpecialBoardPrice(42.42);

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
    public function getAdditionalInformationForEventTopicWithoutAdditionalInformationReturnsEmptyString()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertEquals(
            '',
            $this->fixture->getAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function getAdditionalInformationForEventTopicWithAdditionalInformationReturnsAdditionalInformation()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'additional_information' => 'this is good to know',
            ]
        );

        self::assertEquals(
            'this is good to know',
            $this->fixture->getAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function setAdditionalInformationForEventTopicSetsAdditionalInformation()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setAdditionalInformation('this is good to know');

        self::assertEquals(
            'this is good to know',
            $this->fixture->getAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function hasAdditionalInformationForEventTopicWithoutAdditionalInformationReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->fixture->hasAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function hasAdditionalInformationForEventTopicWithAdditionalInformationReturnsTrue()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'additional_information' => 'this is good to know',
            ]
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
    public function allowsMultipleRegistrationForEventTopicWithUnsetAllowsMultipleRegistrationReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->fixture->allowsMultipleRegistrations()
        );
    }

    /**
     * @test
     */
    public function allowsMultipleRegistrationForEventTopicWithSetAllowsMultipleRegistrationReturnsTrue()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'allows_multiple_registrations' => true,
            ]
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
    public function usesTerms2ForEventTopicWithUnsetUseTerms2ReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->fixture->usesTerms2()
        );
    }

    /**
     * @test
     */
    public function usesTerms2ForEventTopicWithSetUseTerms2ReturnsTrue()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'use_terms_2' => true,
            ]
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
    public function getNotesForEventTopicWithoutNotesReturnsEmptyString()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertEquals(
            '',
            $this->fixture->getNotes()
        );
    }

    /**
     * @test
     */
    public function getNotesForEventTopicWithNotesReturnsNotes()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'notes' => 'Don\'t forget this.',
            ]
        );

        self::assertEquals(
            'Don\'t forget this.',
            $this->fixture->getNotes()
        );
    }

    /**
     * @test
     */
    public function setNotesForEventTopicSetsNotes()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setNotes('Don\'t forget this.');

        self::assertEquals(
            'Don\'t forget this.',
            $this->fixture->getNotes()
        );
    }

    /**
     * @test
     */
    public function hasNotesForEventTopicWithoutNotesReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->fixture->hasNotes()
        );
    }

    /**
     * @test
     */
    public function hasNotesForEventTopicWithNotesReturnsTrue()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'notes' => 'Don\'t forget this.',
            ]
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
    public function getImageForEventTopicWithoutImageReturnsEmptyString()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertEquals(
            '',
            $this->fixture->getImage()
        );
    }

    /**
     * @test
     */
    public function getImageForEventTopicWithImageReturnsImage()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'image' => 'file.jpg',
            ]
        );

        self::assertEquals(
            'file.jpg',
            $this->fixture->getImage()
        );
    }

    /**
     * @test
     */
    public function setImageForEventTopicSetsImage()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->fixture->setImage('file.jpg');

        self::assertEquals(
            'file.jpg',
            $this->fixture->getImage()
        );
    }

    /**
     * @test
     */
    public function hasImageForEventTopicWithoutImageReturnsFalse()
    {
        $this->fixture->setData(
            ['object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->fixture->hasImage()
        );
    }

    /**
     * @test
     */
    public function hasImageForEventTopicWithImageReturnsTrue()
    {
        $this->fixture->setData(
            [
                'object_type' => Tx_Seminars_Model_Event::TYPE_TOPIC,
                'image' => 'file.jpg',
            ]
        );

        self::assertTrue(
            $this->fixture->hasImage()
        );
    }

    ///////////////////////////////////////
    // Tests concerning hasEarlyBirdPrice
    ///////////////////////////////////////

    /**
     * @test
     */
    public function hasEarlyBirdPriceForNoDeadlineAndAllPricesSetReturnsFalse()
    {
        $this->fixture->setData(
            [
                'deadline_early_bird' => 0,
                'price_regular' => 1.000,
                'price_regular_early' => 1.000,
                'price_special' => 1.000,
                'price_special_early' => 1.000,
            ]
        );

        self::assertFalse(
            $this->fixture->hasEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasEarlyBirdPriceForDeadlineAndAllPricesSetReturnsTrue()
    {
        $this->fixture->setData(
            [
                'deadline_early_bird' => 1234,
                'price_regular' => 1.000,
                'price_regular_early' => 1.000,
                'price_special' => 1.000,
                'price_special_early' => 1.000,
            ]
        );

        self::assertTrue(
            $this->fixture->hasEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasEarlyBirdPriceForDeadlinAndAllRegularPricesSetReturnsTrue()
    {
        $this->fixture->setData(
            [
                'deadline_early_bird' => 1234,
                'price_regular' => 1.000,
                'price_regular_early' => 1.000,
            ]
        );

        self::assertTrue(
            $this->fixture->hasEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasEarlyBirdPriceForDeadlineAndRegularPriceAndAllSpecialPricesSetReturnsFalse()
    {
        $this->fixture->setData(
            [
                'deadline_early_bird' => 1234,
                'price_regular' => 1.000,
                'price_special' => 1.000,
                'price_special_early' => 1.000,
            ]
        );

        self::assertFalse(
            $this->fixture->hasEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasEarlyBirdPriceForDeadlineAndNoRegularPriceAndAllSpecialPricesSetReturnsFalse()
    {
        $this->fixture->setData(
            [
                'deadline_early_bird' => 1234,
                'price_special' => 1.000,
                'price_special_early' => 1.000,
            ]
        );

        self::assertFalse(
            $this->fixture->hasEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasEarlyBirdPriceForDeadlineAndOnlyEarlyBirdPricesSetReturnsFalse()
    {
        $this->fixture->setData(
            [
                'deadline_early_bird' => 1234,
                'price_regular_early' => 1.000,
                'price_special_early' => 1.000,
            ]
        );

        self::assertFalse(
            $this->fixture->hasEarlyBirdPrice()
        );
    }

    /////////////////////////////////////////////
    // Tests concerning isEarlyBirdDeadlineOver
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function isEarlyBirdDeadlineOverForNoEarlyBirdDeadlineReturnsTrue()
    {
        $this->fixture->setData([]);

        self::assertTrue(
            $this->fixture->isEarlyBirdDeadlineOver()
        );
    }

    /**
     * @test
     */
    public function isEarlyBirdDeadlineOverForEarlyBirdDeadlineInPastReturnsTrue()
    {
        $this->fixture->setData(
            ['deadline_early_bird' => $GLOBALS['SIM_EXEC_TIME'] - 1]
        );

        self::assertTrue(
            $this->fixture->isEarlyBirdDeadlineOver()
        );
    }

    /**
     * @test
     */
    public function isEarlyBirdDeadlineOverForEarlyBirdDeadlineNowReturnsTrue()
    {
        $this->fixture->setData(
            ['deadline_early_bird' => $GLOBALS['SIM_EXEC_TIME']]
        );

        self::assertTrue(
            $this->fixture->isEarlyBirdDeadlineOver()
        );
    }

    /**
     * @test
     */
    public function isEarlyBirdDeadlineOverForEarlyBirdDeadlineInFutureReturnsFalse()
    {
        $this->fixture->setData(
            ['deadline_early_bird' => $GLOBALS['SIM_EXEC_TIME'] + 1]
        );

        self::assertFalse(
            $this->fixture->isEarlyBirdDeadlineOver()
        );
    }

    //////////////////////////////////////
    // Tests concerning earlyBirdApplies
    //////////////////////////////////////

    /**
     * @test
     */
    public function earlyBirdAppliesForNoEarlyBirdPriceAndDeadlineOverReturnsFalse()
    {
        /** @var \Tx_Seminars_Model_Event|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_Model_Event::class,
            ['hasEarlyBirdPrice', 'isEarlyBirdDeadlineOver']
        );
        $fixture->expects(self::any())->method('hasEarlyBirdPrice')
            ->will(self::returnValue(false));
        $fixture->expects(self::any())->method('isEarlyBirdDeadlineOver')
            ->will(self::returnValue(true));

        self::assertFalse(
            $fixture->earlyBirdApplies()
        );
    }

    /**
     * @test
     */
    public function earlyBirdAppliesForEarlyBirdPriceAndDeadlineOverReturnsFalse()
    {
        /** @var \Tx_Seminars_Model_Event|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_Model_Event::class,
            ['hasEarlyBirdPrice', 'isEarlyBirdDeadlineOver']
        );
        $fixture->expects(self::any())->method('hasEarlyBirdPrice')
            ->will(self::returnValue(true));
        $fixture->expects(self::any())->method('isEarlyBirdDeadlineOver')
            ->will(self::returnValue(true));

        self::assertFalse(
            $fixture->earlyBirdApplies()
        );
    }

    /**
     * @test
     */
    public function earlyBirdAppliesForEarlyBirdPriceAndDeadlineNotOverReturnsTrue()
    {
        /** @var \Tx_Seminars_Model_Event|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_Model_Event::class,
            ['hasEarlyBirdPrice', 'isEarlyBirdDeadlineOver']
        );
        $fixture->expects(self::any())->method('hasEarlyBirdPrice')
            ->will(self::returnValue(true));
        $fixture->expects(self::any())->method('isEarlyBirdDeadlineOver')
            ->will(self::returnValue(false));

        self::assertTrue(
            $fixture->earlyBirdApplies()
        );
    }

    ////////////////////////////////////////
    // Tests concerning getAvailablePrices
    ////////////////////////////////////////

    /**
     * @test
     */
    public function getAvailablePricesForNoPricesSetAndNoEarlyBirdReturnsZeroRegularPrice()
    {
        /** @var \Tx_Seminars_Model_Event|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_Model_Event::class,
            ['earlyBirdApplies']
        );
        $fixture->expects(self::any())->method('earlyBirdApplies')
            ->will(self::returnValue(false));
        $fixture->setData([]);

        self::assertEquals(
            ['regular' => 0.000],
            $fixture->getAvailablePrices()
        );
    }

    /**
     * @test
     */
    public function getAvailablePricesForRegularPriceSetAndNoEarlyBirdReturnsRegularPrice()
    {
        /** @var \Tx_Seminars_Model_Event|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_Model_Event::class,
            ['earlyBirdApplies']
        );
        $fixture->expects(self::any())->method('earlyBirdApplies')
            ->will(self::returnValue(false));
        $fixture->setData(['price_regular' => 12.345]);

        self::assertEquals(
            ['regular' => 12.345],
            $fixture->getAvailablePrices()
        );
    }

    /**
     * @test
     */
    public function getAvailablePricesForRegularEarlyBirdPriceSetAndEarlyBirdReturnsEarlyBirdPrice()
    {
        /** @var \Tx_Seminars_Model_Event|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_Model_Event::class,
            ['earlyBirdApplies']
        );
        $fixture->expects(self::any())->method('earlyBirdApplies')
            ->will(self::returnValue(true));
        $fixture->setData(
            [
                'price_regular' => 12.345,
                'price_regular_early' => 23.456,
            ]
        );

        self::assertEquals(
            ['regular_early' => 23.456],
            $fixture->getAvailablePrices()
        );
    }

    /**
     * @test
     */
    public function getAvailablePricesForRegularEarlyBirdPriceSetAndNoEarlyBirdReturnsRegularPrice()
    {
        /** @var \Tx_Seminars_Model_Event|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_Model_Event::class,
            ['earlyBirdApplies']
        );
        $fixture->expects(self::any())->method('earlyBirdApplies')
            ->will(self::returnValue(false));
        $fixture->setData(
            [
                'price_regular' => 12.345,
                'price_regular_early' => 23.456,
            ]
        );

        self::assertEquals(
            ['regular' => 12.345],
            $fixture->getAvailablePrices()
        );
    }

    /**
     * @test
     */
    public function getAvailablePricesForRegularBoardPriceSetAndNoEarlyBirdReturnsRegularBoardPrice()
    {
        /** @var \Tx_Seminars_Model_Event|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_Model_Event::class,
            ['earlyBirdApplies']
        );
        $fixture->expects(self::any())->method('earlyBirdApplies')
            ->will(self::returnValue(false));
        $fixture->setData(
            [
                'price_regular_board' => 23.456,
            ]
        );

        self::assertEquals(
            [
                'regular' => 0.000,
                'regular_board' => 23.456,
            ],
            $fixture->getAvailablePrices()
        );
    }

    /**
     * @test
     */
    public function getAvailablePricesForSpecialBoardPriceSetAndNoEarlyBirdReturnsSpecialBoardPrice()
    {
        /** @var \Tx_Seminars_Model_Event|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_Model_Event::class,
            ['earlyBirdApplies']
        );
        $fixture->expects(self::any())->method('earlyBirdApplies')
            ->will(self::returnValue(false));
        $fixture->setData(
            [
                'price_special_board' => 23.456,
            ]
        );

        self::assertEquals(
            [
                'regular' => 0.000,
                'special_board' => 23.456,
            ],
            $fixture->getAvailablePrices()
        );
    }

    /**
     * @test
     */
    public function getAvailablePricesForSpecialPriceSetAndNoEarlyBirdReturnsSpecialPrice()
    {
        /** @var \Tx_Seminars_Model_Event|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_Model_Event::class,
            ['earlyBirdApplies']
        );
        $fixture->expects(self::any())->method('earlyBirdApplies')
            ->will(self::returnValue(false));
        $fixture->setData(['price_special' => 12.345]);

        self::assertEquals(
            [
                'regular' => 0.000,
                'special' => 12.345,
            ],
            $fixture->getAvailablePrices()
        );
    }

    /**
     * @test
     */
    public function getAvailablePricesForSpecialPriceSetAndSpecialEarlyBirdPriceSetAndEarlyBirdReturnsSpecialEarlyBirdPrice()
    {
        /** @var \Tx_Seminars_Model_Event|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_Model_Event::class,
            ['earlyBirdApplies']
        );
        $fixture->expects(self::any())->method('earlyBirdApplies')
            ->will(self::returnValue(true));
        $fixture->setData(
            [
                'price_special' => 34.567,
                'price_special_early' => 23.456,
            ]
        );

        self::assertEquals(
            [
                'regular' => 0.000,
                'special_early' => 23.456,
            ],
            $fixture->getAvailablePrices()
        );
    }

    /**
     * @test
     */
    public function getAvailablePricesForNoSpecialPriceSetAndSpecialEarlyBirdPriceSetAndEarlyBirdNotReturnsSpecialEarlyBirdPrice()
    {
        /** @var \Tx_Seminars_Model_Event|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_Model_Event::class,
            ['earlyBirdApplies']
        );
        $fixture->expects(self::any())->method('earlyBirdApplies')
            ->will(self::returnValue(true));
        $fixture->setData(
            [
                'price_regular' => 34.567,
                'price_special_early' => 23.456,
            ]
        );

        self::assertEquals(
            ['regular' => 34.567],
            $fixture->getAvailablePrices()
        );
    }

    /**
     * @test
     */
    public function getAvailablePricesForSpecialPriceSetAndSpecialEarlyBirdPriceSetAndNoEarlyBirdReturnsSpecialPrice()
    {
        /** @var \Tx_Seminars_Model_Event|\PHPUnit_Framework_MockObject_MockObject $fixture */
        $fixture = $this->getMock(
            Tx_Seminars_Model_Event::class,
            ['earlyBirdApplies']
        );
        $fixture->expects(self::any())->method('earlyBirdApplies')
            ->will(self::returnValue(false));
        $fixture->setData(
            [
                'price_special' => 34.567,
                'price_special_early' => 23.456,
            ]
        );

        self::assertEquals(
            [
                'regular' => 0.000,
                'special' => 34.567,
            ],
            $fixture->getAvailablePrices()
        );
    }

    /////////////////////////////////////////
    // Tests concerning the payment methods
    /////////////////////////////////////////

    /**
     * @test
     */
    public function setPaymentMethodsSetsPaymentMethods()
    {
        $this->fixture->setData([]);

        $paymentMethods = new Tx_Oelib_List();
        $this->fixture->setPaymentMethods($paymentMethods);

        self::assertSame(
            $paymentMethods,
            $this->fixture->getPaymentMethods()
        );
    }
}
