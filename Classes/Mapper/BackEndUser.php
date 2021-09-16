<?php

declare(strict_types=1);

use OliverKlee\Oelib\Mapper\AbstractDataMapper;

/**
 * This class represents a mapper for back-end users.
 *
 * @extends AbstractDataMapper<\Tx_Seminars_Model_BackEndUser>
 */
class Tx_Seminars_Mapper_BackEndUser extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'be_users';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Seminars_Model_BackEndUser::class;

    /**
     * @var array<string, class-string<AbstractDataMapper>>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'usergroup' => \Tx_Seminars_Mapper_BackEndUserGroup::class,
    ];
}
