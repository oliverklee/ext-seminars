<?php

declare(strict_types=1);

use OliverKlee\Oelib\Mapper\BackEndUserGroupMapper as OelibBackEndUserGroupMapper;

/**
 * This class represents a mapper for back-end user groups.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_Mapper_BackEndUserGroup extends OelibBackEndUserGroupMapper
{
    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Seminars_Model_BackEndUserGroup::class;

    /**
     * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'subgroup' => \Tx_Seminars_Mapper_BackEndUserGroup::class,
    ];
}
