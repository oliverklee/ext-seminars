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
    protected $tableName = 'tx_seminars_skills';

    protected $modelClassName = Skill::class;
}
