<?php

declare(strict_types=1);

use OliverKlee\Oelib\Templating\TemplateHelper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This class represents a basic view.
 */
abstract class Tx_Seminars_FrontEnd_AbstractView extends TemplateHelper
{
    /**
     * the relative path to the uploaded files
     *
     * @var string
     */
    const UPLOAD_PATH = 'uploads/tx_seminars/';

    /**
     * @var string same as plugin name
     */
    public $prefixId = 'tx_seminars_pi1';

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

    /**
     * @var string
     */
    protected $whatToDisplay = '';

    /**
     * The constructor. Initializes the TypoScript configuration, initializes
     * the flex forms, gets the template HTML code, sets the localized labels
     * and set the CSS classes from TypoScript.
     *
     * @param array $configuration TypoScript configuration for the plugin
     * @param ContentObjectRenderer $contentObjectRenderer the parent cObj content, needed for the flexforms
     */
    public function __construct(array $configuration, ContentObjectRenderer $contentObjectRenderer)
    {
        $this->cObj = $contentObjectRenderer;
        $this->init($configuration);
        $this->pi_initPIflexForm();

        $this->getTemplateCode();
        $this->setLabels();
    }

    /**
     * Renders the view and returns its content.
     *
     * @return string the view's content
     */
    abstract public function render(): string;
}
