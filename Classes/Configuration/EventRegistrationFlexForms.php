<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration;

/**
 * This class provides functions for building FlexForms.
 *
 * @internal
 */
class EventRegistrationFlexForms
{
    private const LOCALLANG_FILE_PREFIX = 'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:';
    private const LABEL_KEY_PREFIX = 'plugin.eventRegistration.settings.fieldsToShow.';
    protected const AVAILABLE_FIELDS = [
        'inputFields' => [
            'attendanceMode',
            'seats',
            'registeredThemselves',
            'attendeesNames',
            'interests',
            'expectations',
            'backgroundKnowledge',
            'knownFrom',
            'comments',
            'priceCode',
            'orderReference',
        ],
        'billingAddress' => [
            'billingCompany',
            'billingFullName',
            'billingStreetAddress',
            'billingZipCode',
            'billingCity',
            'billingCountry',
            'billingPhoneNumber',
            'billingEmailAddress',
        ],
        'confirmationPage' => [
            'personalData',
            'consentedToTermsAndConditions',
        ],
    ];

    /**
     * Sets the selectable items for the fields to display in `$configuration`.
     *
     * @param array<string, array<string, string>> $configuration
     */
    public function buildFields(array &$configuration): void
    {
        /** @var array<int, array{0: non-empty-string, 1: non-empty-string}> $items */
        $items = [];
        foreach (static::AVAILABLE_FIELDS as $groupKey => $fields) {
            foreach ($fields as $fieldKey) {
                $label = self::LOCALLANG_FILE_PREFIX . self::LABEL_KEY_PREFIX . $fieldKey;
                $items[] = [$label, $fieldKey, '', $groupKey];
            }
        }

        $configuration['items'] = $items;
    }
}
