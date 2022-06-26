<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\Food;

/**
 * This class represents a mapper for food.
 *
 * @extends AbstractDataMapper<Food>
 */
class FoodMapper extends AbstractDataMapper
{
    protected $tableName = 'tx_seminars_foods';

    protected $modelClassName = Food::class;
}
