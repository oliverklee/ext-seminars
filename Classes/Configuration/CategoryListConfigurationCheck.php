<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration;

/**
 * Configuration check for the category list.
 */
class CategoryListConfigurationCheck extends AbstractFrontEndConfigurationCheck
{
    protected function checkAllConfigurationValues(): void
    {
        $this->checkCommonFrontEndSettings();

        $this->checkRecursive();
        $this->checkTimeframeInList();

        $this->checkListPid();
    }
}
