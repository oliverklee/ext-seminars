<?php

declare(strict_types=1);

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\BackEndUserMapper as OelibBackEndUserMapper;

/**
 * This class represents a mapper for front-end user groups.
 *
 * @extends AbstractDataMapper<\Tx_Seminars_Model_FrontEndUserGroup>
 */
class Tx_Seminars_Mapper_FrontEndUserGroup extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'fe_groups';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Seminars_Model_FrontEndUserGroup::class;

    /**
     * @var array<string, class-string<AbstractDataMapper>>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'tx_seminars_reviewer' => OelibBackEndUserMapper::class,
        'tx_seminars_default_categories' => \Tx_Seminars_Mapper_Category::class,
        'tx_seminars_default_organizer' => \Tx_Seminars_Mapper_Organizer::class,
    ];
}
