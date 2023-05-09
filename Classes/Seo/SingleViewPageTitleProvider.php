<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Seo;

use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;

/**
 * Provides the page title for the single view.
 */
class SingleViewPageTitleProvider extends AbstractPageTitleProvider
{
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
