<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration\Traits;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Interfaces\Configuration;

/**
 * This trait provides access to the TypoScript configuration for `plugin.tx_seminars`.
 */
trait SharedPluginConfiguration
{
    /**
     * @var Configuration|null
     */
    private $sharedPluginConfiguration = null;

    protected function getSharedConfiguration(): Configuration
    {
        if (!$this->sharedPluginConfiguration instanceof Configuration) {
            $this->sharedPluginConfiguration = ConfigurationRegistry::get('plugin.tx_seminars');
        }

        return $this->sharedPluginConfiguration;
    }

    /**
     * Returns the date format for a full date (with year, month and day) for `strftime`.
     */
    protected function getDateFormat(): string
    {
        return $this->getSharedConfiguration()->getAsString('dateFormatYMD') ?: '%Y-%m-%d';
    }

    /**
     * Returns the time format for `strftime`.
     */
    protected function getTimeFormat(): string
    {
        return $this->getSharedConfiguration()->getAsString('timeFormat') ?: '%H:%M';
    }
}
