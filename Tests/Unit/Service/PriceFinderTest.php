<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Service;

use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\Price;
use OliverKlee\Seminars\Service\PriceFinder;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\DateTimeAspect;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Seminars\Service\PriceFinder
 */
final class PriceFinderTest extends UnitTestCase
{
    private PriceFinder $subject;

    private \DateTimeImmutable $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = new \DateTimeImmutable('2022-04-01 10:00:00');
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('date', new DateTimeAspect($this->now));

        $this->subject = new PriceFinder();
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();

        parent::tearDown();
    }

    /**
     * @deprecated #1960 will be removed in seminars 6.0, use `DateTime::createFromImmutable()` instead (PHP >= 7.3)
     */
    private function createFromImmutable(\DateTimeInterface $dateTime): \DateTime
    {
        return \DateTime::createFromFormat(\DateTimeInterface::ATOM, $dateTime->format(\DateTime::ATOM));
    }

    /**
     * @test
     */
    public function isSingleton(): void
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function findApplicablePricesForFreeEventWithoutEarlyBirdDeadlineReturnsFreeStandardPrice(): void
    {
        $event = new SingleEvent();
        $event->setStandardPrice(0.0);

        $result = $this->subject->findApplicablePrices($event);

        $expectedPrice = new Price(0.0, 'price.standard', Price::PRICE_STANDARD);
        self::assertEquals([Price::PRICE_STANDARD => $expectedPrice], $result);
    }

    /**
     * @test
     */
    public function findApplicablePricesForFreeEventWithEarlyBirdDeadlineInThePastReturnsFreeStandardPrice(): void
    {
        $event = new SingleEvent();
        $event->setStandardPrice(0.0);
        $earlyBirdDeadline = $this->now->modify('-1 day');
        $event->setEarlyBirdDeadline($this->createFromImmutable($earlyBirdDeadline));

        $result = $this->subject->findApplicablePrices($event);

        $expectedPrice = new Price(0.0, 'price.standard', Price::PRICE_STANDARD);
        self::assertEquals([Price::PRICE_STANDARD => $expectedPrice], $result);
    }

    /**
     * @test
     */
    public function findApplicablePricesForFreeEventWithEarlyBirdDeadlineInTheFutureReturnsFreeStandardPrice(): void
    {
        $event = new SingleEvent();
        $event->setStandardPrice(0.0);
        $earlyBirdDeadline = $this->now->modify('+1 day');
        $event->setEarlyBirdDeadline($this->createFromImmutable($earlyBirdDeadline));

        $result = $this->subject->findApplicablePrices($event);

        $expectedPrice = new Price(0.0, 'price.standard', Price::PRICE_STANDARD);
        self::assertEquals([Price::PRICE_STANDARD => $expectedPrice], $result);
    }

    /**
     * @test
     */
    public function findApplicablePricesForFreeEventWithEarlyBirdAndDeadlineInTheFutureReturnsFreeStandardPrice(): void
    {
        $event = new SingleEvent();
        $event->setStandardPrice(0.0);
        $earlyBirdDeadline = $this->now->modify('+1 day');
        $event->setEarlyBirdDeadline($this->createFromImmutable($earlyBirdDeadline));
        $event->setEarlyBirdPrice(14.5);

        $result = $this->subject->findApplicablePrices($event);

        $expectedPrice = new Price(0.0, 'price.standard', Price::PRICE_STANDARD);
        self::assertEquals([Price::PRICE_STANDARD => $expectedPrice], $result);
    }

    /**
     * @test
     */
    public function findApplicablePricesForAllPricesAndNoEarlyBirdDeadlineReturnsNonEarlyBirdPrices(): void
    {
        $event = new SingleEvent();

        $standardPriceAmount = 1.0;
        $earlyBirdPriceAmount = 2.0;
        $specialPriceAmount = 3.0;
        $specialEarlyBirdPriceAmount = 4.0;
        $event->setStandardPrice($standardPriceAmount);
        $event->setEarlyBirdPrice($earlyBirdPriceAmount);
        $event->setSpecialPrice($specialPriceAmount);
        $event->setSpecialEarlyBirdPrice($specialEarlyBirdPriceAmount);

        $result = $this->subject->findApplicablePrices($event);

        $expected = [
            Price::PRICE_STANDARD => new Price($standardPriceAmount, 'price.standard', Price::PRICE_STANDARD),
            Price::PRICE_SPECIAL => new Price($specialPriceAmount, 'price.special', Price::PRICE_SPECIAL),
        ];

        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function findApplicablePricesForAllPricesAndEarlyBirdDeadlineInThePastReturnsNonEarlyBirdPrices(): void
    {
        $event = new SingleEvent();
        $earlyBirdDeadline = $this->now->modify('-1 day');
        $event->setEarlyBirdDeadline($this->createFromImmutable($earlyBirdDeadline));

        $standardPriceAmount = 1.0;
        $earlyBirdPriceAmount = 2.0;
        $specialPriceAmount = 3.0;
        $specialEarlyBirdPriceAmount = 4.0;
        $event->setStandardPrice($standardPriceAmount);
        $event->setEarlyBirdPrice($earlyBirdPriceAmount);
        $event->setSpecialPrice($specialPriceAmount);
        $event->setSpecialEarlyBirdPrice($specialEarlyBirdPriceAmount);

        $result = $this->subject->findApplicablePrices($event);

        $expected = [
            Price::PRICE_STANDARD => new Price($standardPriceAmount, 'price.standard', Price::PRICE_STANDARD),
            Price::PRICE_SPECIAL => new Price($specialPriceAmount, 'price.special', Price::PRICE_SPECIAL),
        ];

        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function findApplicablePricesForAllPricesAndEarlyBirdDeadlineNowReturnsNonEarlyBirdPrices(): void
    {
        $event = new SingleEvent();
        $event->setEarlyBirdDeadline($this->createFromImmutable($this->now));

        $standardPriceAmount = 1.0;
        $earlyBirdPriceAmount = 2.0;
        $specialPriceAmount = 3.0;
        $specialEarlyBirdPriceAmount = 4.0;
        $event->setStandardPrice($standardPriceAmount);
        $event->setEarlyBirdPrice($earlyBirdPriceAmount);
        $event->setSpecialPrice($specialPriceAmount);
        $event->setSpecialEarlyBirdPrice($specialEarlyBirdPriceAmount);

        $result = $this->subject->findApplicablePrices($event);

        $expected = [
            Price::PRICE_STANDARD => new Price($standardPriceAmount, 'price.standard', Price::PRICE_STANDARD),
            Price::PRICE_SPECIAL => new Price($specialPriceAmount, 'price.special', Price::PRICE_SPECIAL),
        ];

        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function findApplicablePricesForAllPricesAndEarlyBirdDeadlineInTheFutureReturnsEarlyBirdPrices(): void
    {
        $event = new SingleEvent();
        $earlyBirdDeadline = $this->now->modify('+1 day');
        $event->setEarlyBirdDeadline($this->createFromImmutable($earlyBirdDeadline));

        $standardPriceAmount = 1.0;
        $earlyBirdPriceAmount = 2.0;
        $specialPriceAmount = 3.0;
        $specialEarlyBirdPriceAmount = 4.0;
        $event->setStandardPrice($standardPriceAmount);
        $event->setEarlyBirdPrice($earlyBirdPriceAmount);
        $event->setSpecialPrice($specialPriceAmount);
        $event->setSpecialEarlyBirdPrice($specialEarlyBirdPriceAmount);

        $result = $this->subject->findApplicablePrices($event);

        $expected = [
            Price::PRICE_EARLY_BIRD => new Price($earlyBirdPriceAmount, 'price.earlyBird', Price::PRICE_EARLY_BIRD),
            Price::PRICE_SPECIAL_EARLY_BIRD => new Price(
                $specialEarlyBirdPriceAmount,
                'price.specialEarlyBird',
                Price::PRICE_SPECIAL_EARLY_BIRD,
            ),
        ];

        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function findApplicablePricesWithEarlyBirdApplicableReplacesStandardPriceWithEarlyBirdVersion(): void
    {
        $event = new SingleEvent();
        $earlyBirdDeadline = $this->now->modify('+1 day');
        $event->setEarlyBirdDeadline($this->createFromImmutable($earlyBirdDeadline));

        $standardPriceAmount = 1.0;
        $earlyBirdPriceAmount = 2.0;
        $event->setStandardPrice($standardPriceAmount);
        $event->setEarlyBirdPrice($earlyBirdPriceAmount);

        $result = $this->subject->findApplicablePrices($event);

        $expected = [
            Price::PRICE_EARLY_BIRD => new Price($earlyBirdPriceAmount, 'price.earlyBird', Price::PRICE_EARLY_BIRD),
        ];

        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function findApplicablePricesWithEarlyBirdApplicableReplacesSpecialPriceWithEarlyBirdVersion(): void
    {
        $event = new SingleEvent();
        $earlyBirdDeadline = $this->now->modify('+1 day');
        $event->setEarlyBirdDeadline($this->createFromImmutable($earlyBirdDeadline));

        $standardPriceAmount = 1.0;
        $specialPriceAmount = 3.0;
        $specialEarlyBirdPriceAmount = 4.0;
        $event->setStandardPrice($standardPriceAmount);
        $event->setSpecialPrice($specialPriceAmount);
        $event->setSpecialEarlyBirdPrice($specialEarlyBirdPriceAmount);

        $result = $this->subject->findApplicablePrices($event);

        $expected = [
            Price::PRICE_STANDARD => new Price($standardPriceAmount, 'price.standard', Price::PRICE_STANDARD),
            Price::PRICE_SPECIAL_EARLY_BIRD => new Price(
                $specialEarlyBirdPriceAmount,
                'price.specialEarlyBird',
                Price::PRICE_SPECIAL_EARLY_BIRD,
            ),
        ];

        self::assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function findApplicablePricesWithEarlyBirdApplicableAndNoSpecialPriceKeepsEarlyBirdSpecialPrice(): void
    {
        $event = new SingleEvent();
        $earlyBirdDeadline = $this->now->modify('+1 day');
        $event->setEarlyBirdDeadline($this->createFromImmutable($earlyBirdDeadline));

        $standardPriceAmount = 1.0;
        $earlyBirdPriceAmount = 2.0;
        $specialEarlyBirdPriceAmount = 4.0;
        $event->setStandardPrice($standardPriceAmount);
        $event->setEarlyBirdPrice($earlyBirdPriceAmount);
        $event->setSpecialEarlyBirdPrice($specialEarlyBirdPriceAmount);

        $result = $this->subject->findApplicablePrices($event);

        $expected = [
            Price::PRICE_EARLY_BIRD => new Price($earlyBirdPriceAmount, 'price.earlyBird', Price::PRICE_EARLY_BIRD),
            Price::PRICE_SPECIAL_EARLY_BIRD => new Price(
                $specialEarlyBirdPriceAmount,
                'price.specialEarlyBird',
                Price::PRICE_SPECIAL_EARLY_BIRD,
            ),
        ];

        self::assertEquals($expected, $result);
    }
}
