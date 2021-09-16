<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\LegacyUnit\Fixtures\Model;

use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a titled model for testing purposes.
 */
class TitledTestingModel extends AbstractModel implements Titled
{
    /**
     * Returns our title.
     *
     * @return string our title, will not be empty
     */
    public function getTitle(): string
    {
        return $this->getAsString('title');
    }
}
