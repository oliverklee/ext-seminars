<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\PhpUnit\TestCase;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Model\Event;

/**
 * This test case holds all tests specific to event dates.
 *
 * @covers \OliverKlee\Seminars\Model\Event
 */
final class EventDateTest extends TestCase
{
    /**
     * @var Event
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new Event();
    }

    // Tests concerning the title.

    /**
     * @test
     */
    public function getTitleWithNonEmptyTopicTitleReturnsTopicTitle(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['title' => 'Superhero']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function getRawTitleWithNonEmptyTopicTitleReturnsDateTitle(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel(['title' => 'Superhero']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
                'title' => 'Supervillain',
            ]
        );

        self::assertSame(
            'Supervillain',
            $this->subject->getRawTitle()
        );
    }

    // Tests regarding the subtitle.

    /**
     * @test
     */
    public function getSubtitleForEventDateWithoutSubtitleReturnsAnEmptyString(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function getSubtitleForEventDateWithSubtitleReturnsSubtitle(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['subtitle' => 'sub title']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setSubtitleForEventDateSetsSubtitle(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasSubtitleForEventDateWithoutSubtitleReturnsFalse(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasSubtitleForEventDateWithSubtitleReturnsTrue(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['subtitle' => 'sub title']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue(
            $this->subject->hasSubtitle()
        );
    }

    // Tests regarding the teaser.

    /**
     * @test
     */
    public function getTeaserForEventDateWithoutTeaserReturnsAnEmptyString(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function getTeaserForEventDateWithTeaserReturnsTeaser(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['teaser' => 'wow, this is teasing']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setTeaserForEventDateSetsTeaser(): void
    {
        /** @var Event $topic */
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasTeaserForEventDateWithoutTeaserReturnsFalse(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasTeaserForEventDateWithTeaserReturnsTrue(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['teaser' => 'wow, this is teasing']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue(
            $this->subject->hasTeaser()
        );
    }

    // Tests regarding the description.

    /**
     * @test
     */
    public function getDescriptionForEventDateWithoutDescriptionReturnsAnEmptyString(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function getDescriptionForEventDateWithDescriptionReturnsDescription(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(
                ['description' => 'this is a great event.']
            );
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setDescriptionForEventDateSetsDescription(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasDescriptionForEventDateWithoutDescriptionReturnsFalse(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasDescriptionForEventDateWithDescriptionReturnsTrue(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(
                ['description' => 'this is a great event.']
            );
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue(
            $this->subject->hasDescription()
        );
    }

    // Tests regarding the credit points.

    /**
     * @test
     */
    public function getCreditPointsForEventDateWithoutCreditPointsReturnsZero(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function getCreditPointsForEventDateWithPositiveCreditPointsReturnsCreditPoints(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['credit_points' => 42]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setCreditPointsForEventDateWithZeroCreditPointsSetsCreditPoints(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setCreditPointsForEventDateWithPositiveCreditPointsSetsCreditPoints(): void
    {
        /** @var Event $topic */
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasCreditPointsForEventDateWithoutCreditPointsReturnsFalse(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasCreditPointsForEventDateWithCreditPointsReturnsTrue(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['credit_points' => 42]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue(
            $this->subject->hasCreditPoints()
        );
    }

    // Tests regarding the regular price.

    /**
     * @test
     */
    public function getRegularPriceForEventDateWithoutRegularPriceReturnsZero(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['price_regular' => '0.0']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertEquals(
            0.0,
            $this->subject->getRegularPrice()
        );
    }

    /**
     * @test
     */
    public function getRegularPriceForEventDateWithPositiveRegularPriceReturnsRegularPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['price_regular' => '42.42']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setRegularPriceForEventDateWithZeroRegularPriceSetsRegularPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setRegularPriceForEventDateWithPositiveRegularPriceSetsRegularPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasRegularPriceForEventDateWithoutRegularPriceReturnsFalse(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasRegularPriceForEventDateWithRegularPriceReturnsTrue(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['price_regular' => '42.42']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue(
            $this->subject->hasRegularPrice()
        );
    }

    // Tests regarding the regular early bird price.

    /**
     * @test
     */
    public function getRegularEarlyBirdPriceForEventDateWithoutRegularEarlyBirdPriceReturnsZero(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function getRegularEarlyBirdPriceForEventDateWithPositiveRegularEarlyBirdPriceReturnsRegularEarlyBirdPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['price_regular_early' => '42.42']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setRegularEarlyBirdPriceForEventDateWithZeroRegularEarlyBirdPriceSetsRegularEarlyBirdPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setRegularEarlyBirdPriceForEventDateWithPositiveRegularEarlyBirdPriceSetsRegularEarlyBirdPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasRegularEarlyBirdPriceForEventDateWithoutRegularEarlyBirdPriceReturnsFalse(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasRegularEarlyBirdPriceForEventDateWithRegularEarlyBirdPriceReturnsTrue(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['price_regular_early' => '42.42']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue(
            $this->subject->hasRegularEarlyBirdPrice()
        );
    }

    // Tests regarding the regular board price.

    /**
     * @test
     */
    public function getRegularBoardPriceForEventDateWithoutRegularBoardPriceReturnsZero(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function getRegularBoardPriceForEventDateWithPositiveRegularBoardPriceReturnsRegularBoardPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['price_regular_board' => '42.42']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setRegularBoardPriceForEventDateWithZeroRegularBoardPriceSetsRegularBoardPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setRegularBoardPriceForEventDateWithPositiveRegularBoardPriceSetsRegularBoardPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasRegularBoardPriceForEventDateWithoutRegularBoardPriceReturnsFalse(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasRegularBoardPriceForEventDateWithRegularBoardPriceReturnsTrue(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['price_regular_board' => '42.42']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue(
            $this->subject->hasRegularBoardPrice()
        );
    }

    // Tests regarding the special price.

    /**
     * @test
     */
    public function getSpecialPriceForEventDateWithoutSpecialPriceReturnsZero(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function getSpecialPriceForEventDateWithSpecialPriceReturnsSpecialPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['price_special' => '42.42']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setSpecialPriceForEventDateWithZeroSpecialPriceSetsSpecialPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setSpecialPriceForEventDateWithPositiveSpecialPriceSetsSpecialPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasSpecialPriceForEventDateWithoutSpecialPriceReturnsFalse(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasSpecialPriceForEventDateWithSpecialPriceReturnsTrue(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['price_special' => '42.42']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue(
            $this->subject->hasSpecialPrice()
        );
    }

    // Tests regarding the special early bird price.

    /**
     * @test
     */
    public function getSpecialEarlyBirdPriceForEventDateWithoutSpecialEarlyBirdPriceReturnsZero(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_COMPLETE,
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
    public function getSpecialEarlyBirdPriceForEventDateWithPositiveSpecialEarlyBirdPriceReturnsSpecialEarlyBirdPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['price_special_early' => '42.42']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setSpecialEarlyBirdPriceForEventDateWithZeroSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setSpecialEarlyBirdPriceForEventDateWithPositiveSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasSpecialEarlyBirdPriceForEventDateWithoutSpecialEarlyBirdPriceReturnsFalse(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasSpecialEarlyBirdPriceForEventDateWithSpecialEarlyBirdPriceReturnsTrue(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['price_special_early' => '42.42']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue(
            $this->subject->hasSpecialEarlyBirdPrice()
        );
    }

    // Tests regarding the special board price.

    /**
     * @test
     */
    public function getSpecialBoardPriceForEventDateWithoutSpecialBoardPriceReturnsZero(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function getSpecialBoardPriceForEventDateWithSpecialBoardPriceReturnsSpecialBoardPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['price_special_board' => '42.42']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setSpecialBoardPriceForEventDateWithZeroSpecialBoardPriceSetsSpecialBoardPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setSpecialBoardPriceForEventDateWithPositiveSpecialBoardPriceSetsSpecialBoardPrice(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasSpecialBoardPriceForEventDateWithoutSpecialBoardPriceReturnsFalse(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasSpecialBoardPriceForEventDateWithSpecialBoardPriceReturnsTrue(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['price_special_board' => '42.42']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue(
            $this->subject->hasSpecialBoardPrice()
        );
    }

    // Tests regarding the additional information.

    /**
     * @test
     */
    public function getAdditionalInformationForEventDateWithoutAdditionalInformationReturnsEmptyString(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function getAdditionalInformationForEventDateWithAdditionalInformationReturnsAdditionalInformation(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(
                ['additional_information' => 'this is good to know']
            );
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setAdditionalInformationForEventDateSetsAdditionalInformation(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasAdditionalInformationForEventDateWithoutAdditionalInformationReturnsFalse(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasAdditionalInformationForEventDateWithAdditionalInformationReturnsTrue(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(
                ['additional_information' => 'this is good to know']
            );
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue(
            $this->subject->hasAdditionalInformation()
        );
    }

    // Tests regarding allowsMultipleRegistration().

    /**
     * @test
     */
    public function allowsMultipleRegistrationForEventDateWithUnsetAllowsMultipleRegistrationReturnsFalse(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function allowsMultipleRegistrationForEventDateWithSetAllowsMultipleRegistrationReturnsTrue(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['allows_multiple_registrations' => 1]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue(
            $this->subject->allowsMultipleRegistrations()
        );
    }

    // Tests regarding usesTerms2().

    /**
     * @test
     */
    public function usesTerms2ForEventDateWithUnsetUseTerms2ReturnsFalse(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function usesTerms2ForEventDateWithSetUseTerms2ReturnsTrue(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel(['use_terms_2' => 1]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue(
            $this->subject->usesTerms2()
        );
    }

    // Tests regarding the notes.

    /**
     * @test
     */
    public function getNotesForEventDateWithoutNotesReturnsEmptyString(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function getNotesForEventDateWithNotesReturnsNotes(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['notes' => 'Don\'t forget this.']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setNotesForEventDateSetsNotes(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasNotesForEventDateWithoutNotesReturnsFalse(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasNotesForEventDateWithNotesReturnsTrue(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['notes' => 'Don\'t forget this.']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue(
            $this->subject->hasNotes()
        );
    }

    // Tests regarding the image.

    /**
     * @test
     */
    public function getImageForEventDateWithoutImageReturnsEmptyString(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function getImageForEventDateWithImageReturnsImage(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['image' => 'file.jpg']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setImageForEventDateSetsImage(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasImageForEventDateWithoutImageReturnsFalse(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel([]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function hasImageForEventDateWithImageReturnsTrue(): void
    {
        $topic = MapperRegistry::get(EventMapper::class)
            ->getLoadedTestingModel(['image' => 'file.jpg']);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue(
            $this->subject->hasImage()
        );
    }

    // Tests concerning the payment methods

    /**
     * @test
     */
    public function getPaymentMethodsReturnsPaymentMethodsFromTopic(): void
    {
        $paymentMethods = new Collection();
        $topic = new Event();
        $topic->setData(['payment_methods' => $paymentMethods]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
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
    public function setPaymentMethodsThrowsException(): void
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'setPaymentMethods may only be called on single events and event ' .
            'topics, but not on event dates.'
        );

        $topic = new Event();
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        /** @var Collection<\Tx_Seminars_Model_PaymentMethod> $paymentMethods */
        $paymentMethods = new Collection();
        $this->subject->setPaymentMethods($paymentMethods);
    }

    // Tests concerning "price on request"

    /**
     * @test
     */
    public function getPriceOnRequestReturnsPriceOnRequestFromDopic(): void
    {
        $topic = new Event();
        $topic->setData(['price_on_request' => true]);
        $this->subject->setData(
            [
                'object_type' => Event::TYPE_DATE,
                'topic' => $topic,
            ]
        );

        self::assertTrue($this->subject->getPriceOnRequest());
    }
}
