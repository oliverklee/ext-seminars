<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;

/**
 * This class represents a mapper for time-slots.
 *
 * @extends AbstractDataMapper<\Tx_Seminars_Model_TimeSlot>
 */
class TimeSlotMapper extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_timeslots';

    /**
     * @var class-string<\Tx_Seminars_Model_TimeSlot> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Seminars_Model_TimeSlot::class;

    /**
     * @var array<non-empty-string, class-string>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'speakers' => SpeakerMapper::class,
        'place' => PlaceMapper::class,
        'seminar' => EventMapper::class,
    ];
}
