<?php
declare(strict_types = 1);

/**
 * This class represents a mapper for speakers.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Mapper_Speaker extends \Tx_Oelib_DataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_speakers';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Seminars_Model_Speaker::class;

    /**
     * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'skills' => \Tx_Seminars_Mapper_Skill::class,
        'owner' => \Tx_Seminars_Mapper_FrontEndUser::class,
    ];
}
