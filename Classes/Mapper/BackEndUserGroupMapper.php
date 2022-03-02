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
    /**
     * @var non-empty-string the name of the database table for this mapper
     */
    protected $tableName = 'be_groups';

    /**
     * @var class-string<BackEndUserGroup> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = BackEndUserGroup::class;

    /**
     * @var array<non-empty-string, class-string>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'subgroup' => BackEndUserGroupMapper::class,
    ];
}
