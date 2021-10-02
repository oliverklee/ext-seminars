<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\TargetGroup;

/**
 * This class represents a mapper for target groups.
 *
 * @extends AbstractDataMapper<TargetGroup>
 */
class TargetGroupMapper extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_target_groups';

    /**
     * @var class-string<TargetGroup> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = TargetGroup::class;

    /**
     * @var array<non-empty-string, class-string>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'owner' => FrontEndUserMapper::class,
    ];
}
