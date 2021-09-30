<?php

declare(strict_types=1);

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\BackEndUserGroup;

/**
 * This class represents a mapper for back-end user groups.
 *
 * @extends AbstractDataMapper<BackEndUserGroup>
 */
class Tx_Seminars_Mapper_BackEndUserGroup extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'be_groups';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = BackEndUserGroup::class;

    /**
     * @var array<string, class-string<AbstractDataMapper>>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'subgroup' => \Tx_Seminars_Mapper_BackEndUserGroup::class,
    ];
}
