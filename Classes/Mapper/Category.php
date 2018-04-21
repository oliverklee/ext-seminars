<?php

/**
 * This class represents a mapper for categories.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Mapper_Category extends Tx_Oelib_DataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_categories';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = Tx_Seminars_Model_Category::class;
}
