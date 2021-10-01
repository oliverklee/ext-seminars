<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;

/**
 * This class represents a mapper for places.
 *
 * @extends AbstractDataMapper<\Tx_Seminars_Model_Place>
 */
class PlaceMapper extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_sites';

    /**
     * @var class-string<\Tx_Seminars_Model_Place> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Seminars_Model_Place::class;

    /**
     * @var array<non-empty-string, class-string>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'owner' => FrontEndUserMapper::class,
    ];
}
