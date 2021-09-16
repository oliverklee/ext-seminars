<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\PhpUnit\TestCase;

/**
 * This test case holds all tests specific to single events.
 */
class SingleEventTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_Event
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Model_Event();
    }

    //////////////////////////////////
    // Tests regarding the subtitle.
    //////////////////////////////////

    /**
     * @test
     */
    public function getSubtitleForSingleEventWithoutSubtitleReturnsAnEmptyString()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertEquals(
            '',
            $this->subject->getSubtitle()
        );
    }

    /**
     * @test
     */
    public function getSubtitleForSingleEventWithSubtitleReturnsSubtitle()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'subtitle' => 'sub title',
            ]
        );

        self::assertEquals(
            'sub title',
            $this->subject->getSubtitle()
        );
    }

    /**
     * @test
     */
    public function setSubtitleForSingleEventSetsSubtitle()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setSubtitle('sub title');

        self::assertEquals(
            'sub title',
            $this->subject->getSubtitle()
        );
    }

    /**
     * @test
     */
    public function hasSubtitleForSingleEventWithoutSubtitleReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );

        self::assertFalse(
            $this->subject->hasSubtitle()
        );
    }

    /**
     * @test
     */
    public function hasSubtitleForSingleEventWithSubtitleReturnsTrue()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_TOPIC]
        );
        $this->subject->setSubtitle('sub title');

        self::assertTrue(
            $this->subject->hasSubtitle()
        );
    }

    ////////////////////////////////
    // Tests regarding the teaser.
    ////////////////////////////////

    /**
     * @test
     */
    public function getTeaserForSingleEventWithoutTeaserReturnsAnEmptyString()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertEquals(
            '',
            $this->subject->getTeaser()
        );
    }

    /**
     * @test
     */
    public function getTeaserForSingleEventWithTeaserReturnsTeaser()
    {
        $this->subject->setData(
            [
                'teaser' => 'wow, this is teasing',
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
            ]
        );

        self::assertEquals(
            'wow, this is teasing',
            $this->subject->getTeaser()
        );
    }

    /**
     * @test
     */
    public function setTeaserForSingleEventSetsTeaser()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setTeaser('wow, this is teasing');

        self::assertEquals(
            'wow, this is teasing',
            $this->subject->getTeaser()
        );
    }

    /**
     * @test
     */
    public function hasTeaserForSingleEventWithoutTeaserReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertFalse(
            $this->subject->hasTeaser()
        );
    }

    /**
     * @test
     */
    public function hasTeaserForSingleEventWithTeaserReturnsTrue()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'teaser' => 'wow, this is teasing',
            ]
        );

        self::assertTrue(
            $this->subject->hasTeaser()
        );
    }

    /////////////////////////////////////
    // Tests regarding the description.
    /////////////////////////////////////

    /**
     * @test
     */
    public function getDescriptionForSingleEventWithoutDescriptionReturnsAnEmptyString()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertEquals(
            '',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionForSingleEventWithDescriptionReturnsDescription()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'description' => 'this is a great event.',
            ]
        );

        self::assertEquals(
            'this is a great event.',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function setDescriptionForSingleEventSetsDescription()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setDescription('this is a great event.');

        self::assertEquals(
            'this is a great event.',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionForSingleEventWithoutDescriptionReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertFalse(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionForSingleEventWithDescriptionReturnsTrue()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'description' => 'this is a great event.',
            ]
        );

        self::assertTrue(
            $this->subject->hasDescription()
        );
    }

    ///////////////////////////////////////
    // Tests regarding the credit points.
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getCreditPointsForSingleEventWithoutCreditPointsReturnsZero()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertEquals(
            0,
            $this->subject->getCreditPoints()
        );
    }

    /**
     * @test
     */
    public function getCreditPointsForSingleEventWithPositiveCreditPointsReturnsCreditPoints()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'credit_points' => 42,
            ]
        );

        self::assertEquals(
            42,
            $this->subject->getCreditPoints()
        );
    }

    /**
     * @test
     */
    public function setCreditPointsForSingleEventWithNegativeCreditPointsThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $creditPoints must be >= 0.'
        );
        $this->subject->setData([]);

        $this->subject->setCreditPoints(-1);
    }

    /**
     * @test
     */
    public function setCreditPointsForSingleEventWithZeroCreditPointsSetsCreditPoints()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setCreditPoints(0);

        self::assertEquals(
            0,
            $this->subject->getCreditPoints()
        );
    }

    /**
     * @test
     */
    public function setCreditPointsForSingleEventWithPositiveCreditPointsSetsCreditPoints()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setCreditPoints(42);

        self::assertEquals(
            42,
            $this->subject->getCreditPoints()
        );
    }

    /**
     * @test
     */
    public function hasCreditPointsForSingleEventWithoutCreditPointsReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertFalse(
            $this->subject->hasCreditPoints()
        );
    }

    /**
     * @test
     */
    public function hasCreditPointsForSingleEventWithCreditPointsReturnsTrue()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'credit_points' => 42,
            ]
        );

        self::assertTrue(
            $this->subject->hasCreditPoints()
        );
    }

    ///////////////////////////////////////
    // Tests regarding the regular price.
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getRegularPriceForSingleEventWithoutRegularPriceReturnsZero()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'price_regular' => 0.00,
            ]
        );

        self::assertEquals(
            0.00,
            $this->subject->getRegularPrice()
        );
    }

    /**
     * @test
     */
    public function getRegularPriceForSingleEventWithPositiveRegularPriceReturnsRegularPrice()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'price_regular' => 42.42,
            ]
        );

        self::assertEquals(
            42.42,
            $this->subject->getRegularPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularPriceForSingleEventWithNegativeRegularPriceThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $price must be >= 0.00.'
        );

        $this->subject->setRegularPrice(-1);
    }

    /**
     * @test
     */
    public function setRegularPriceForSingleEventWithZeroRegularPriceSetsRegularPrice()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setRegularPrice(0.00);

        self::assertEquals(
            0.00,
            $this->subject->getRegularPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularPriceForSingleEventWithPositiveRegularPriceSetsRegularPrice()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setRegularPrice(42.42);

        self::assertEquals(
            42.42,
            $this->subject->getRegularPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularPriceForSingleEventWithoutRegularPriceReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertFalse(
            $this->subject->hasRegularPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularPriceForSingleEventWithRegularPriceReturnsTrue()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'price_regular' => 42.42,
            ]
        );

        self::assertTrue(
            $this->subject->hasRegularPrice()
        );
    }

    //////////////////////////////////////////////////
    // Tests regarding the regular early bird price.
    //////////////////////////////////////////////////

    /**
     * @test
     */
    public function getRegularEarlyBirdPriceForSingleEventWithoutRegularEarlyBirdPriceReturnsZero()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertEquals(
            0.00,
            $this->subject->getRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function getRegularEarlyBirdPriceForSingleEventWithPositiveRegularEarlyBirdPriceReturnsRegularEarlyBirdPrice()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'price_regular_early' => 42.42,
            ]
        );

        self::assertEquals(
            42.42,
            $this->subject->getRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularEarlyBirdPriceForSingleEventWithNegativeRegularEarlyBirdPriceThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $price must be >= 0.00.'
        );

        $this->subject->setRegularEarlyBirdPrice(-1.00);
    }

    /**
     * @test
     */
    public function setRegularEarlyBirdPriceForSingleEventWithZeroRegularEarlyBirdPriceSetsRegularEarlyBirdPrice()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setRegularEarlyBirdPrice(0.00);

        self::assertEquals(
            0.00,
            $this->subject->getRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularEarlyBirdPriceForSingleEventWithPositiveRegularEarlyBirdPriceSetsRegularEarlyBirdPrice()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setRegularEarlyBirdPrice(42.42);

        self::assertEquals(
            42.42,
            $this->subject->getRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularEarlyBirdPriceForSingleEventWithoutRegularEarlyBirdPriceReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertFalse(
            $this->subject->hasRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularEarlyBirdPriceForSingleEventWithRegularEarlyBirdPriceReturnsTrue()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'price_regular_early' => 42.42,
            ]
        );

        self::assertTrue(
            $this->subject->hasRegularEarlyBirdPrice()
        );
    }

    /////////////////////////////////////////////
    // Tests regarding the regular board price.
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function getRegularBoardPriceForSingleEventWithoutRegularBoardPriceReturnsZero()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertEquals(
            0.00,
            $this->subject->getRegularBoardPrice()
        );
    }

    /**
     * @test
     */
    public function getRegularBoardPriceForSingleEventWithPositiveRegularBoardPriceReturnsRegularBoardPrice()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'price_regular_board' => 42.42,
            ]
        );

        self::assertEquals(
            42.42,
            $this->subject->getRegularBoardPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularBoardPriceForSingleEventWithNegativeRegularBoardPriceThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $price must be >= 0.00.'
        );

        $this->subject->setRegularBoardPrice(-1.00);
    }

    /**
     * @test
     */
    public function setRegularBoardPriceForSingleEventWithZeroRegularBoardPriceSetsRegularBoardPrice()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setRegularBoardPrice(0.00);

        self::assertEquals(
            0.00,
            $this->subject->getRegularBoardPrice()
        );
    }

    /**
     * @test
     */
    public function setRegularBoardPriceForSingleEventWithPositiveRegularBoardPriceSetsRegularBoardPrice()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setRegularBoardPrice(42.42);

        self::assertEquals(
            42.42,
            $this->subject->getRegularBoardPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularBoardPriceForSingleEventWithoutRegularBoardPriceReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertFalse(
            $this->subject->hasRegularBoardPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularBoardPriceForSingleEventWithRegularBoardPriceReturnsTrue()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'price_regular_board' => 42.42,
            ]
        );

        self::assertTrue(
            $this->subject->hasRegularBoardPrice()
        );
    }

    ///////////////////////////////////////
    // Tests regarding the special price.
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getSpecialPriceForSingleEventWithoutSpecialPriceReturnsZero()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertEquals(
            0.00,
            $this->subject->getSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function getSpecialPriceForSingleEventWithSpecialPriceReturnsSpecialPrice()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'price_special' => 42.42,
            ]
        );

        self::assertEquals(
            42.42,
            $this->subject->getSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialPriceForSingleEventWithNegativeSpecialPriceThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $price must be >= 0.00.'
        );

        $this->subject->setSpecialPrice(-1.00);
    }

    /**
     * @test
     */
    public function setSpecialPriceForSingleEventWithZeroSpecialPriceSetsSpecialPrice()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setSpecialPrice(0.00);

        self::assertEquals(
            0.00,
            $this->subject->getSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialPriceForSingleEventWithPositiveSpecialPriceSetsSpecialPrice()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setSpecialPrice(42.42);

        self::assertEquals(
            42.42,
            $this->subject->getSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialPriceForSingleEventWithoutSpecialPriceReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertFalse(
            $this->subject->hasSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialPriceForSingleEventWithSpecialPriceReturnsTrue()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setSpecialPrice(42.42);

        self::assertTrue(
            $this->subject->hasSpecialPrice()
        );
    }

    //////////////////////////////////////////////////
    // Tests regarding the special early bird price.
    //////////////////////////////////////////////////

    /**
     * @test
     */
    public function getSpecialEarlyBirdPriceForSingleEventWithoutSpecialEarlyBirdPriceReturnsZero()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertEquals(
            0.00,
            $this->subject->getSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function getSpecialEarlyBirdPriceForSingleEventWithPositiveSpecialEarlyBirdPriceReturnsSpecialEarlyBirdPrice()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'price_special_early' => 42.42,
            ]
        );

        self::assertEquals(
            42.42,
            $this->subject->getSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialEarlyBirdPriceForSingleEventWithNegativeSpecialEarlyBirdPriceThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $price must be >= 0.00.'
        );

        $this->subject->setSpecialEarlyBirdPrice(-1.00);
    }

    /**
     * @test
     */
    public function setSpecialEarlyBirdPriceForSingleEventWithZeroSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setSpecialEarlyBirdPrice(0.00);

        self::assertEquals(
            0.00,
            $this->subject->getSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialEarlyBirdPriceForSingleEventWithPositiveSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setSpecialEarlyBirdPrice(42.42);

        self::assertEquals(
            42.42,
            $this->subject->getSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialEarlyBirdPriceForSingleEventWithoutSpecialEarlyBirdPriceReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertFalse(
            $this->subject->hasSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialEarlyBirdPriceForSingleEventWithSpecialEarlyBirdPriceReturnsTrue()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'price_special_early' => 42.42,
            ]
        );

        self::assertTrue(
            $this->subject->hasSpecialEarlyBirdPrice()
        );
    }

    /////////////////////////////////////////////
    // Tests regarding the special board price.
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function getSpecialBoardPriceForSingleEventWithoutSpecialBoardPriceReturnsZero()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertEquals(
            0.00,
            $this->subject->getSpecialBoardPrice()
        );
    }

    /**
     * @test
     */
    public function getSpecialBoardPriceForSingleEventWithSpecialBoardPriceReturnsSpecialBoardPrice()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'price_special_board' => 42.42,
            ]
        );

        self::assertEquals(
            42.42,
            $this->subject->getSpecialBoardPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialBoardPriceForSingleEventWithNegativeSpecialBoardPriceThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $price must be >= 0.00.'
        );

        $this->subject->setSpecialBoardPrice(-1.00);
    }

    /**
     * @test
     */
    public function setSpecialBoardPriceForSingleEventWithZeroSpecialBoardPriceSetsSpecialBoardPrice()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setSpecialBoardPrice(0.00);

        self::assertEquals(
            0.00,
            $this->subject->getSpecialBoardPrice()
        );
    }

    /**
     * @test
     */
    public function setSpecialBoardPriceForSingleEventWithPositiveSpecialBoardPriceSetsSpecialBoardPrice()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setSpecialBoardPrice(42.42);

        self::assertEquals(
            42.42,
            $this->subject->getSpecialBoardPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialBoardPriceForSingleEventWithoutSpecialBoardPriceReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertFalse(
            $this->subject->hasSpecialBoardPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialBoardPriceForSingleEventWithSpecialBoardPriceReturnsTrue()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setSpecialBoardPrice(42.42);

        self::assertTrue(
            $this->subject->hasSpecialBoardPrice()
        );
    }

    ////////////////////////////////////////////////
    // Tests regarding the additional information.
    ////////////////////////////////////////////////

    /**
     * @test
     */
    public function getAdditionalInformationForSingleEventWithoutAdditionalInformationReturnsEmptyString()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertEquals(
            '',
            $this->subject->getAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function getAdditionalInformationForSingleEventWithAdditionalInformationReturnsAdditionalInformation()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'additional_information' => 'this is good to know',
            ]
        );

        self::assertEquals(
            'this is good to know',
            $this->subject->getAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function setAdditionalInformationForSingleEventSetsAdditionalInformation()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setAdditionalInformation('this is good to know');

        self::assertEquals(
            'this is good to know',
            $this->subject->getAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function hasAdditionalInformationForSingleEventWithoutAdditionalInformationReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertFalse(
            $this->subject->hasAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function hasAdditionalInformationForSingleEventWithAdditionalInformationReturnsTrue()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'additional_information' => 'this is good to know',
            ]
        );

        self::assertTrue(
            $this->subject->hasAdditionalInformation()
        );
    }

    //////////////////////////////////////////////////
    // Tests regarding allowsMultipleRegistration().
    //////////////////////////////////////////////////

    /**
     * @test
     */
    public function allowsMultipleRegistrationForSingleEventWithUnsetAllowsMultipleRegistrationReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertFalse(
            $this->subject->allowsMultipleRegistrations()
        );
    }

    /**
     * @test
     */
    public function allowsMultipleRegistrationForSingleEventWithSetAllowsMultipleRegistrationReturnsTrue()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'allows_multiple_registrations' => true,
            ]
        );

        self::assertTrue(
            $this->subject->allowsMultipleRegistrations()
        );
    }

    //////////////////////////////////
    // Tests regarding usesTerms2().
    //////////////////////////////////

    /**
     * @test
     */
    public function usesTerms2ForSingleEventWithUnsetUseTerms2ReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertFalse(
            $this->subject->usesTerms2()
        );
    }

    /**
     * @test
     */
    public function usesTerms2ForSingleEventWithSetUseTerms2ReturnsTrue()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'use_terms_2' => true,
            ]
        );

        self::assertTrue(
            $this->subject->usesTerms2()
        );
    }

    ///////////////////////////////
    // Tests regarding the notes.
    ///////////////////////////////

    /**
     * @test
     */
    public function getNotesForSingleEventWithoutNotesReturnsEmptyString()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertEquals(
            '',
            $this->subject->getNotes()
        );
    }

    /**
     * @test
     */
    public function getNotesForSingleEventWithNotesReturnsNotes()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'notes' => 'Don\'t forget this.',
            ]
        );

        self::assertEquals(
            'Don\'t forget this.',
            $this->subject->getNotes()
        );
    }

    /**
     * @test
     */
    public function setNotesForSingleEventSetsNotes()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setNotes('Don\'t forget this.');

        self::assertEquals(
            'Don\'t forget this.',
            $this->subject->getNotes()
        );
    }

    /**
     * @test
     */
    public function hasNotesForSingleEventWithoutNotesReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertFalse(
            $this->subject->hasNotes()
        );
    }

    /**
     * @test
     */
    public function hasNotesForSingleEventWithNotesReturnsTrue()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'notes' => 'Don\'t forget this.',
            ]
        );

        self::assertTrue(
            $this->subject->hasNotes()
        );
    }

    ///////////////////////////////
    // Tests regarding the image.
    ///////////////////////////////

    /**
     * @test
     */
    public function getImageForSingleEventWithoutImageReturnsEmptyString()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertEquals(
            '',
            $this->subject->getImage()
        );
    }

    /**
     * @test
     */
    public function getImageForSingleEventWithImageReturnsImage()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'image' => 'file.jpg',
            ]
        );

        self::assertEquals(
            'file.jpg',
            $this->subject->getImage()
        );
    }

    /**
     * @test
     */
    public function setImageForSingleEventSetsImage()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );
        $this->subject->setImage('file.jpg');

        self::assertEquals(
            'file.jpg',
            $this->subject->getImage()
        );
    }

    /**
     * @test
     */
    public function hasImageForSingleEventWithoutImageReturnsFalse()
    {
        $this->subject->setData(
            ['object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE]
        );

        self::assertFalse(
            $this->subject->hasImage()
        );
    }

    /**
     * @test
     */
    public function hasImageForSingleEventWithImageReturnsTrue()
    {
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'image' => 'file.jpg',
            ]
        );

        self::assertTrue(
            $this->subject->hasImage()
        );
    }
}
