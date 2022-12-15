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
    protected $tableName = 'tx_seminars_sites';

    protected $modelClassName = Place::class;
}
