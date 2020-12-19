<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\PhpUnit\TestCase;

/**
 * This test case holds all tests specific to event dates.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class EventDateTest extends TestCase
{
    /**
     * @var \Tx_Seminars_Model_Event
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Seminars_Model_Event();
    }

    ////////////////////////////////
    // Tests concerning the title.
    ////////////////////////////////

    /**
     * @test
     */
    public function getTitleWithNonEmptyTopicTitleReturnsTopicTitle()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['title' => 'Superhero']);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
                'title' => 'Supervillain',
            ]
        );

        self::assertSame(
            'Superhero',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getRawTitleWithNonEmptyTopicTitleReturnsDateTitle()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['title' => 'Superhero']);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
                'title' => 'Supervillain',
            ]
        );

        self::assertSame(
            'Supervillain',
            $this->subject->getRawTitle()
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
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertEquals(
            '',
            $this->subject->getSubtitle()
        );
    }

    /**
     * @test
     */
    public function getSubtitleForEventDateWithSubtitleReturnsSubtitle()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['subtitle' => 'sub title']);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function setSubtitleForEventDateSetsSubtitle()
    {
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setSubtitle('sub title');

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
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertFalse(
            $this->subject->hasSubtitle()
        );
    }

    /**
     * @test
     */
    public function hasSubtitleForEventDateWithSubtitleReturnsTrue()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['subtitle' => 'sub title']);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

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
    public function getTeaserForEventDateWithoutTeaserReturnsAnEmptyString()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertEquals(
            '',
            $this->subject->getTeaser()
        );
    }

    /**
     * @test
     */
    public function getTeaserForEventDateWithTeaserReturnsTeaser()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['teaser' => 'wow, this is teasing']);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function setTeaserForEventDateSetsTeaser()
    {
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setTeaser('wow, this is teasing');

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
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertFalse(
            $this->subject->hasTeaser()
        );
    }

    /**
     * @test
     */
    public function hasTeaserForEventDateWithTeaserReturnsTrue()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['teaser' => 'wow, this is teasing']);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function getDescriptionForEventDateWithoutDescriptionReturnsAnEmptyString()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertEquals(
            '',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionForEventDateWithDescriptionReturnsDescription()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(
                ['description' => 'this is a great event.']
            );
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function setDescriptionForEventDateSetsDescription()
    {
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setDescription('this is a great event.');

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
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertFalse(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionForEventDateWithDescriptionReturnsTrue()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(
                ['description' => 'this is a great event.']
            );
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function getCreditPointsForEventDateWithoutCreditPointsReturnsZero()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertEquals(
            0,
            $this->subject->getCreditPoints()
        );
    }

    /**
     * @test
     */
    public function getCreditPointsForEventDateWithPositiveCreditPointsReturnsCreditPoints()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['credit_points' => 42]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function setCreditPointsForEventDateWithZeroCreditPointsSetsCreditPoints()
    {
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setCreditPoints(0);

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
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setCreditPoints(42);

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
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertFalse(
            $this->subject->hasCreditPoints()
        );
    }

    /**
     * @test
     */
    public function hasCreditPointsForEventDateWithCreditPointsReturnsTrue()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['credit_points' => 42]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function getRegularPriceForEventDateWithoutRegularPriceReturnsZero()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['price_regular' => 0.00]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function getRegularPriceForEventDateWithPositiveRegularPriceReturnsRegularPrice()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['price_regular' => 42.42]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function setRegularPriceForEventDateWithZeroRegularPriceSetsRegularPrice()
    {
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setRegularPrice(0.00);

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
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setRegularPrice(42.42);

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
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertFalse(
            $this->subject->hasRegularPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularPriceForEventDateWithRegularPriceReturnsTrue()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['price_regular' => 42.42]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function getRegularEarlyBirdPriceForEventDateWithoutRegularEarlyBirdPriceReturnsZero()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertEquals(
            0.00,
            $this->subject->getRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function getRegularEarlyBirdPriceForEventDateWithPositiveRegularEarlyBirdPriceReturnsRegularEarlyBirdPrice()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['price_regular_early' => 42.42]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function setRegularEarlyBirdPriceForEventDateWithZeroRegularEarlyBirdPriceSetsRegularEarlyBirdPrice()
    {
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setRegularEarlyBirdPrice(0.00);

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
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setRegularEarlyBirdPrice(42.42);

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
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertFalse(
            $this->subject->hasRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularEarlyBirdPriceForEventDateWithRegularEarlyBirdPriceReturnsTrue()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['price_regular_early' => 42.42]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function getRegularBoardPriceForEventDateWithoutRegularBoardPriceReturnsZero()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertEquals(
            0.00,
            $this->subject->getRegularBoardPrice()
        );
    }

    /**
     * @test
     */
    public function getRegularBoardPriceForEventDateWithPositiveRegularBoardPriceReturnsRegularBoardPrice()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['price_regular_board' => 42.42]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function setRegularBoardPriceForEventDateWithZeroRegularBoardPriceSetsRegularBoardPrice()
    {
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setRegularBoardPrice(0.00);

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
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setRegularBoardPrice(42.42);

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
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertFalse(
            $this->subject->hasRegularBoardPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularBoardPriceForEventDateWithRegularBoardPriceReturnsTrue()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['price_regular_board' => 42.42]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function getSpecialPriceForEventDateWithoutSpecialPriceReturnsZero()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertEquals(
            0.00,
            $this->subject->getSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function getSpecialPriceForEventDateWithSpecialPriceReturnsSpecialPrice()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['price_special' => 42.42]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function setSpecialPriceForEventDateWithZeroSpecialPriceSetsSpecialPrice()
    {
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setSpecialPrice(0.00);

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
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setSpecialPrice(42.42);

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
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertFalse(
            $this->subject->hasSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialPriceForEventDateWithSpecialPriceReturnsTrue()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['price_special' => 42.42]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

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
    public function getSpecialEarlyBirdPriceForEventDateWithoutSpecialEarlyBirdPriceReturnsZero()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_COMPLETE,
                'topic' => $topic,
            ]
        );

        self::assertEquals(
            0.00,
            $this->subject->getSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function getSpecialEarlyBirdPriceForEventDateWithPositiveSpecialEarlyBirdPriceReturnsSpecialEarlyBirdPrice()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['price_special_early' => 42.42]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function setSpecialEarlyBirdPriceForEventDateWithZeroSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice()
    {
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setSpecialEarlyBirdPrice(0.00);

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
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setSpecialEarlyBirdPrice(42.42);

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
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertFalse(
            $this->subject->hasSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialEarlyBirdPriceForEventDateWithSpecialEarlyBirdPriceReturnsTrue()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['price_special_early' => 42.42]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function getSpecialBoardPriceForEventDateWithoutSpecialBoardPriceReturnsZero()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertEquals(
            0.00,
            $this->subject->getSpecialBoardPrice()
        );
    }

    /**
     * @test
     */
    public function getSpecialBoardPriceForEventDateWithSpecialBoardPriceReturnsSpecialBoardPrice()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['price_special_board' => 42.42]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function setSpecialBoardPriceForEventDateWithZeroSpecialBoardPriceSetsSpecialBoardPrice()
    {
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setSpecialBoardPrice(0.00);

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
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setSpecialBoardPrice(42.42);

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
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertFalse(
            $this->subject->hasSpecialBoardPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialBoardPriceForEventDateWithSpecialBoardPriceReturnsTrue()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['price_special_board' => 42.42]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

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
    public function getAdditionalInformationForEventDateWithoutAdditionalInformationReturnsEmptyString()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertEquals(
            '',
            $this->subject->getAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function getAdditionalInformationForEventDateWithAdditionalInformationReturnsAdditionalInformation()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(
                ['additional_information' => 'this is good to know']
            );
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function setAdditionalInformationForEventDateSetsAdditionalInformation()
    {
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setAdditionalInformation('this is good to know');

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
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertFalse(
            $this->subject->hasAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function hasAdditionalInformationForEventDateWithAdditionalInformationReturnsTrue()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(
                ['additional_information' => 'this is good to know']
            );
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function allowsMultipleRegistrationForEventDateWithUnsetAllowsMultipleRegistrationReturnsFalse()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertFalse(
            $this->subject->allowsMultipleRegistrations()
        );
    }

    /**
     * @test
     */
    public function allowsMultipleRegistrationForEventDateWithSetAllowsMultipleRegistrationReturnsTrue()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(
                ['allows_multiple_registrations' => true]
            );
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function usesTerms2ForEventDateWithUnsetUseTerms2ReturnsFalse()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertFalse(
            $this->subject->usesTerms2()
        );
    }

    /**
     * @test
     */
    public function usesTerms2ForEventDateWithSetUseTerms2ReturnsTrue()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['use_terms_2' => true]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function getNotesForEventDateWithoutNotesReturnsEmptyString()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertEquals(
            '',
            $this->subject->getNotes()
        );
    }

    /**
     * @test
     */
    public function getNotesForEventDateWithNotesReturnsNotes()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['notes' => 'Don\'t forget this.']);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function setNotesForEventDateSetsNotes()
    {
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setNotes("Don't forget this.");

        self::assertEquals(
            "Don't forget this.",
            $topic->getNotes()
        );
    }

    /**
     * @test
     */
    public function hasNotesForEventDateWithoutNotesReturnsFalse()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertFalse(
            $this->subject->hasNotes()
        );
    }

    /**
     * @test
     */
    public function hasNotesForEventDateWithNotesReturnsTrue()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['notes' => 'Don\'t forget this.']);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function getImageForEventDateWithoutImageReturnsEmptyString()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertEquals(
            '',
            $this->subject->getImage()
        );
    }

    /**
     * @test
     */
    public function getImageForEventDateWithImageReturnsImage()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['image' => 'file.jpg']);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
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
    public function setImageForEventDateSetsImage()
    {
        /** @var \Tx_Seminars_Model_Event $topic */
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );
        $this->subject->setImage('file.jpg');

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
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertFalse(
            $this->subject->hasImage()
        );
    }

    /**
     * @test
     */
    public function hasImageForEventDateWithImageReturnsTrue()
    {
        $topic = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class)
            ->getLoadedTestingModel(['image' => 'file.jpg']);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue(
            $this->subject->hasImage()
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
        $paymentMethods = new \Tx_Oelib_List();
        $topic = new \Tx_Seminars_Model_Event();
        $topic->setData(['payment_methods' => $paymentMethods]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertSame(
            $paymentMethods,
            $this->subject->getPaymentMethods()
        );
    }

    /**
     * @test
     */
    public function setPaymentMethodsThrowsException()
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'setPaymentMethods may only be called on single events and event ' .
            'topics, but not on event dates.'
        );

        $topic = new \Tx_Seminars_Model_Event();
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        $this->subject->setPaymentMethods(new \Tx_Oelib_List());
    }

    /*
     * Tests concerning "price on request"
     */

    /**
     * @test
     */
    public function getPriceOnRequestReturnsPriceOnRequestFromDopic()
    {
        $topic = new \Tx_Seminars_Model_Event();
        $topic->setData(['price_on_request' => true]);
        $this->subject->setData(
            [
                'object_type' => \Tx_Seminars_Model_Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue($this->subject->getPriceOnRequest());
    }
}
