<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\TimeSlot;

/**
 * This class represents a mapper for time-slots.
 *
 * @extends AbstractDataMapper<TimeSlot>
 */
class TimeSlotMapper extends AbstractDataMapper
{
    protected $tableName = 'tx_seminars_timeslots';

    protected $modelClassName = TimeSlot::class;

    protected $relations = [
        'speakers' => SpeakerMapper::class,
        'place' => PlaceMapper::class,
        'seminar' => EventMapper::class,
    ];
}
