<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Model;

use OliverKlee\Oelib\Model\BackEndUserGroup as OelibBackEndUserGroup;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a back-end user group.
 */
class BackEndUserGroup extends OelibBackEndUserGroup implements Titled
{
}
