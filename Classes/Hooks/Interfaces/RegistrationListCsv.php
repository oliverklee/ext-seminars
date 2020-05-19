<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks\Interfaces;

/**
 * Use this interface for hooks concerning the registration list CSV.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
interface RegistrationListCsv extends Hook
{
    /**
     * Modifies the rendered CSV string.
     *
     * This allows modifying the complete CSV text right before it is delivered.
     *
     * @param string $csv the CSV text produced by `AbstractRegistrationListView::render()`
     * @param \Tx_Seminars_Csv_AbstractRegistrationListView $registrationList the CSV data provider
     *
     * @return string the modified CSV text to use
     */
    public function modifyCsv(string $csv, \Tx_Seminars_Csv_AbstractRegistrationListView $registrationList): string;
}
