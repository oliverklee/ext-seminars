<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

/**
 * Adds raw data methods (particularly helpful for creating icons in the backend).
 *
 * This is the default implementation of the `RawDataInterface`.
 *
 * @internal
 */
trait RawDataTrait
{
    /**
     * @var array<string, string|int|float|null>
     * @TYPO3\CMS\Extbase\Annotation\ORM\Transient
     * @internal
     */
    protected $rawData;

    /**
     * Returns the raw data as it is stored in the database.
     *
     * @return array<string, string|int|float|null>|null
     *
     * @internal
     */
    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    /**
     * Sets the raw data as it is stored in the database.
     *
     * @param array<string, string|int|float|null> $rawData
     *
     * @internal
     */
    public function setRawData(array $rawData): void
    {
        $this->rawData = $rawData;
    }
}
