<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Configuration;

use OliverKlee\Oelib\Templating\TemplateHelper;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Legacy configuration for the registration form. This class exists only as long as the new registration form
 * uses the legacy email functionality.
 *
 * @deprecated #1911 will be removed in seminars 5.3.0
 */
class LegacyRegistrationConfiguration extends TemplateHelper
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
