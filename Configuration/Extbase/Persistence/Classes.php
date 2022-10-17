<?php

declare(strict_types=1);

return [
    OliverKlee\Seminars\Domain\Model\Event::class => [
        'tableName' => 'tx_seminars_seminars',
        'properties' => [
            'internalTitle' => ['fieldName' => 'title'],
        ],
    ],
    OliverKlee\Seminars\Domain\Model\EventType::class => [
        'tableName' => 'tx_seminars_event_types',
    ],
    OliverKlee\Seminars\Domain\Model\Organizer::class => [
        'tableName' => 'tx_seminars_organizers',
        'properties' => [
            'name' => ['fieldName' => 'title'],
            'emailAddress' => ['fieldName' => 'email'],
        ],
    ],
    OliverKlee\Seminars\Domain\Model\Speaker::class => [
        'tableName' => 'tx_seminars_speakers',
        'properties' => [
            'name' => ['fieldName' => 'title'],
            'emailAddress' => ['fieldName' => 'email'],
        ],
    ],
    OliverKlee\Seminars\Domain\Model\Venue::class => [
        'tableName' => 'tx_seminars_sites',
    ],
];
