<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\Lodging;

/**
 * This class represents a mapper for lodgings.
 *
 * @extends AbstractDataMapper<Lodging>
 */
class LodgingMapper extends AbstractDataMapper
{
    protected $tableName = 'tx_seminars_lodgings';

    protected $modelClassName = Lodging::class;
}
