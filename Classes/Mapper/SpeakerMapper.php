<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Seminars\Model\Speaker;

/**
 * This class represents a mapper for speakers.
 *
 * @extends AbstractDataMapper<Speaker>
 */
class SpeakerMapper extends AbstractDataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_speakers';

    /**
     * @var class-string<Speaker> the model class name for this mapper, must not be empty
     */
    protected $modelClassName = Speaker::class;

    /**
     * @var array<non-empty-string, class-string>
     *      the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'skills' => SkillMapper::class,
        'owner' => FrontEndUserMapper::class,
    ];
}
