<?php

declare(strict_types=1);

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\BackEndUserMapper as OelibBackEndUserMapper;

/**
 * This class represents a mapper for back-end users.
 *
 * @extends AbstractDataMapper<\Tx_Seminars_Model_BackEndUser>
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_Mapper_BackEndUser extends OelibBackEndUserMapper
{
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
