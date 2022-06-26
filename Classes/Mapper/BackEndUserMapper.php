<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\BackEndUser;

/**
 * This class represents a mapper for back-end users.
 *
 * @extends AbstractDataMapper<BackEndUser>
 */
class BackEndUserMapper extends AbstractDataMapper
{
    protected $tableName = 'be_users';

    protected $modelClassName = BackEndUser::class;

    protected $relations = [
        'usergroup' => BackEndUserGroupMapper::class,
    ];
}
