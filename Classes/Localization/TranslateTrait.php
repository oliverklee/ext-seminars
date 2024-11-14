<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Localization;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * This trait provides a `translate` method for classes that need to translate labels.
 *
 * @internal
 */
trait TranslateTrait
{
    /**
     * Retrieves the localized string for the given key within the `seminars` extension.
     *
     * Note: This method does not take the salutation mode (formal/informal) nor its suffixes into account.
     *
     * @param non-empty-string $key
     *
     * @return string the localized label, or the given key if there is no label with that key
     */
    protected function translate(string $key): string
    {
        $label = LocalizationUtility::translate($key, 'seminars');

        return (\is_string($label) && $label !== '') ? $label : $key;
    }
}
