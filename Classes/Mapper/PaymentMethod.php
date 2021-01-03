<?php

declare(strict_types=1);

use OliverKlee\Oelib\Mapper\AbstractDataMapper;

/**
 * This class represents a mapper for payment methods.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Mapper_PaymentMethod extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_payment_methods';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Seminars_Model_PaymentMethod::class;
}
