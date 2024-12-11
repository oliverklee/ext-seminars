<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration\Traits;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Interfaces\Configuration;

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
}
