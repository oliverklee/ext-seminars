<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\Service\EventStatisticsCalculator;

/**
 * Trait for getting the `EventStatisticsCalculator` injected..
 */
trait EventStatisticsTrait
{
    /**
     * @var EventStatisticsCalculator
     */
    private $eventStatisticsCalculator;

    public function injectEventStatisticsCalculator(EventStatisticsCalculator $calculator): void
    {
        $this->eventStatisticsCalculator = $calculator;
    }
}
