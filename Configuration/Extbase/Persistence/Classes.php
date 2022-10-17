<?php

declare(strict_types=1);

return [
    \OliverKlee\Seminars\Domain\Model\Event::class => [
        'tableName' => 'tx_seminars_seminars',
        'recordType' => 'object_type',
        'subclasses' => [
            \OliverKlee\Seminars\Domain\Model\EventInterface::TYPE_SINGLE_EVENT
            => \OliverKlee\Seminars\Domain\Model\SingleEvent::class,
            \OliverKlee\Seminars\Domain\Model\EventInterface::TYPE_EVENT_TOPIC
            => \OliverKlee\Seminars\Domain\Model\EventTopic::class,
            \OliverKlee\Seminars\Domain\Model\EventInterface::TYPE_EVENT_DATE
            => \OliverKlee\Seminars\Domain\Model\EventDate::class,
        ],
        'properties' => [
            'internalTitle' => ['fieldName' => 'title'],
        ],
    ],
    \OliverKlee\Seminars\Domain\Model\SingleEvent::class => [
        'tableName' => 'tx_seminars_seminars',
        'recordType' => \OliverKlee\Seminars\Domain\Model\EventInterface::TYPE_SINGLE_EVENT,
        'properties' => [
            'internalTitle' => ['fieldName' => 'title'],
        ],
    ],
    \OliverKlee\Seminars\Domain\Model\EventTopic::class => [
        'tableName' => 'tx_seminars_seminars',
        'recordType' => \OliverKlee\Seminars\Domain\Model\EventInterface::TYPE_EVENT_TOPIC,
        'properties' => [
            'internalTitle' => ['fieldName' => 'title'],
        ],
    ],
    \OliverKlee\Seminars\Domain\Model\EventDate::class => [
        'tableName' => 'tx_seminars_seminars',
        'recordType' => \OliverKlee\Seminars\Domain\Model\EventInterface::TYPE_EVENT_DATE,
        'properties' => [
            'internalTitle' => ['fieldName' => 'title'],
        ],
    ],
    \OliverKlee\Seminars\Domain\Model\EventType::class => [
        'tableName' => 'tx_seminars_event_types',
    ],
    \OliverKlee\Seminars\Domain\Model\Organizer::class => [
        'tableName' => 'tx_seminars_organizers',
        'properties' => [
            'name' => ['fieldName' => 'title'],
            'emailAddress' => ['fieldName' => 'email'],
        ],
    ],
    \OliverKlee\Seminars\Domain\Model\Speaker::class => [
        'tableName' => 'tx_seminars_speakers',
        'properties' => [
            'name' => ['fieldName' => 'title'],
            'emailAddress' => ['fieldName' => 'email'],
        ],
    ],
    \OliverKlee\Seminars\Domain\Model\Venue::class => [
        'tableName' => 'tx_seminars_sites',
    ],
];
