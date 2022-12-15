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
    protected $tableName = 'tx_seminars_target_groups';

    protected $modelClassName = TargetGroup::class;
}
