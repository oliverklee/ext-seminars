<?php

declare(strict_types=1);

return [
    \OliverKlee\Seminars\Domain\Model\Event\Event::class => [
        'tableName' => 'tx_seminars_seminars',
        'recordType' => 'object_type',
        'subclasses' => [
            \OliverKlee\Seminars\Domain\Model\Event\EventInterface::TYPE_SINGLE_EVENT
            => \OliverKlee\Seminars\Domain\Model\Event\SingleEvent::class,
            \OliverKlee\Seminars\Domain\Model\Event\EventInterface::TYPE_EVENT_TOPIC
            => \OliverKlee\Seminars\Domain\Model\Event\EventTopic::class,
            \OliverKlee\Seminars\Domain\Model\Event\EventInterface::TYPE_EVENT_DATE
            => \OliverKlee\Seminars\Domain\Model\Event\EventDate::class,
        ],
    ],
    \OliverKlee\Seminars\Domain\Model\Event\SingleEvent::class => [
        'tableName' => 'tx_seminars_seminars',
        'recordType' => \OliverKlee\Seminars\Domain\Model\Event\EventInterface::TYPE_SINGLE_EVENT,
        'properties' => [
            'internalTitle' => ['fieldName' => 'title'],
            'start' => ['fieldName' => 'begin_date'],
            'end' => ['fieldName' => 'end_date'],
            'earlyBirdDeadline' => ['fieldName' => 'deadline_early_bird'],
            'registrationDeadline' => ['fieldName' => 'deadline_registration'],
        ],
    ],
    \OliverKlee\Seminars\Domain\Model\Event\EventTopic::class => [
        'tableName' => 'tx_seminars_seminars',
        'recordType' => \OliverKlee\Seminars\Domain\Model\Event\EventInterface::TYPE_EVENT_TOPIC,
        'properties' => [
            'internalTitle' => ['fieldName' => 'title'],
        ],
    ],
    \OliverKlee\Seminars\Domain\Model\Event\EventDate::class => [
        'tableName' => 'tx_seminars_seminars',
        'recordType' => \OliverKlee\Seminars\Domain\Model\Event\EventInterface::TYPE_EVENT_DATE,
        'properties' => [
            'internalTitle' => ['fieldName' => 'title'],
            'topic' => ['fieldName' => 'topic'],
            'start' => ['fieldName' => 'begin_date'],
            'end' => ['fieldName' => 'end_date'],
            'earlyBirdDeadline' => ['fieldName' => 'deadline_early_bird'],
            'registrationDeadline' => ['fieldName' => 'deadline_registration'],
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
