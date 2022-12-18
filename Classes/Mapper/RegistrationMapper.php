<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\Model\Registration;
use TYPO3\CMS\Core\Database\Connection;

/**
 * This class represents a mapper for registrations.
 *
 * @extends AbstractDataMapper<Registration>
 */
class RegistrationMapper extends AbstractDataMapper
{
    protected $tableName = 'tx_seminars_attendances';

    protected $modelClassName = Registration::class;

    protected $relations = [
        'seminar' => EventMapper::class,
        'user' => FrontEndUserMapper::class,
        'method_of_payment' => PaymentMethodMapper::class,
        'lodgings' => LodgingMapper::class,
        'foods' => FoodMapper::class,
        'checkboxes' => CheckboxMapper::class,
        'additional_persons' => FrontEndUserMapper::class,
    ];

    public function countByFrontEndUser(FrontEndUser $user): int
    {
        /** @var Connection $connection */
        $connection = $this->getConnectionForTable($this->getTableName());
        return $connection->count('*', $this->getTableName(), ['user' => $user->getUid()]);
    }
}
