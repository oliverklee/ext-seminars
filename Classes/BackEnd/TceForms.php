<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This provides some helper functions to create parts of the TCA/TCE dynamically.
 */
class TceForms
{
    /**
     * Creates the values for a language selector in the TCA, using the alpha 2 codes as array keys.
     *
     * @param array[] $parameters
     *
     * @return void
     */
    public function createLanguageSelector(array &$parameters)
    {
        $items = [['', '']];

        /** @var string[] $language */
        foreach (self::findAllLanguages() as $language) {
            $items[] = [$language['lg_name_local'], $language['lg_iso_2']];
        }

        $parameters['items'] = $items;
    }

    /**
     * Creates the values for a country selector in the TCA, using the alpha 2 codes as array keys.
     *
     * @param array[] $parameters
     *
     * @return void
     */
    public function createCountrySelector(array &$parameters)
    {
        $items = [['', '']];

        /** @var string[] $country */
        foreach (self::findAllCountries() as $country) {
            $items[] = [$country['cn_short_local'], $country['cn_iso_2']];
        }

        $parameters['items'] = $items;
    }

    private static function findAllLanguages(): array
    {
        $table = 'static_languages';

        return self::getConnectionForTable($table)
            ->select(['*'], $table, [], [], ['lg_name_local' => 'ASC'])->fetchAll();
    }

    private static function findAllCountries(): array
    {
        $table = 'static_countries';

        return self::getConnectionForTable($table)
            ->select(['*'], $table, [], [], ['cn_short_local' => 'ASC'])->fetchAll();
    }

    private static function getConnectionForTable(string $table): Connection
    {
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);

        return $pool->getConnectionForTable($table);
    }
}
