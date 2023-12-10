<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Model;

use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Model\Event;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * This test case holds all tests specific to single events.
 * @covers \OliverKlee\Seminars\Model\AbstractTimeSpan
 * @covers \OliverKlee\Seminars\Model\Event
 */
final class SingleEventTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/static_info_tables',
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/seminars',
    ];

    protected $initializeDatabase = false;

    /**
     * @var Event
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Event();
    }

    //////////////////////////////////
    // Tests regarding the subtitle.
    //////////////////////////////////

    /**
     * @test
     */
    public function getSubtitleForSingleEventWithoutSubtitleReturnsAnEmptyString(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertEquals(
            '',
            $this->subject->getSubtitle()
        );
    }

    /**
     * @test
     */
    public function getSubtitleForSingleEventWithSubtitleReturnsSubtitle(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function setSubtitleForSingleEventSetsSubtitle(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function hasSubtitleForSingleEventWithoutSubtitleReturnsFalse(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_EVENT_TOPIC]
        );

        self::assertFalse(
            $this->subject->hasSubtitle()
        );
    }

    /**
     * @test
     */
    public function hasSubtitleForSingleEventWithSubtitleReturnsTrue(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_EVENT_TOPIC]
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
    public function getTeaserForSingleEventWithoutTeaserReturnsAnEmptyString(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertEquals(
            '',
            $this->subject->getTeaser()
        );
    }

    /**
     * @test
     */
    public function getTeaserForSingleEventWithTeaserReturnsTeaser(): void
    {
        $this->subject->setData(
            [
                'teaser' => 'wow, this is teasing',
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function setTeaserForSingleEventSetsTeaser(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function hasTeaserForSingleEventWithoutTeaserReturnsFalse(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertFalse(
            $this->subject->hasTeaser()
        );
    }

    /**
     * @test
     */
    public function hasTeaserForSingleEventWithTeaserReturnsTrue(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function getDescriptionForSingleEventWithoutDescriptionReturnsAnEmptyString(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertEquals(
            '',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionForSingleEventWithDescriptionReturnsDescription(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function setDescriptionForSingleEventSetsDescription(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function hasDescriptionForSingleEventWithoutDescriptionReturnsFalse(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertFalse(
            $this->subject->hasDescription()
        );
    }

    /**
     * @test
     */
    public function hasDescriptionForSingleEventWithDescriptionReturnsTrue(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function getCreditPointsForSingleEventWithoutCreditPointsReturnsZero(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertEquals(
            0,
            $this->subject->getCreditPoints()
        );
    }

    /**
     * @test
     */
    public function getCreditPointsForSingleEventWithPositiveCreditPointsReturnsCreditPoints(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function setCreditPointsForSingleEventWithNegativeCreditPointsThrowsException(): void
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
    public function setCreditPointsForSingleEventWithZeroCreditPointsSetsCreditPoints(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function setCreditPointsForSingleEventWithPositiveCreditPointsSetsCreditPoints(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function hasCreditPointsForSingleEventWithoutCreditPointsReturnsFalse(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertFalse(
            $this->subject->hasCreditPoints()
        );
    }

    /**
     * @test
     */
    public function hasCreditPointsForSingleEventWithCreditPointsReturnsTrue(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function getRegularPriceForSingleEventWithoutRegularPriceReturnsZero(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function getRegularPriceForSingleEventWithPositiveRegularPriceReturnsRegularPrice(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function setRegularPriceForSingleEventWithNegativeRegularPriceThrowsException(): void
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
    public function setRegularPriceForSingleEventWithZeroRegularPriceSetsRegularPrice(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function setRegularPriceForSingleEventWithPositiveRegularPriceSetsRegularPrice(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function hasRegularPriceForSingleEventWithoutRegularPriceReturnsFalse(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertFalse(
            $this->subject->hasRegularPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularPriceForSingleEventWithRegularPriceReturnsTrue(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function getRegularEarlyBirdPriceForSingleEventWithoutRegularEarlyBirdPriceReturnsZero(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertEquals(
            0.00,
            $this->subject->getRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function getRegularEarlyBirdPriceForSingleEventWithPositiveRegularEarlyBirdPriceReturnsRegularEarlyBirdPrice(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function setRegularEarlyBirdPriceForSingleEventWithNegativeRegularEarlyBirdPriceThrowsException(): void
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
    public function setRegularEarlyBirdPriceForSingleEventWithZeroRegularEarlyBirdPriceSetsRegularEarlyBirdPrice(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function setRegularEarlyBirdPriceForSingleEventWithPositiveRegularEarlyBirdPriceSetsRegularEarlyBirdPrice(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function hasRegularEarlyBirdPriceForSingleEventWithoutRegularEarlyBirdPriceReturnsFalse(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertFalse(
            $this->subject->hasRegularEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasRegularEarlyBirdPriceForSingleEventWithRegularEarlyBirdPriceReturnsTrue(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
                'price_regular_early' => 42.42,
            ]
        );

        self::assertTrue(
            $this->subject->hasRegularEarlyBirdPrice()
        );
    }

    ///////////////////////////////////////
    // Tests regarding the special price.
    ///////////////////////////////////////

    /**
     * @test
     */
    public function getSpecialPriceForSingleEventWithoutSpecialPriceReturnsZero(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertEquals(
            0.00,
            $this->subject->getSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function getSpecialPriceForSingleEventWithSpecialPriceReturnsSpecialPrice(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function setSpecialPriceForSingleEventWithNegativeSpecialPriceThrowsException(): void
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
    public function setSpecialPriceForSingleEventWithZeroSpecialPriceSetsSpecialPrice(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function setSpecialPriceForSingleEventWithPositiveSpecialPriceSetsSpecialPrice(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function hasSpecialPriceForSingleEventWithoutSpecialPriceReturnsFalse(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertFalse(
            $this->subject->hasSpecialPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialPriceForSingleEventWithSpecialPriceReturnsTrue(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function getSpecialEarlyBirdPriceForSingleEventWithoutSpecialEarlyBirdPriceReturnsZero(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertEquals(
            0.00,
            $this->subject->getSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function getSpecialEarlyBirdPriceForSingleEventWithPositiveSpecialEarlyBirdPriceReturnsSpecialEarlyBirdPrice(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function setSpecialEarlyBirdPriceForSingleEventWithNegativeSpecialEarlyBirdPriceThrowsException(): void
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
    public function setSpecialEarlyBirdPriceForSingleEventWithZeroSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function setSpecialEarlyBirdPriceForSingleEventWithPositiveSpecialEarlyBirdPriceSetsSpecialEarlyBirdPrice(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function hasSpecialEarlyBirdPriceForSingleEventWithoutSpecialEarlyBirdPriceReturnsFalse(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertFalse(
            $this->subject->hasSpecialEarlyBirdPrice()
        );
    }

    /**
     * @test
     */
    public function hasSpecialEarlyBirdPriceForSingleEventWithSpecialEarlyBirdPriceReturnsTrue(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
                'price_special_early' => 42.42,
            ]
        );

        self::assertTrue(
            $this->subject->hasSpecialEarlyBirdPrice()
        );
    }

    ////////////////////////////////////////////////
    // Tests regarding the additional information.
    ////////////////////////////////////////////////

    /**
     * @test
     */
    public function getAdditionalInformationForSingleEventWithoutAdditionalInformationReturnsEmptyString(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertEquals(
            '',
            $this->subject->getAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function getAdditionalInformationForSingleEventWithAdditionalInformationReturnsAdditionalInformation(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function setAdditionalInformationForSingleEventSetsAdditionalInformation(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function hasAdditionalInformationForSingleEventWithoutAdditionalInformationReturnsFalse(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertFalse(
            $this->subject->hasAdditionalInformation()
        );
    }

    /**
     * @test
     */
    public function hasAdditionalInformationForSingleEventWithAdditionalInformationReturnsTrue(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function allowsMultipleRegistrationForSingleEventWithUnsetAllowsMultipleRegistrationReturnsFalse(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertFalse(
            $this->subject->allowsMultipleRegistrations()
        );
    }

    /**
     * @test
     */
    public function allowsMultipleRegistrationForSingleEventWithSetAllowsMultipleRegistrationReturnsTrue(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function usesTerms2ForSingleEventWithUnsetUseTerms2ReturnsFalse(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertFalse(
            $this->subject->usesTerms2()
        );
    }

    /**
     * @test
     */
    public function usesTerms2ForSingleEventWithSetUseTerms2ReturnsTrue(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function getNotesForSingleEventWithoutNotesReturnsEmptyString(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertEquals(
            '',
            $this->subject->getNotes()
        );
    }

    /**
     * @test
     */
    public function getNotesForSingleEventWithNotesReturnsNotes(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
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
    public function setNotesForSingleEventSetsNotes(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
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
    public function hasNotesForSingleEventWithoutNotesReturnsFalse(): void
    {
        $this->subject->setData(
            ['object_type' => EventInterface::TYPE_SINGLE_EVENT]
        );

        self::assertFalse(
            $this->subject->hasNotes()
        );
    }

    /**
     * @test
     */
    public function hasNotesForSingleEventWithNotesReturnsTrue(): void
    {
        $this->subject->setData(
            [
                'object_type' => EventInterface::TYPE_SINGLE_EVENT,
                'notes' => 'Don\'t forget this.',
            ]
        );

        self::assertTrue(
            $this->subject->hasNotes()
        );
    }
}
