<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Model\AbstractModel;

/**
 * This abstract class represents a time span.
 */
abstract class AbstractTimeSpan extends AbstractModel
{
    /**
     * @return int<0, max> our begin date as UNIX time-stamp, will be >= 0, 0 means "no begin date"
     */
    public function getBeginDateAsUnixTimeStamp(): int
    {
        return $this->getAsNonNegativeInteger('begin_date');
    }

    /**
     * @param int $beginDate our begin date as UNIX time-stamp, must be >= 0, 0 means "no begin date"
     */
    public function setBeginDateAsUnixTimeStamp(int $beginDate): void
    {
        if ($beginDate < 0) {
            throw new \InvalidArgumentException('The parameter $beginDate must be >= 0.', 1333293455);
        }

        $this->setAsInteger('begin_date', $beginDate);
    }

    /**
     * @return bool TRUE if this time-span has a begin date, FALSE otherwise
     */
    public function hasBeginDate(): bool
    {
        return $this->hasInteger('begin_date');
    }

    /**
     * @return int<0, max> our end date as UNIX time-stamp, will be >= 0, 0 means "no end date"
     */
    public function getEndDateAsUnixTimeStamp(): int
    {
        return $this->getAsNonNegativeInteger('end_date');
    }

    /**
     * @param int $endDate our end date as UNIX time-stamp, must be >= 0, 0 means "no end date"
     */
    public function setEndDateAsUnixTimeStamp(int $endDate): void
    {
        if ($endDate < 0) {
            throw new \InvalidArgumentException('The parameter $endDate must be >= 0.', 1333293465);
        }

        $this->setAsInteger('end_date', $endDate);
    }

    public function hasEndDate(): bool
    {
        return $this->hasInteger('end_date');
    }
}
