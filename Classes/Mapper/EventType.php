<?php

declare(strict_types=1);

use OliverKlee\Oelib\Mapper\AbstractDataMapper;

/**
 * This class represents a mapper for event types.
 *
 * @extends AbstractDataMapper<\Tx_Seminars_Model_EventType>
 */
class Tx_Seminars_Mapper_EventType extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_event_types';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Seminars_Model_EventType::class;
}
