<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\FrontEndUser;

/**
 * This class represents a mapper for front-end users.
 *
 * @extends AbstractDataMapper<FrontEndUser>
 */
class FrontEndUserMapper extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'fe_users';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = FrontEndUser::class;

    /**
     * @var array<string, class-string<AbstractDataMapper>>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'usergroup' => FrontEndUserGroupMapper::class,
        'tx_seminars_registration' => RegistrationMapper::class,
    ];

    /**
     * @var array<int, string> the column names of additional string keys
     */
    protected $additionalKeys = ['username'];

    /**
     * Finds a front-end user by user name. Hidden user records will be retrieved as well.
     *
     * @param string $userName user name, case-insensitive, must not be empty
     *
     * @throws NotFoundException if there is no front-end user with the provided user name in the database
     */
    public function findByUserName(string $userName): FrontEndUser
    {
        /** @var FrontEndUser $result */
        $result = $this->findOneByKey('username', $userName);

        return $result;
    }
}
