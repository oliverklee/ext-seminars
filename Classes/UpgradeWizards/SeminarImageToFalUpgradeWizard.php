<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\UpgradeWizards;

use TYPO3\CMS\Install\Updates\RepeatableInterface;

/**
 * This upgrade wizard migrates the category icons from old-style image uploads to FAL.
 */
class SeminarImageToFalUpgradeWizard extends AbstractFalUpgradeWizard implements RepeatableInterface
{
    /**
     * @var non-empty-string
     */
    protected $identifier = 'seminars_migrateSeminarImagesToFal';

    /**
     * @var non-empty-string
     */
    protected $title = 'Migrate seminar images to FAL';

    /**
     * target folder after migration, relative to fileadmin
     *
     * @var non-empty-string
     */
    protected $targetPath = '_migrated/seminars_images/';

    /**
     * @var non-empty-string
     */
    protected $description = 'The seminars extension used to have a legacy file upload for the seminar images. '
    . 'This wizard now migrates those to FAL.';

    /**
     * @var non-empty-string
     */
    protected $table = 'tx_seminars_seminars';

    /**
     * @var non-empty-string
     */
    protected $fieldToMigrate = 'image';
}
