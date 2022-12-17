<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration;

use OliverKlee\Oelib\Templating\TemplateHelper;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Legacy configuration for the registration and unregistration forms. This class exists only as long as the new
 * registration form uses the legacy email functionality and the new unregistration form uses the registration manager
 * to remove the registration.
 */
class LegacyConfiguration extends TemplateHelper
{
    public function __construct()
    {
        $frontEndController = $GLOBALS['TSFE'] ?? null;
        if (!$frontEndController instanceof TypoScriptFrontendController) {
            throw new \RuntimeException('No front end found.', 1668938450);
        }
        $this->cObj = $frontEndController->cObj;

        parent::__construct(null, $frontEndController);
    }
}
