<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BagBuilder;

use OliverKlee\Seminars\Bag\SpeakerBag;

/**
 * This builder class creates customized speaker bag objects.
 *
 * @extends AbstractBagBuilder<SpeakerBag>
 */
class SpeakerBagBuilder extends AbstractBagBuilder
{
    /**
     * @var class-string<SpeakerBag> class name of the bag class that will be built
     */
    protected string $bagClassName = SpeakerBag::class;

    /**
     * @var non-empty-string the table name of the bag to build
     */
    protected string $tableName = 'tx_seminars_speakers';
}
