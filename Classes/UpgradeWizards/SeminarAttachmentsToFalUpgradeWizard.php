<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\UpgradeWizards;

use TYPO3\CMS\Install\Updates\RepeatableInterface;

/**
 * This upgrade wizard migrates the seminar attachments from old-style image uploads to FAL.
 */
class SeminarAttachmentsToFalUpgradeWizard extends AbstractFalUpgradeWizard implements RepeatableInterface
{
    /**
     * @var non-empty-string
     */
    protected $identifier = 'seminars_migrateSeminarAttachmentsToFal';

    /**
     * @var non-empty-string
     */
    protected $title = 'Migrate seminar attachments to FAL';

    /**
     * target folder after migration, relative to fileadmin
     *
     * @var non-empty-string
     */
    protected $targetPath = '_migrated/seminars_attachments/';

    /**
     * @var non-empty-string
     */
    protected $description = 'The seminars extension used to have a legacy file upload for the seminar attachments. '
    . 'This wizard now migrates those to FAL.';

    /**
     * @var non-empty-string
     */
    protected $table = 'tx_seminars_seminars';

    /**
     * @var non-empty-string
     */
    protected $fieldToMigrate = 'attached_files';
}
