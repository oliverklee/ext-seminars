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
    /**
     * @var non-empty-string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_categories';

    /**
     * @var class-string<Category> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = Category::class;
}
