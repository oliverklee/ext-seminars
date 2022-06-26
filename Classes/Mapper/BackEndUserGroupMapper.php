<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\BackEndUserGroup;

/**
 * This class represents a mapper for back-end user groups.
 *
 * @extends AbstractDataMapper<BackEndUserGroup>
 */
class BackEndUserGroupMapper extends AbstractDataMapper
{
    protected $tableName = 'be_groups';

    protected $modelClassName = BackEndUserGroup::class;

    protected $relations = [
        'subgroup' => BackEndUserGroupMapper::class,
    ];
}
