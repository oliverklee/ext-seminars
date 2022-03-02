<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\Place;

/**
 * This class represents a mapper for places.
 *
 * @extends AbstractDataMapper<Place>
 */
class PlaceMapper extends AbstractDataMapper
{
    /**
     * @var non-empty-string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_sites';

    /**
     * @var class-string<Place> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = Place::class;

    /**
     * @var array<non-empty-string, class-string>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'owner' => FrontEndUserMapper::class,
    ];
}
