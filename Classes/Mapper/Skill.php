<?php

declare(strict_types=1);

use OliverKlee\Oelib\Mapper\AbstractDataMapper;

/**
 * This class represents a mapper for skills.
 *
 * @extends AbstractDataMapper<\Tx_Seminars_Model_Skill>
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Mapper_Skill extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_skills';

    /**
     * @var class-string<\Tx_Seminars_Model_Skill> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Seminars_Model_Skill::class;
}
