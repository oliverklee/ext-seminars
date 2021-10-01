<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Bag;

use OliverKlee\Seminars\OldModel\LegacySpeaker;

/**
 * This aggregate class holds a bunch of speaker objects and allows iterating over them.
 *
 * @extends AbstractBag<LegacySpeaker>
 */
class SpeakerBag extends AbstractBag
{
    /**
     * @var class-string<LegacySpeaker>
     */
    protected static $modelClassName = LegacySpeaker::class;

    /**
     * @var string the name of the main DB table from which we get the records for this bag
     */
    protected static $tableName = 'tx_seminars_speakers';
}
