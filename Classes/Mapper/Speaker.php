<?php

declare(strict_types=1);

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;

/**
 * This class represents a mapper for speakers.
 *
 * @extends AbstractDataMapper<\Tx_Seminars_Model_Speaker>
 */
class Tx_Seminars_Mapper_Speaker extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_speakers';

    /**
     * @var class-string<\Tx_Seminars_Model_Speaker> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Seminars_Model_Speaker::class;

    /**
     * @var array<string, class-string<AbstractDataMapper>>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'skills' => \Tx_Seminars_Mapper_Skill::class,
        'owner' => FrontEndUserMapper::class,
    ];
}
