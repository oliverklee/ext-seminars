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
    /**
     * @var non-empty-string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_organizers';

    /**
     * @var class-string<Organizer> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = Organizer::class;
}
