<?php

declare(strict_types=1);

use OliverKlee\Oelib\Mapper\AbstractDataMapper;

/**
 * This class represents a mapper for time-slots.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Mapper_TimeSlot extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_timeslots';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Seminars_Model_TimeSlot::class;

    /**
     * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'speakers' => \Tx_Seminars_Mapper_Speaker::class,
        'place' => \Tx_Seminars_Mapper_Place::class,
        'seminar' => \Tx_Seminars_Mapper_Event::class,
    ];
}
