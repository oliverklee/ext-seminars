<?php

declare(strict_types=1);

use OliverKlee\Oelib\Templating\TemplateHelper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This class represents a basic view.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
abstract class Tx_Seminars_FrontEnd_AbstractView extends TemplateHelper implements \Tx_Oelib_Interface_ConfigurationCheckable
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
     * Eliminates the renderlet path info from the given form data.
     *
     * @param mixed[] $formData submitted renderlet data
     * @param \tx_mkforms_forms_Base $form
     *
     * @return mixed[] renderlet data with the path info removed from the keys
     */
    protected static function removePathFromWidgetData(array $formData, tx_mkforms_forms_Base $form): array
    {
        \tx_rnbase::load(\tx_mkforms_util_FormBase::class);
        return tx_mkforms_util_FormBase::removePathFromWidgetData($formData, $form);
    }

    /**
     * Renders the view and returns its content.
     *
     * @return string the view's content
     */
    abstract public function render(): string;

    /**
     * Returns the prefix for the configuration to check, e.g. "plugin.tx_seminars_pi1.".
     *
     * @return string the namespace prefix, will end with a dot
     */
    public function getTypoScriptNamespace(): string
    {
        return 'plugin.tx_seminars_pi1.';
    }
}
