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
    protected static string $modelClassName = LegacySpeaker::class;

    /**
     * @var non-empty-string
     */
    protected static string $tableName = 'tx_seminars_speakers';
}
