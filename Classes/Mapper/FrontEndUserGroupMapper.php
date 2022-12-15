<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\FrontEndUserGroup;

/**
 * This class represents a mapper for front-end user groups.
 *
 * @extends AbstractDataMapper<FrontEndUserGroup>
 */
class FrontEndUserGroupMapper extends AbstractDataMapper
{
    protected $tableName = 'fe_groups';

    protected $modelClassName = FrontEndUserGroup::class;

    protected $relations = [
        'tx_seminars_default_categories' => CategoryMapper::class,
        'tx_seminars_default_organizer' => OrganizerMapper::class,
    ];
}
