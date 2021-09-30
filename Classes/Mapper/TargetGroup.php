<?php

declare(strict_types=1);

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;

/**
 * This class represents a mapper for target groups.
 *
 * @extends AbstractDataMapper<\Tx_Seminars_Model_TargetGroup>
 */
class Tx_Seminars_Mapper_TargetGroup extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_target_groups';

    /**
     * @var class-string<\Tx_Seminars_Model_TargetGroup> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Seminars_Model_TargetGroup::class;

    /**
     * @var array<non-empty-string, class-string>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'owner' => FrontEndUserMapper::class,
    ];
}
