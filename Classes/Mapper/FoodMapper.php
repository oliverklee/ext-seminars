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
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_foods';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = Food::class;
}
