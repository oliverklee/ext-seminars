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
    protected $tableName = 'fe_users';

    protected $modelClassName = FrontEndUser::class;

    protected $relations = [
        'usergroup' => FrontEndUserGroupMapper::class,
        'tx_seminars_registration' => RegistrationMapper::class,
    ];

    protected $additionalKeys = ['username'];

    /**
     * Finds a front-end user by username. Hidden user records will be retrieved as well.
     *
     * @param non-empty-string $userName username, case-insensitive
     *
     * @throws NotFoundException if there is no front-end user with the provided username in the database
     */
    public function findByUserName(string $userName): FrontEndUser
    {
        /** @var FrontEndUser $result */
        $result = $this->findOneByKey('username', $userName);

        return $result;
    }
}
