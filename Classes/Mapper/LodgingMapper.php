<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;

/**
 * This class represents a mapper for lodgings.
 *
 * @extends AbstractDataMapper<\Tx_Seminars_Model_Lodging>
 */
class LodgingMapper extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_lodgings';

    /**
     * @var class-string<\Tx_Seminars_Model_Lodging> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Seminars_Model_Lodging::class;
}
