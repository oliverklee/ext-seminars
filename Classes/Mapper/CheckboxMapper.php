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
    /**
     * @var non-empty-string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_checkboxes';

    /**
     * @var class-string<Checkbox> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = Checkbox::class;

    /**
     * @var array<non-empty-string, class-string>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'owner' => FrontEndUserMapper::class,
    ];
}
