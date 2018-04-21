<?php

/**
 * This class represents a mapper for target groups.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Mapper_TargetGroup extends Tx_Oelib_DataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_target_groups';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = Tx_Seminars_Model_TargetGroup::class;

    /**
     * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'owner' => Tx_Seminars_Mapper_FrontEndUser::class,
    ];
}
