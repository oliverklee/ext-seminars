<?php

declare(strict_types=1);

return [
    OliverKlee\Seminars\Domain\Model\EventType::class => [
        'tableName' => 'tx_seminars_event_types',
    ],
    OliverKlee\Seminars\Domain\Model\Speaker::class => [
        'tableName' => 'tx_seminars_speakers',
        'properties' => [
            'name' => ['fieldName' => 'title'],
            'emailAddress' => ['fieldName' => 'email'],
        ],
    ],
];
