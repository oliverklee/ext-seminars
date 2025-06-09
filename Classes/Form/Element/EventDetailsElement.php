<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Form\Element;

use TYPO3\CMS\Backend\Form\Element\GroupElement;

class EventDetailsElement extends GroupElement
{
    /**
     * @var non-empty-string
     */
    private const TABLE_NAME = 'tx_seminars_attendances';

    /**
     * @var non-empty-string
     */
    private const FIELD_NAME = 'seminar';

    public function render(): array
    {
        $tableName = $this->data['tableName'] ?? '';
        if ($tableName !== self::TABLE_NAME) {
            throw new \RuntimeException(
                'EventDetailsElement can only be used for the "' . self::TABLE_NAME . '" table.',
                1752769757
            );
        }
        $fieldName = $this->data['fieldName'] ?? '';
        if ($fieldName !== self::FIELD_NAME) {
            throw new \RuntimeException(
                'EventDetailsElement can only be used for the "' . self::FIELD_NAME . '" field.',
                1752769855
            );
        }

        $result = parent::render();
        $originalHtml = $result['html'] ?? '';

        $eventData = $this->data['databaseRow']['seminar'][0]['row'] ?? null;
        if (\is_array($eventData)) {
            $eventDataParts = [];
            $dateFormat = $this->getDateFormat();
            $timestamp = (int)($eventData['begin_date'] ?? 0);
            $eventDate = \htmlspecialchars(
                \date($dateFormat, $timestamp),
                ENT_QUOTES | ENT_HTML5
            );
            $eventTitle = \htmlspecialchars($eventData['title'] ?? '', ENT_QUOTES | ENT_HTML5);
            $eventUid = '[' . ($eventData['uid'] ?? 0) . '] ';
            $additionalEventInformation = '<p>' . $eventTitle . ' ' . $eventDate . ' ' . $eventUid . '</p>';

            $result['html'] = $additionalEventInformation . $originalHtml;
        }

        return $result;
    }

    private function getDateFormat(): string
    {
        return $GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] ?? 'Y-m-d';
    }
}
