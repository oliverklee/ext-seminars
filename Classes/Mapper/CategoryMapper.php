<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\Category;

/**
 * This class represents a mapper for categories.
 *
 * @extends AbstractDataMapper<Category>
 */
class CategoryMapper extends AbstractDataMapper
{
    protected $tableName = 'tx_seminars_categories';

    protected $modelClassName = Category::class;
}
