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
    protected $tableName = 'tx_seminars_speakers';

    protected $modelClassName = Speaker::class;
}
