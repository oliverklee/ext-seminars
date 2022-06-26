<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\Organizer;

/**
 * This class represents a mapper for organizers.
 *
 * @extends AbstractDataMapper<Organizer>
 */
class OrganizerMapper extends AbstractDataMapper
{
    protected $tableName = 'tx_seminars_organizers';

    protected $modelClassName = Organizer::class;
}
