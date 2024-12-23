<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\Registration;

/**
 * This class represents a mapper for registrations.
 *
 * @extends AbstractDataMapper<Registration>
 */
class RegistrationMapper extends AbstractDataMapper
{
    protected $tableName = 'tx_seminars_attendances';

    protected $modelClassName = Registration::class;

    protected $relations = [
        'seminar' => EventMapper::class,
        'user' => FrontEndUserMapper::class,
        'additional_persons' => FrontEndUserMapper::class,
    ];
}
