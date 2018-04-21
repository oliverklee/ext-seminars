<?php

/**
 * This class represents a mapper for back-end user groups.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_Mapper_BackEndUserGroup extends Tx_Oelib_Mapper_BackEndUserGroup
{
    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = Tx_Seminars_Model_BackEndUserGroup::class;

    /**
     * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'subgroup' => Tx_Seminars_Mapper_BackEndUserGroup::class,
    ];
}
