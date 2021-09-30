<?php

declare(strict_types=1);

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Mapper\EventMapper;

/**
 * This class represents a mapper for time-slots.
 *
 * @extends AbstractDataMapper<\Tx_Seminars_Model_TimeSlot>
 */
class Tx_Seminars_Mapper_TimeSlot extends AbstractDataMapper
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
        'speakers' => \Tx_Seminars_Mapper_Speaker::class,
        'place' => \Tx_Seminars_Mapper_Place::class,
        'seminar' => EventMapper::class,
    ];
}
