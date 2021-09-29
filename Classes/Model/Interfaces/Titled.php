<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model\Interfaces;

/**
 * This interface is used for models having a title and is needed for the `CommaSeparatedTitles` view helper.
 */
interface Titled
{
    public function getTitle(): string;
}
