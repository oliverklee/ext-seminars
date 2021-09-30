<?php

declare(strict_types=1);

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\CurrencyMapper;
use OliverKlee\Seminars\Mapper\EventMapper;
use TYPO3\CMS\Core\Database\Connection;

/**
 * This class represents a mapper for registrations.
 *
 * @extends AbstractDataMapper<\Tx_Seminars_Model_Registration>
 */
class Tx_Seminars_Mapper_Registration extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_attendances';

    /**
     * @var class-string<\Tx_Seminars_Model_Registration> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Seminars_Model_Registration::class;

    /**
     * @var array<string, class-string<AbstractDataMapper>>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'seminar' => EventMapper::class,
        'user' => \Tx_Seminars_Mapper_FrontEndUser::class,
        'currency' => CurrencyMapper::class,
        'method_of_payment' => \Tx_Seminars_Mapper_PaymentMethod::class,
        'lodgings' => \Tx_Seminars_Mapper_Lodging::class,
        'foods' => \Tx_Seminars_Mapper_Food::class,
        'checkboxes' => \Tx_Seminars_Mapper_Checkbox::class,
        'additional_persons' => \Tx_Seminars_Mapper_FrontEndUser::class,
    ];

    /**
     * @param Tx_Seminars_Model_FrontEndUser $user
     *
     * @return int
     */
    public function countByFrontEndUser(\Tx_Seminars_Model_FrontEndUser $user): int
    {
        /** @var Connection $connection */
        $connection = $this->getConnectionForTable($this->getTableName());
        return $connection->count('*', $this->getTableName(), ['user' => $user->getUid()]);
    }
}
