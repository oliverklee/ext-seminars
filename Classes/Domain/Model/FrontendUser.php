<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser as ExtraFieldsFrontendUser;

/**
 * This class represents a frontend user with some additional data specific to the seminars extension.
 */
class FrontendUser extends ExtraFieldsFrontendUser
{
}
