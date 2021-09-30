<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\BackEndUserMapper as OelibBackEndUserMapper;
use OliverKlee\Seminars\Model\FrontEndUserGroup;

/**
 * This class represents a mapper for front-end user groups.
 *
 * @extends AbstractDataMapper<FrontEndUserGroup>
 */
class FrontEndUserGroupMapper extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'fe_groups';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = FrontEndUserGroup::class;

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
