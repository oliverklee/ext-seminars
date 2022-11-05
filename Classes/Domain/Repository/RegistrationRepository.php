<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Repository;

use OliverKlee\Oelib\Domain\Repository\Interfaces\DirectPersist;
use OliverKlee\Oelib\Domain\Repository\Traits\StoragePageAgnostic;
use OliverKlee\Seminars\Domain\Model\Registration;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<Registration>
 */
class RegistrationRepository extends Repository implements DirectPersist
{
    use \OliverKlee\Oelib\Domain\Repository\Traits\DirectPersist;
    use StoragePageAgnostic;
}
