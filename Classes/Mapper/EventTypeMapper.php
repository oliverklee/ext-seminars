<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\EventType;

/**
 * This class represents a mapper for event types.
 *
 * @extends AbstractDataMapper<EventType>
 */
class EventTypeMapper extends AbstractDataMapper
{
    /**
     * @var non-empty-string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_event_types';

    /**
     * @var class-string<EventType> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = EventType::class;
}
