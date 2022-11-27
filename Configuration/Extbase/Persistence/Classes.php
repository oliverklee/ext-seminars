<?php

declare(strict_types=1);

return [
    \OliverKlee\Seminars\Domain\Model\AccommodationOption::class => [
        'tableName' => 'tx_seminars_lodgings',
    ],
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
        'properties' => [
            'ownerUid' => ['fieldName' => 'owner_feuser'],
        ],
    ],
    \OliverKlee\Seminars\Domain\Model\Event\SingleEvent::class => [
        'tableName' => 'tx_seminars_seminars',
        'recordType' => \OliverKlee\Seminars\Domain\Model\Event\EventInterface::TYPE_SINGLE_EVENT,
        'properties' => [
            'internalTitle' => ['fieldName' => 'title'],
            'start' => ['fieldName' => 'begin_date'],
            'end' => ['fieldName' => 'end_date'],
            'registrationStart' => ['fieldName' => 'begin_date_registration'],
            'earlyBirdDeadline' => ['fieldName' => 'deadline_early_bird'],
            'registrationDeadline' => ['fieldName' => 'deadline_registration'],
            'registrationRequired' => ['fieldName' => 'needs_registration'],
            'waitingList' => ['fieldName' => 'queue_size'],
            'minimumNumberOfRegistrations' => ['fieldName' => 'attendees_min'],
            'maximumNumberOfRegistrations' => ['fieldName' => 'attendees_max'],
            'standardPrice' => ['fieldName' => 'price_regular'],
            'earlyBirdPrice' => ['fieldName' => 'price_regular_early'],
            'venues' => ['fieldName' => 'place'],
            'ownerUid' => ['fieldName' => 'owner_feuser'],
            'additionalTerms' => ['fieldName' => 'uses_terms_2'],
            'multipleRegistrationPossible' => ['fieldName' => 'allows_multiple_registrations'],
            'numberOfOfflineRegistrations' => ['fieldName' => 'offline_attendees'],
            'status' => ['fieldName' => 'cancelled'],
            'specialPrice' => ['fieldName' => 'price_special'],
            'specialEarlyBirdPrice' => ['fieldName' => 'price_special_early'],
            'accommodationOptions' => ['fieldName' => 'lodgings'],
            'foodOptions' => ['fieldName' => 'foods'],
            'registrationCheckboxes' => ['fieldName' => 'checkboxes'],
        ],
    ],
    \OliverKlee\Seminars\Domain\Model\Event\EventTopic::class => [
        'tableName' => 'tx_seminars_seminars',
        'recordType' => \OliverKlee\Seminars\Domain\Model\Event\EventInterface::TYPE_EVENT_TOPIC,
        'properties' => [
            'internalTitle' => ['fieldName' => 'title'],
            'standardPrice' => ['fieldName' => 'price_regular'],
            'earlyBirdPrice' => ['fieldName' => 'price_regular_early'],
            'ownerUid' => ['fieldName' => 'owner_feuser'],
            'additionalTerms' => ['fieldName' => 'uses_terms_2'],
            'multipleRegistrationPossible' => ['fieldName' => 'allows_multiple_registrations'],
            'specialPrice' => ['fieldName' => 'price_special'],
            'specialEarlyBirdPrice' => ['fieldName' => 'price_special_early'],
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
            'registrationStart' => ['fieldName' => 'begin_date_registration'],
            'earlyBirdDeadline' => ['fieldName' => 'deadline_early_bird'],
            'registrationDeadline' => ['fieldName' => 'deadline_registration'],
            'registrationRequired' => ['fieldName' => 'needs_registration'],
            'waitingList' => ['fieldName' => 'queue_size'],
            'minimumNumberOfRegistrations' => ['fieldName' => 'attendees_min'],
            'maximumNumberOfRegistrations' => ['fieldName' => 'attendees_max'],
            'venues' => ['fieldName' => 'place'],
            'ownerUid' => ['fieldName' => 'owner_feuser'],
            'numberOfOfflineRegistrations' => ['fieldName' => 'offline_attendees'],
            'status' => ['fieldName' => 'cancelled'],
            'accommodationOptions' => ['fieldName' => 'lodgings'],
            'foodOptions' => ['fieldName' => 'foods'],
            'registrationCheckboxes' => ['fieldName' => 'checkboxes'],
        ],
    ],
    \OliverKlee\Seminars\Domain\Model\EventType::class => [
        'tableName' => 'tx_seminars_event_types',
    ],
    \OliverKlee\Seminars\Domain\Model\FoodOption::class => [
        'tableName' => 'tx_seminars_foods',
    ],
    \OliverKlee\Seminars\Domain\Model\Organizer::class => [
        'tableName' => 'tx_seminars_organizers',
        'properties' => [
            'name' => ['fieldName' => 'title'],
            'emailAddress' => ['fieldName' => 'email'],
        ],
    ],
    \OliverKlee\Seminars\Domain\Model\PaymentMethod::class => [
        'tableName' => 'tx_seminars_payment_methods',
    ],
    \OliverKlee\Seminars\Domain\Model\Registration\Registration::class => [
        'tableName' => 'tx_seminars_attendances',
        'properties' => [
            'event' => ['fieldName' => 'seminar'],
            'onWaitingList' => ['fieldName' => 'registration_queue'],
            'comments' => ['fieldName' => 'notes'],
            'accommodationOptions' => ['fieldName' => 'lodgings'],
            'foodOptions' => ['fieldName' => 'foods'],
            'registrationCheckboxes' => ['fieldName' => 'checkboxes'],
            'billingCompany' => ['fieldName' => 'company'],
            'billingFullName' => ['fieldName' => 'name'],
            'billingStreetAddress' => ['fieldName' => 'address'],
            'billingZipCode' => ['fieldName' => 'zip'],
            'billingCity' => ['fieldName' => 'city'],
            'billingCountry' => ['fieldName' => 'country'],
            'billingPhoneNumber' => ['fieldName' => 'telephone'],
            'billingEmailAddress' => ['fieldName' => 'email'],
            'paymentMethod' => ['fieldName' => 'method_of_payment'],
            'humanReadablePrice' => ['fieldName' => 'price'],
        ],
    ],
    \OliverKlee\Seminars\Domain\Model\RegistrationCheckbox::class => [
        'tableName' => 'tx_seminars_checkboxes',
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
