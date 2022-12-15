<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\Checkbox;

/**
 * This class represents a mapper for checkboxes.
 *
 * @extends AbstractDataMapper<Checkbox>
 */
class CheckboxMapper extends AbstractDataMapper
{
    protected $tableName = 'tx_seminars_checkboxes';

    protected $modelClassName = Checkbox::class;
}
