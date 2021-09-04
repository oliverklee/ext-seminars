<?php

declare(strict_types=1);

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\FrontEndUserMapper as OelibFrontEndUserMapper;

/**
 * This class represents a mapper for front-end users.
 *
 * @extends OelibFrontEndUserMapper<\Tx_Seminars_Model_FrontEndUser>
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_Mapper_FrontEndUser extends OelibFrontEndUserMapper
{
    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Seminars_Model_FrontEndUser::class;

    /**
     * @var array<string, class-string<AbstractDataMapper>>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'usergroup' => \Tx_Seminars_Mapper_FrontEndUserGroup::class,
        'tx_seminars_registration' => \Tx_Seminars_Mapper_Registration::class,
    ];
}
