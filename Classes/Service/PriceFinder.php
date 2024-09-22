<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Service;

use OliverKlee\Seminars\Domain\Model\Event\EventDateInterface;
use OliverKlee\Seminars\Domain\Model\Price;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This service finds applicable prices for an event or a registration.
 */
class PriceFinder implements SingletonInterface
{
    /**
     * mapping from the non-early-bird version of a price to the early-bird version
     *
     * @var array<Price::PRICE_*, Price::PRICE_*>
     */
    private const EARLY_BIRD_PRICE_MAPPING = [
        Price::PRICE_STANDARD => Price::PRICE_EARLY_BIRD,
        Price::PRICE_SPECIAL => Price::PRICE_SPECIAL_EARLY_BIRD,
    ];

    private \DateTimeImmutable $now;

    public function __construct()
    {
        $now = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'full');
        \assert($now instanceof \DateTimeImmutable);

        $this->now = $now;
    }

    /**
     * Finds the applicable prices for the given event right now, taking the early bird deadline into account
     * (if there is one).
     *
     * @return array<Price::PRICE_*, Price>
     */
    public function findApplicablePrices(EventDateInterface $event): array
    {
        $prices = $event->getAllPrices();
        if (!$this->earlyBirdPricesApply($event)) {
            return $this->dropEarlyBirdPrices($prices);
        }

        return $this->replaceRegularPricesWithEarlyBirdPrices($prices);
    }

    private function earlyBirdPricesApply(EventDateInterface $event): bool
    {
        $deadline = $event->getEarlyBirdDeadline();
        if (!$deadline instanceof \DateTimeInterface) {
            return false;
        }

        return $this->now < $deadline;
    }

    /**
     * @param array<Price::PRICE_*, Price> $prices
     *
     * @return array<Price::PRICE_*, Price>
     */
    private function dropEarlyBirdPrices(array $prices): array
    {
        $pricesWithoutEarlyBird = $prices;

        foreach (self::EARLY_BIRD_PRICE_MAPPING as $earlyBirdPriceCode) {
            unset($pricesWithoutEarlyBird[$earlyBirdPriceCode]);
        }

        return $pricesWithoutEarlyBird;
    }

    /**
     * @param array<Price::PRICE_*, Price> $prices
     *
     * @return array<Price::PRICE_*, Price>
     */
    private function replaceRegularPricesWithEarlyBirdPrices(array $prices): array
    {
        $pricesWithoutRegular = $prices;

        foreach (self::EARLY_BIRD_PRICE_MAPPING as $regularPriceCode => $earlyBirdPriceCode) {
            if (!isset($pricesWithoutRegular[$earlyBirdPriceCode])) {
                continue;
            }

            unset($pricesWithoutRegular[$regularPriceCode]);
        }

        return $pricesWithoutRegular;
    }
}
