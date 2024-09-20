<?php

declare(strict_types=1);

use OliverKlee\Seminars\Domain\Model\AccommodationOption;
use OliverKlee\Seminars\Domain\Model\Event\Event;
use OliverKlee\Seminars\Domain\Model\Event\EventDate;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Domain\Model\Event\EventTopic;
use OliverKlee\Seminars\Domain\Model\Event\SingleEvent;
use OliverKlee\Seminars\Domain\Model\EventType;
use OliverKlee\Seminars\Domain\Model\FoodOption;
use OliverKlee\Seminars\Domain\Model\Organizer;
use OliverKlee\Seminars\Domain\Model\PaymentMethod;
use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use OliverKlee\Seminars\Domain\Model\RegistrationCheckbox;
use OliverKlee\Seminars\Domain\Model\Speaker;
use OliverKlee\Seminars\Domain\Model\Venue;

return [
    AccommodationOption::class => [
        'tableName' => 'tx_seminars_lodgings',
    ],
    Event::class => [
        'tableName' => 'tx_seminars_seminars',
        'recordType' => 'object_type',
        'subclasses' => [
            EventInterface::TYPE_SINGLE_EVENT => SingleEvent::class,
            EventInterface::TYPE_EVENT_TOPIC => EventTopic::class,
            EventInterface::TYPE_EVENT_DATE => EventDate::class,
        ],
        'properties' => [
            'ownerUid' => ['fieldName' => 'owner_feuser'],
        ],
    ],
    SingleEvent::class => [
        'tableName' => 'tx_seminars_seminars',
        'recordType' => EventInterface::TYPE_SINGLE_EVENT,
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
    EventTopic::class => [
        'tableName' => 'tx_seminars_seminars',
        'recordType' => EventInterface::TYPE_EVENT_TOPIC,
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
    EventDate::class => [
        'tableName' => 'tx_seminars_seminars',
        'recordType' => EventInterface::TYPE_EVENT_DATE,
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
    EventType::class => [
        'tableName' => 'tx_seminars_event_types',
    ],
    FoodOption::class => [
        'tableName' => 'tx_seminars_foods',
    ],
    Organizer::class => [
        'tableName' => 'tx_seminars_organizers',
        'properties' => [
            'name' => ['fieldName' => 'title'],
            'emailAddress' => ['fieldName' => 'email'],
        ],
    ],
    PaymentMethod::class => [
        'tableName' => 'tx_seminars_payment_methods',
    ],
    Registration::class => [
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
    RegistrationCheckbox::class => [
        'tableName' => 'tx_seminars_checkboxes',
    ],
    Speaker::class => [
        'tableName' => 'tx_seminars_speakers',
        'properties' => [
            'name' => ['fieldName' => 'title'],
            'emailAddress' => ['fieldName' => 'email'],
        ],
    ],
    Venue::class => [
        'tableName' => 'tx_seminars_sites',
        'properties' => [
            'fullAddress' => ['fieldName' => 'address'],
        ],
    ],
];
