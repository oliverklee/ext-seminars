<?php

declare(strict_types=1);

/**
 * This interface is used for models having a title and is needed for the CommaSeparatedTitles view helper.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
interface Tx_Seminars_Interface_Titled
{
    /**
     * Returns our title.
     *
     * @return string our title, will not be empty
     */
    public function getTitle(): string;
}
