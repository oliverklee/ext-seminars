<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This class represents a basic view.
 *
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
abstract class Tx_Seminars_FrontEnd_AbstractView extends Tx_Oelib_TemplateHelper implements Tx_Oelib_Interface_ConfigurationCheckable
{
    /**
     * @var string same as plugin name
     */
    public $prefixId = 'tx_seminars_pi1';

    /**
     * faking $this->scriptRelPath so the locallang.xml file is found
     *
     * @var string
     */
    public $scriptRelPath = 'Resources/Private/Language/FrontEnd/locallang.xml';

    /**
     * @var string the extension key
     */
    public $extKey = 'seminars';

    /**
     * the relative path to the uploaded files
     *
     * @var string
     */
    const UPLOAD_PATH = 'uploads/tx_seminars/';

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
     * Frees as much memory that has been used by this object as possible.
     */
    public function __destruct()
    {
        parent::__destruct();
    }

    /**
     * Renders the view and returns its content.
     *
     * @return string the view's content
     */
    abstract public function render();

    /**
     * Returns the prefix for the configuration to check, e.g. "plugin.tx_seminars_pi1.".
     *
     * @return string the namespace prefix, will end with a dot
     */
    public function getTypoScriptNamespace()
    {
        return 'plugin.tx_seminars_pi1.';
    }
}
