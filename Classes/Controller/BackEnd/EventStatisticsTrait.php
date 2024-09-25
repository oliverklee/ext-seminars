<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller\BackEnd;

use OliverKlee\Seminars\Service\EventStatisticsCalculator;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Trait for getting the `EventStatisticsCalculator` injected..
 *
 * @phpstan-require-extends ActionController
 */
trait EventStatisticsTrait
{
    private EventStatisticsCalculator $eventStatisticsCalculator;

    public function injectEventStatisticsCalculator(EventStatisticsCalculator $calculator): void
    {
        $this->eventStatisticsCalculator = $calculator;
    }
}
