<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\UpgradeWizards;

use TYPO3\CMS\Install\Updates\RepeatableInterface;

/**
 * This upgrade wizard migrates the category icons from old-style image uploads to FAL.
 */
class CategoryIconToFalUpgradeWizard extends AbstractFalUpgradeWizard implements RepeatableInterface
{
    /**
     * @var non-empty-string
     */
    protected $identifier = 'seminars_migrateCategoryIconsToFal';

    /**
     * @var non-empty-string
     */
    protected $title = 'Migrate seminars category icons to FAL';

    /**
     * target folder after migration, relative to fileadmin
     *
     * @var non-empty-string
     */
    protected $targetPath = '_migrated/seminars_category_icons/';

    /**
     * @var non-empty-string
     */
    protected $description = 'The seminars extension used to have a legacy file upload for the category icons. '
    . 'This wizard now migrates those to FAL.';

    /**
     * @var non-empty-string
     */
    protected $table = 'tx_seminars_categories';

    /**
     * @var non-empty-string
     */
    protected $fieldToMigrate = 'icon';
}
