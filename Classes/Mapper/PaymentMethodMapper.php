<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\PaymentMethod;

/**
 * This class represents a mapper for payment methods.
 *
 * @extends AbstractDataMapper<PaymentMethod>
 */
class PaymentMethodMapper extends AbstractDataMapper
{
    protected $tableName = 'tx_seminars_payment_methods';

    protected $modelClassName = PaymentMethod::class;
}
