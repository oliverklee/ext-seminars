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
    protected $tableName = 'tx_seminars_event_types';

    protected $modelClassName = EventType::class;
}
