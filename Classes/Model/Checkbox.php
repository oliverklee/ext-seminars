<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a checkbox.
 */
class Checkbox extends AbstractModel implements Titled
{
    /**
     * @return string our title, will not be empty
     */
    public function getTitle(): string
    {
        return $this->getAsString('title');
    }
}
