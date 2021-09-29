<?php

declare(strict_types=1);

use OliverKlee\Oelib\Templating\TemplateHelper;

/**
 * This class provides a way to access config values from plugin.tx_seminars to classes within FrontEnd/.
 */
class Tx_Seminars_Service_ConfigurationService extends TemplateHelper
{
    /**
     * @var class-string same as class name
     */
    public $prefixId = \Tx_Seminars_Service_ConfigurationService::class;

    /**
     * faking $this->scriptRelPath so the locallang.xlf file is found
     *
     * @var string
     */
    public $scriptRelPath = 'Resources/Private/Language/locallang.xlf';

    /**
     * @var string the extension key
     */
    public $extKey = 'seminars';

    public function __construct()
    {
        $this->init();
    }
}
