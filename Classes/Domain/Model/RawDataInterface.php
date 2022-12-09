<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

/**
 * Adds raw data methods (particularly helpful for creating icons in the backend).
 *
 * The default implementation is the `RawDataTrait`.
 *
 * @internal
 */
interface RawDataInterface
{
    /**
     * Returns the raw data as it is stored in the database.
     *
     * @return array<string, string|int|float|null>|null
     *
     * @internal
     */
    public function getRawData(): ?array;

    /**
     * Sets the raw data as it is stored in the database.
     *
     * @param array<string, string|int|float|null> $rawData
     *
     * @internal
     */
    public function setRawData(array $rawData): void;
}
