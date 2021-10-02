<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\Skill;

/**
 * This class represents a mapper for skills.
 *
 * @extends AbstractDataMapper<Skill>
 */
class SkillMapper extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_skills';

    /**
     * @var class-string<Skill> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = Skill::class;
}
