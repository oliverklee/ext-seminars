<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration\Traits;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Interfaces\Configuration;
use OliverKlee\Seminars\Service\DateFormatConverter;

/**
 * This trait provides access to the TypoScript configuration for `plugin.tx_seminars`.
 *
 * @internal
 */
trait SharedPluginConfiguration
{
    private ?Configuration $sharedPluginConfiguration = null;

    protected function getSharedConfiguration(): Configuration
    {
        if (!$this->sharedPluginConfiguration instanceof Configuration) {
            $this->sharedPluginConfiguration = ConfigurationRegistry::get('plugin.tx_seminars');
        }

        return $this->sharedPluginConfiguration;
    }

    /**
     * Returns the date format for a full date (with year, month and day) for `date`.
     */
    protected function getDateFormat(): string
    {
        $oldFormat = $this->getSharedConfiguration()->getAsString('dateFormatYMD');
        if ($oldFormat === '') {
            $oldFormat = '%Y-%m-%d';
        }

        return DateFormatConverter::convert($oldFormat);
    }

    /**
     * Returns the time format for `date`.
     */
    protected function getTimeFormat(): string
    {
        $oldFormat = $this->getSharedConfiguration()->getAsString('timeFormat');
        if ($oldFormat === '') {
            $oldFormat = '%H:%M';
        }

        return DateFormatConverter::convert($oldFormat);
    }
}
