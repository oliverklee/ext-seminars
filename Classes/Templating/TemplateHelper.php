<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Templating;

use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Templating\Template;
use OliverKlee\Oelib\Templating\TemplateRegistry;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This utility class provides some commonly-used functions for handling
 * templates (in addition to all functionality provided by the base classes).
 *
 * @internal
 */
abstract class TemplateHelper
{
    /**
     * @var non-empty-string the regular expression used to find subparts
     */
    private const LABEL_PATTERN = '/###(LABEL_([A-Z\\d_]+))###/';

    /**
     * @var list<false|''|0|'0'|null>
     */
    private const FALSEY_VALUES = [null, false, '', 0, '0'];

    /**
     * The back-reference to the mother cObj object set at call time
     *
     * @var ContentObjectRenderer|null
     * @todo: Signature in v12: protected ?ContentObjectRenderer $cObj = null;
     */
    public $cObj;

    /**
     * This is the incoming array by name `$this->prefixId` merged between POST and GET, POST taking precedence.
     * Eg. if the class name is 'tx_myext'
     * then the content of this array will be whatever comes into &tx_myext[...]=...
     *
     * @var array
     */
    public $piVars = [
        'pointer' => '',
        // Used as a pointer for lists
        'mode' => '',
        // List mode
        'sword' => '',
        // Search word
        'sort' => '',
    ];

    /**
     * Local pointer variable array.
     * Holds pointer information for the MVC like approach Kasper
     * initially proposed.
     *
     * @var array{
     *        res_count: int,
     *        results_at_a_time: int,
     *        maxPages: int,
     *        currentRow: array,
     *        currentTable: string,
     *        descFlag: bool
     *      }
     */
    public $internal = [
        'res_count' => 0,
        'results_at_a_time' => 20,
        'maxPages' => 10,
        'currentRow' => [],
        'currentTable' => '',
        'descFlag' => false,
    ];

    /**
     * Local Language content
     *
     * @var array
     */
    public $LOCAL_LANG = [];

    /**
     * Contains those LL keys, which have been set to (empty) in TypoScript.
     * This is necessary, as we cannot distinguish between a nonexisting
     * translation and a label that has been cleared by TS.
     * In both cases ['key'][0]['target'] is "".
     *
     * @var array
     */
    protected $LOCAL_LANG_UNSET = [];

    /**
     * Flag that tells if the locallang file has been fetch (or tried to
     * be fetched) already.
     *
     * @var bool
     */
    public $LOCAL_LANG_loaded = false;

    /**
     * Pointer to the language to use.
     *
     * @var string
     */
    public $LLkey = 'default';

    /**
     * Pointer to alternative fall-back language to use.
     *
     * @var string
     */
    public $altLLkey = '';

    /**
     * You can set this during development to some value that makes it
     * easy for you to spot all labels that ARe delivered by the getLL function.
     *
     * @var string
     */
    public $LLtestPrefix = '';

    /**
     * Save as LLtestPrefix, but additional prefix for the alternative value
     * in getLL() function calls
     *
     * @var string
     */
    public $LLtestPrefixAlt = '';

    /**
     * @var string
     */
    public $pi_isOnlyFields = 'mode,pointer';

    /**
     * @var int
     */
    public $pi_alwaysPrev = 0;

    /**
     * @var int
     */
    public $pi_lowerThan = 5;

    /**
     * @var string
     */
    public $pi_moreParams = '';

    /**
     * @var array
     */
    public $pi_autoCacheFields = [];

    /**
     * @var bool
     */
    public $pi_autoCacheEn = false;

    /**
     * Should normally be set in the main function with the TypoScript content passed to the method.
     *
     * $conf[LOCAL_LANG][_key_] is reserved for Local Language overrides.
     * $conf[userFunc] reserved for setting up the USER / USER_INT object. See TSref
     *
     * @var array
     */
    public $conf = [];

    /**
     * @var int
     */
    public $pi_tmpPageId = 0;

    /**
     * Property for accessing TypoScriptFrontendController centrally
     *
     * @var TypoScriptFrontendController
     */
    protected $frontendController;

    /**
     * @var MarkerBasedTemplateService
     */
    protected $templateService;

    /**
     * @var string the prefix used for CSS classes
     */
    public $prefixId = 'tx_seminars_pi1';

    /**
     * faking `$this->scriptRelPath` so the `locallang.xlf` file is found
     *
     * @var string
     */
    public $scriptRelPath = 'Resources/Private/Language/locallang.xlf';

    /**
     * @var string the extension key
     */
    public $extKey = 'seminars';

    /**
     * @var bool whether `init()` already has been called (in order to avoid duplicate calls)
     */
    protected $isInitialized = false;

    /**
     * @var string the file name of the template set via TypoScript or FlexForms
     */
    private $templateFileName = '';

    /**
     * @var Template|null this object's (only) template
     */
    private $template;

    /**
     * A list of language keys for which the localizations have been loaded
     * (or NULL if the list has not been compiled yet).
     *
     * @var array<string>|null
     */
    private $availableLanguages;

    /**
     * An ordered list of language label suffixes that should be tried to get
     * localizations in the preferred order of formality (or NULL if the list
     * has not been compiled yet).
     *
     * @var list<'_formal'|'_informal'|''>|null
     */
    private $suffixesToTry;

    /**
     * @var array<non-empty-string, string>
     */
    protected $translationCache = [];

    /**
     * Class Constructor (true constructor)
     * Initializes $this->piVars if $this->prefixId is set to any value
     * Will also set $this->LLkey based on the config.language setting.
     *
     * @param null $_ unused,
     * @param TypoScriptFrontendController $frontendController
     */
    public function __construct($_ = null, TypoScriptFrontendController $frontendController = null)
    {
        $this->frontendController = $frontendController ?: $GLOBALS['TSFE'];
        $this->templateService = GeneralUtility::makeInstance(MarkerBasedTemplateService::class);
        // Setting piVars:
        if ($this->prefixId !== '') {
            $this->piVars = GeneralUtility::_GPmerged($this->prefixId);
        }
        $this->LLkey = $this->frontendController->getLanguage()->getTypo3Language();

        $locales = GeneralUtility::makeInstance(Locales::class);
        if (in_array($this->LLkey, $locales->getLocales(), true)) {
            foreach ($locales->getLocaleDependencies($this->LLkey) as $language) {
                $this->altLLkey .= $language . ',';
            }
            $this->altLLkey = rtrim($this->altLLkey, ',');
        }
    }

    /**
     * Initializes the FE plugin stuff and reads the configuration.
     *
     * It is harmless if this function gets called multiple times as it
     * recognizes this and ignores all calls but the first one.
     *
     * This is merely a convenience function.
     *
     * If the parameter is omitted, the configuration for `plugin.tx_[extkey]` is
     * used instead, e.g., `plugin.tx_seminars`.
     *
     * @param array<string, mixed>|mixed $configuration TypoScript configuration for the plugin (usually an array)
     */
    public function init($configuration = null): void
    {
        if ($this->isInitialized) {
            return;
        }

        if (\is_array($configuration)) {
            $this->conf = $configuration;
        }
        $this->ensureContentObject();

        $this->isInitialized = true;
    }

    protected function isConfigurationCheckEnabled(): bool
    {
        if ($this->extKey === '') {
            return false;
        }

        return ConfigurationProxy::getInstance($this->extKey)->getAsBoolean('enableConfigCheck');
    }

    /**
     * Ensures that $this->cObj points to a valid content object.
     *
     * If this object already has a valid cObj, this function does nothing.
     *
     * If there is a front end and this object does not have a cObj yet, the cObj from the front end is used.
     *
     * If this object has no cObj and there is no front end, this function will do nothing.
     */
    protected function ensureContentObject(): void
    {
        if ($this->cObj instanceof ContentObjectRenderer) {
            return;
        }

        $frontEnd = $this->getFrontEndController();
        if ($frontEnd instanceof TypoScriptFrontendController) {
            $this->cObj = $frontEnd->cObj;
        }
    }

    /**
     * Checks that this object is properly initialized.
     */
    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }

    /**
     * Gets a value from flexforms or TypoScript setup.
     * The priority lies on flexforms; if nothing is found there, the value
     * from TypoScript setup is returned. If there is no field with that name in TypoScript setup,
     * an empty string is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     * @param bool $ignoreFlexform whether to ignore the flexform values and just get the settings from TypoScript,
     *        may be empty
     *
     * @return string the value of the corresponding flexforms or TypoScript setup entry (may be empty)
     */
    private function getConfValue(
        string $fieldName,
        string $sheet = 'sDEF',
        bool $ignoreFlexform = false
    ): string {
        $configurationValueFromTypoScript = (string)($this->conf[$fieldName] ?? '');
        $contentObject = $this->cObj;
        if (!$contentObject instanceof ContentObjectRenderer) {
            return $configurationValueFromTypoScript;
        }

        $flexFormsData = $contentObject->data['pi_flexform'] ?? null;
        if (!$ignoreFlexform && \is_array($flexFormsData)) {
            $flexFormsValue = $this->pi_getFFvalue($contentObject->data['pi_flexform'], $fieldName, $sheet);
        } else {
            $flexFormsValue = null;
        }

        return !\in_array($flexFormsValue, self::FALSEY_VALUES, true)
            ? $flexFormsValue : $configurationValueFromTypoScript;
    }

    /**
     * Gets a trimmed string value from flexforms or TypoScript setup.
     * The priority lies on flexforms; if nothing is found there, the value
     * from TypoScript setup is returned. If there is no field with that name in TS
     * setup, an empty string is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     * @param bool $isFileName whether this is a filename, which has to be combined with a path
     * @param bool $ignoreFlexform
     *        whether to ignore the flexform values and just get the settings from TypoScript, may be empty
     *
     * @return string the trimmed value of the corresponding flexforms or
     *                TypoScript setup entry (may be empty)
     */
    public function getConfValueString(
        string $fieldName,
        string $sheet = 'sDEF',
        bool $isFileName = false,
        bool $ignoreFlexform = false
    ): string {
        return trim(
            $this->getConfValue(
                $fieldName,
                $sheet,
                $ignoreFlexform
            )
        );
    }

    /**
     * Checks whether a string value from flexforms or TypoScript setup is set.
     * The priority lies on flexforms; if nothing is found there, the value
     * from TypoScript setup is checked. If there is no field with that name in TS
     * setup, FALSE is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     * @param bool $ignoreFlexform
     *        whether to ignore the flexform values and just get the settings from TypoScript, may be empty
     *
     * @return bool whether there is a non-empty value in the
     *                 corresponding flexforms or TypoScript setup entry
     */
    public function hasConfValueString(
        string $fieldName,
        string $sheet = 'sDEF',
        bool $ignoreFlexform = false
    ): bool {
        return $this->getConfValueString($fieldName, $sheet, false, $ignoreFlexform) !== '';
    }

    /**
     * Gets an integer value from flexforms or TypoScript setup.
     * The priority lies on flexforms; if nothing is found there, the value
     * from TypoScript setup is returned. If there is no field with that name in TS
     * setup, zero is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     *
     * @return int the int value of the corresponding flexforms or TypoScript setup entry
     */
    public function getConfValueInteger(string $fieldName, string $sheet = 'sDEF'): int
    {
        return (int)$this->getConfValue($fieldName, $sheet);
    }

    /**
     * Checks whether an integer value from flexforms or TypoScript setup is set and
     * non-zero. The priority lies on flexforms; if nothing is found there, the
     * value from TypoScript setup is checked. If there is no field with that name in
     * TypoScript setup, FALSE is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     *
     * @return bool whether there is a non-zero value in the
     *                 corresponding flexforms or TypoScript setup entry
     */
    public function hasConfValueInteger(string $fieldName, string $sheet = 'sDEF'): bool
    {
        return (bool)$this->getConfValueInteger($fieldName, $sheet);
    }

    /**
     * Gets a boolean value from flexforms or TypoScript setup.
     * The priority lies on flexforms; if nothing is found there, the value
     * from TypoScript setup is returned. If there is no field with that name in TS
     * setup, FALSE is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     *
     * @return bool the boolean value of the corresponding flexforms or
     *                 TypoScript setup entry
     */
    public function getConfValueBoolean(string $fieldName, string $sheet = 'sDEF'): bool
    {
        return (bool)$this->getConfValue($fieldName, $sheet);
    }

    /**
     * Sets a configuration value.
     *
     * This function is intended to be used for testing purposes only.
     *
     * @param non-empty-string $key key of the configuration property to set
     * @param mixed $value value of the configuration property, may be empty or zero
     */
    public function setConfigurationValue(string $key, $value): void
    {
        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        if ($key === '') {
            throw new \InvalidArgumentException('$key must not be empty', 1331489491);
        }

        $this->conf[$key] = $value;
    }

    /**
     * Gets the configuration.
     *
     * @return array<string, mixed> configuration array, might be empty
     */
    public function getConfiguration(): array
    {
        return $this->conf;
    }

    /**
     * Retrieves the plugin template file set in `$this->conf['templateFile']`
     * (or also via flexforms if TYPO3 mode is FE) and writes it to `$this->templateCode`.
     * The subparts will be written to $this->templateCache.
     *
     * @param bool $ignoreFlexform whether the settings in the Flexform should be ignored
     */
    public function getTemplateCode(bool $ignoreFlexform = false): void
    {
        // Trying to fetch the template code via `$this->cObj` in BE mode leads to
        // a non-catchable error in the `ContentObjectRenderer` class because the `cObj`
        // configuration array is not initialized properly.
        // As flexforms can be used in FE mode only, `$ignoreFlexform` is set true if we are in the BE mode.
        // By this, `$this->cObj->fileResource` can be sheltered from being called.
        if (!($GLOBALS['TSFE'] ?? null) instanceof TypoScriptFrontendController) {
            $ignoreFlexform = true;
        }

        $templateFileName = $this->getConfValueString(
            'templateFile',
            's_template_special',
            true,
            $ignoreFlexform
        );

        if (!$ignoreFlexform) {
            $templateFileName = GeneralUtility::getFileAbsFileName($templateFileName);
        }

        $this->templateFileName = $templateFileName;
    }

    /**
     * Returns the template object from the template registry for the file name
     * in $this->templateFileName.
     *
     * @return Template the template object for the template file name in `$this->templateFileName`
     */
    protected function getTemplate(): Template
    {
        if (!$this->template instanceof Template) {
            $this->template = TemplateRegistry::get($this->templateFileName);
        }

        return $this->template;
    }

    /**
     * Stores the given HTML template and retrieves all subparts, writing them
     * to $this->templateCache.
     *
     * The subpart names are automatically retrieved from $templateCode and
     * are used as array keys. For this, the ### are removed, but the names stay
     * uppercase.
     *
     * Example: The subpart ###MY_SUBPART### will be stored with the array key
     * 'MY_SUBPART'.
     *
     * @param string $templateCode the content of the HTML template
     */
    public function processTemplate(string $templateCode): void
    {
        $this->getTemplate()->processTemplate($templateCode);
    }

    /**
     * Sets a marker's content.
     *
     * Example: If the prefix is "field" and the marker name is "one", the
     * marker "###FIELD_ONE###" will be written.
     *
     * If the prefix is empty and the marker name is "one", the marker
     * "###ONE###" will be written.
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive,
     *        will get uppercased
     * @param mixed $content the marker's content, may be empty
     * @param string $prefix prefix to the marker name (may be empty, case-insensitive, will get uppercased)
     */
    public function setMarker(string $markerName, $content, string $prefix = ''): void
    {
        $this->getTemplate()->setMarker($markerName, $content, $prefix);
    }

    /**
     * Gets a marker's content.
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive,
     *        will get uppercased
     *
     * @return string the marker's content or an empty string if the marker has not been set before
     */
    public function getMarker(string $markerName): string
    {
        return $this->getTemplate()->getMarker($markerName);
    }

    /**
     * Sets a subpart's content.
     *
     * Example: If the prefix is "field" and the subpart name is "one", the
     * subpart "###FIELD_ONE###" will be written.
     *
     * If the prefix is empty and the subpart name is "one", the subpart
     * "###ONE###" will be written.
     *
     * @param non-empty-string $subpartName name without the ### signs, case-insensitive, will get uppercased
     * @param mixed $content the subpart's content, may be empty
     * @param string $prefix prefix to the subpart name (may be empty, case-insensitive, will get uppercased)
     *
     * @throws NotFoundException
     */
    public function setSubpart(string $subpartName, $content, string $prefix = ''): void
    {
        $this->getTemplate()->setSubpart($subpartName, $content, $prefix);
    }

    /**
     * Sets a marker based on whether the int content is non-zero.
     *
     * If (int)$content is non-zero, this function sets the marker's content, working
     * exactly like setMarker($markerName, $content, $markerPrefix).
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive,
     *        will get uppercased
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix to the marker name for setting (may be empty, case-insensitive, will get uppercased)
     *
     * @return bool TRUE if the marker content has been set, FALSE otherwise
     *
     * @see setMarkerIfNotEmpty
     */
    public function setMarkerIfNotZero(string $markerName, $content, string $markerPrefix = ''): bool
    {
        return $this->getTemplate()->setMarkerIfNotZero($markerName, $content, $markerPrefix);
    }

    /**
     * Sets a marker based on whether the (string) content is non-empty.
     * If $content is non-empty, this function sets the marker's content,
     * working exactly like setMarker($markerName, $content, $markerPrefix).
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive,
     *        will get uppercased
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix prefix to the marker name for setting
     *        (may be empty, case-insensitive, will get uppercased)
     *
     * @return bool TRUE if the marker content has been set, FALSE otherwise
     *
     * @see setMarkerIfNotZero
     */
    public function setMarkerIfNotEmpty(string $markerName, $content, string $markerPrefix = ''): bool
    {
        return $this->getTemplate()->setMarkerIfNotEmpty($markerName, $content, $markerPrefix);
    }

    /**
     * Checks whether a subpart is visible.
     *
     * Note: If the subpart to check does not exist, this function will return false.
     *
     * @param string $subpartName name of the subpart to check (without the ###)
     *
     * @return bool TRUE if the subpart is visible, FALSE otherwise
     */
    public function isSubpartVisible(string $subpartName): bool
    {
        return $this->getTemplate()->isSubpartVisible($subpartName);
    }

    /**
     * Takes a comma-separated list of subpart names and sets them to hidden. In
     * the process, the names are changed from 'aname' to '###BLA_ANAME###' and
     * used as keys.
     *
     * Example: If the prefix is "field" and the list is "one,two", the subparts
     * "###FIELD_ONE###" and "###FIELD_TWO###" will be hidden.
     *
     * If the prefix is empty and the list is "one,two", the subparts
     * "###ONE###" and "###TWO###" will be hidden.
     *
     * @param string $subparts comma-separated list of the subparts to hide
     *        (case-insensitive, will get uppercased)
     * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
     */
    public function hideSubparts(string $subparts, string $prefix = ''): void
    {
        $this->getTemplate()->hideSubparts($subparts, $prefix);
    }

    /**
     * Takes an array of subpart names and sets them to hidden. In the process,
     * the names are changed from 'aname' to '###BLA_ANAME###' and used as keys.
     *
     * Example: If the prefix is "field" and the array has two elements "one"
     * and "two", the subparts "###FIELD_ONE###" and "###FIELD_TWO###" will be
     * hidden.
     *
     * If the prefix is empty and the array has two elements "one" and "two",
     * the subparts "###ONE###" and "###TWO###" will be hidden.
     *
     * @param array<string|int, non-empty-string> $subparts subpart names to hide
     *        (may be empty, case-insensitive, will get uppercased)
     * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
     */
    public function hideSubpartsArray(array $subparts, string $prefix = ''): void
    {
        $this->getTemplate()->hideSubpartsArray($subparts, $prefix);
    }

    /**
     * Takes a comma-separated list of subpart names and unhides them if they
     * have been hidden beforehand.
     *
     * Note: All subpartNames that are provided with the second parameter will
     * not be unhidden. This is to avoid unhiding subparts that are hidden by
     * the configuration.
     *
     * In the process, the names are changed from 'aname' to '###BLA_ANAME###'.
     *
     * Example: If the prefix is "field" and the list is "one,two", the subparts
     * "###FIELD_ONE###" and "###FIELD_TWO###" will be unhidden.
     *
     * If the prefix is empty and the list is "one,two", the subparts
     * "###ONE###" and "###TWO###" will be unhidden.
     *
     * @param non-empty-string $subparts comma-separated list of subpart names to unhide
     *        (case-insensitive, will get uppercased)
     * @param string $permanentlyHiddenSubparts comma-separated list of subpart names that shouldn't get unhidden
     * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
     */
    public function unhideSubparts(
        string $subparts,
        string $permanentlyHiddenSubparts = '',
        string $prefix = ''
    ): void {
        $this->getTemplate()->unhideSubparts(
            $subparts,
            $permanentlyHiddenSubparts,
            $prefix
        );
    }

    /**
     * Takes an array of subpart names and unhides them if they have been hidden
     * beforehand.
     *
     * Note: All subpartNames that are provided with the second parameter will
     * not be unhidden. This is to avoid unhiding subparts that are hidden by
     * the configuration.
     *
     * In the process, the names are changed from 'aname' to '###BLA_ANAME###'.
     *
     * Example: If the prefix is "field" and the array has two elements "one"
     * and "two", the subparts "###FIELD_ONE###" and "###FIELD_TWO###" will be
     * unhidden.
     *
     * If the prefix is empty and the array has two elements "one" and "two",
     * the subparts "###ONE###" and "###TWO###" will be unhidden.
     *
     * @param array<string|int, non-empty-string> $subparts $subparts subpart names to unhide
     *       (may be empty, case-insensitive, will get uppercased)
     * @param string[] $permanentlyHiddenSubparts subpart names that shouldn't get unhidden
     * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
     */
    public function unhideSubpartsArray(
        array $subparts,
        array $permanentlyHiddenSubparts = [],
        string $prefix = ''
    ): void {
        $this->getTemplate()->unhideSubpartsArray($subparts, $permanentlyHiddenSubparts, $prefix);
    }

    /**
     * Sets or hides a marker based on $condition.
     * If $condition is TRUE, this function sets the marker's content, working
     * exactly like setMarker($markerName, $content, $markerPrefix).
     * If $condition is FALSE, this function removes the wrapping subpart,
     * working exactly like hideSubparts($markerName, $wrapperPrefix).
     *
     * @param non-empty-string $markerName the marker's name without the ### signs,
     *        case-insensitive, will get uppercased
     * @param bool $condition if this is TRUE, the marker will be filled, otherwise the wrapped marker will be hidden
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix prefix to the marker name for setting
     *        (may be empty, case-insensitive, will get uppercased)
     * @param string $wrapperPrefix prefix to the subpart name for hiding
     *       (may be empty, case-insensitive, will get uppercased)
     *
     * @return bool TRUE if the marker content has been set, FALSE if the subpart has been hidden
     *
     * @see setMarkerContent
     * @see hideSubparts
     */
    public function setOrDeleteMarker(
        string $markerName,
        bool $condition,
        $content,
        string $markerPrefix = '',
        string $wrapperPrefix = ''
    ): bool {
        return $this->getTemplate()->setOrDeleteMarker(
            $markerName,
            $condition,
            $content,
            $markerPrefix,
            $wrapperPrefix
        );
    }

    /**
     * Sets or hides a marker based on whether the int content is non-zero.
     *
     * If (int)$content is non-zero, this function sets the marker's content,
     * working exactly like setMarker($markerName, $content,
     * $markerPrefix).
     * If (int)$condition is zero, this function removes the wrapping
     * subpart, working exactly like hideSubparts($markerName, $wrapperPrefix).
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive,
     *        will get uppercased
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix prefix to the marker name for setting
     *        (may be empty, case-insensitive, will get uppercased)
     * @param string $wrapperPrefix prefix to the subpart name for hiding
     *        (may be empty, case-insensitive, will get uppercased)
     *
     * @return bool TRUE if the marker content has been set, FALSE if the subpart has been hidden
     *
     * @see setOrDeleteMarker
     * @see setOrDeleteMarkerIfNotEmpty
     * @see setMarkerContent
     * @see hideSubparts
     */
    public function setOrDeleteMarkerIfNotZero(
        string $markerName,
        $content,
        string $markerPrefix = '',
        string $wrapperPrefix = ''
    ): bool {
        return $this->getTemplate()->setOrDeleteMarkerIfNotZero(
            $markerName,
            $content,
            $markerPrefix,
            $wrapperPrefix
        );
    }

    /**
     * Sets or hides a marker based on whether the (string) content is
     * non-empty.
     * If $content is non-empty, this function sets the marker's content,
     * working exactly like setMarker($markerName, $content,
     * $markerPrefix).
     * If $condition is empty, this function removes the wrapping subpart,
     * working exactly like hideSubparts($markerName, $wrapperPrefix).
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive,
     *        will get uppercased
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix prefix to the marker name for setting
     *        (may be empty, case-insensitive, will get uppercased)
     * @param string $wrapperPrefix prefix to the subpart name for hiding
     *        (may be empty, case-insensitive, will get uppercased)
     *
     * @return bool TRUE if the marker content has been set, FALSE if the subpart has been hidden
     *
     * @see setOrDeleteMarker
     * @see setOrDeleteMarkerIfNotZero
     * @see setMarkerContent
     * @see hideSubparts
     */
    public function setOrDeleteMarkerIfNotEmpty(
        string $markerName,
        $content,
        string $markerPrefix = '',
        string $wrapperPrefix = ''
    ): bool {
        return $this->getTemplate()->setOrDeleteMarkerIfNotEmpty(
            $markerName,
            $content,
            $markerPrefix,
            $wrapperPrefix
        );
    }

    /**
     * Retrieves a named subpart, recursively filling in its inner subparts
     * and markers. Inner subparts that are marked to be hidden will be
     * substituted with empty strings.
     *
     * This function either works on the subpart with the name $key or the
     * complete HTML template if $key is an empty string.
     *
     * @param string $key
     *        key of an existing subpart, for example 'LIST_ITEM' (without the ###),
     *        or an empty string to use the complete HTML template
     *
     * @return string the subpart content or an empty string if the
     *                subpart is hidden or the subpart name is missing
     */
    public function getSubpart(string $key = ''): string
    {
        return $this->getTemplate()->getSubpart($key);
    }

    /**
     * Retrieves a named subpart, recursively filling in its inner subparts
     * and markers. Inner subparts that are marked to be hidden will be
     * substituted with empty strings.
     *
     * This function either works on the subpart with the name $key or the
     * complete HTML template if $key is an empty string.
     *
     * All label markers in the rendered subpart are automatically replaced with their corresponding localized labels,
     * removing the need use the very expensive setLabels method.
     *
     * @param string $subpartKey
     *        key of an existing subpart, for example 'LIST_ITEM' (without the ###),
     *        or an empty string to use the complete HTML template
     *
     * @return string the subpart content or an empty string if the subpart is hidden or the subpart name is missing
     */
    public function getSubpartWithLabels(string $subpartKey = ''): string
    {
        $renderedSubpart = $this->getSubpart($subpartKey);

        $translator = $this;
        return (string)\preg_replace_callback(
            self::LABEL_PATTERN,
            static function (array $matches) use ($translator): string {
                /** @var non-empty-string $key */
                $key = \strtolower($matches[1]);
                return $translator->translate($key);
            },
            $renderedSubpart
        );
    }

    /**
     * Writes all localized labels for the current template into their corresponding template markers.
     *
     * For this, the label markers in the template must be prefixed with
     * "LABEL_" (e.g., "###LABEL_FOO###"), and the corresponding localization
     * entry must have the same key, but lowercased and without the ###
     * (e.g., "label_foo").
     */
    public function setLabels(): void
    {
        $template = $this->getTemplate();
        foreach ($template->getLabelMarkerNames() as $label) {
            $template->setMarker($label, $this->translate($label));
        }
    }

    /**
     * Intvals all piVars that are supposed to be integers. These are the keys
     * showUid, pointer and mode and the keys provided in $additionalPiVars.
     *
     * If some piVars are not set or no piVars array is defined yet, this
     * function will set the not yet existing piVars to zero.
     *
     * @param array<array-key, string> $additionalPiVars keys for $this->piVars that will be ensured to exist
     *        as integers in `$this->piVars` as well
     */
    protected function ensureIntegerPiVars(array $additionalPiVars = []): void
    {
        if (!\is_array($this->piVars)) {
            $this->piVars = [];
        }

        foreach (\array_merge(['showUid', 'pointer', 'mode'], $additionalPiVars) as $key) {
            if (isset($this->piVars[$key])) {
                $this->piVars[$key] = (int)$this->piVars[$key];
            } else {
                $this->piVars[$key] = 0;
            }
        }
    }

    /**
     * Extracts a value within listView.
     *
     * @param non-empty-string $fieldName TypoScript setup field name to extract (within listView.)
     *
     * @return string the contents of that field within listView., may be empty
     */
    private function getListViewConfigurationValue(string $fieldName): string
    {
        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        if ($fieldName === '') {
            throw new \InvalidArgumentException('$fieldName must not be empty.', 1331489528);
        }

        return isset($this->conf['listView.'][$fieldName]) ? (string)$this->conf['listView.'][$fieldName] : '';
    }

    /**
     * Returns a string value within listView.
     *
     * @param non-empty-string $fieldName TypoScript setup field name to extract (within listView.)
     *
     * @return string the trimmed contents of that field within listView.
     *                or an empty string if the value was not set
     */
    public function getListViewConfValueString(string $fieldName): string
    {
        return trim($this->getListViewConfigurationValue($fieldName));
    }

    /**
     * Returns an integer value within listView.
     *
     * @param non-empty-string $fieldName TypoScript setup field name to extract (within listView.)
     *
     * @return int the integer value of that field within listView, or zero if the value was not set
     */
    public function getListViewConfValueInteger(string $fieldName): int
    {
        return (int)$this->getListViewConfigurationValue($fieldName);
    }

    /**
     * Returns a boolean value within listView.
     *
     * @param non-empty-string $fieldName TypoScript setup field name to extract (within listView.)
     *
     * @return bool the boolean value of that field within listView., FALSE if no value was set
     */
    public function getListViewConfValueBoolean(string $fieldName): bool
    {
        return (bool)$this->getListViewConfigurationValue($fieldName);
    }

    /**
     * Makes this object serializable.
     *
     * @return list<non-empty-string>
     */
    public function __sleep(): array
    {
        $propertyNames = [];
        foreach ((new \ReflectionClass($this))->getProperties() as $property) {
            $propertyName = $property->getName();
            if ($propertyName === 'frontendController') {
                continue;
            }
            $propertyNames[] = $property->isPrivate() ? (static::class . ':' . $propertyName) : $propertyName;
        }

        return $propertyNames;
    }

    /**
     * Restores data that got lost during the serialization.
     */
    public function __wakeup(): void
    {
        $controller = $this->getFrontEndController();
        if ($controller instanceof TypoScriptFrontendController) {
            $this->frontendController = $controller;
        }
    }

    /**
     * Retrieves the localized string for the local language key $key.
     *
     * This function checks whether the FE or BE localization functions are
     * available and then uses the appropriate method.
     *
     * In $this->conf['salutation'], a suffix to the key may be set (which may
     * be either 'formal' or 'informal'). If a corresponding key exists, the
     * formal/informal localized string is used instead.
     * If the formal/informal key doesn't exist, this function just uses the
     * regular string.
     *
     * Example: key = 'greeting', suffix = 'informal'. If the key
     * 'greeting_informal' exists, that string is used.
     * If it doesn't exist, this functions tries to use the string with the key
     * 'greeting'.
     *
     * @param non-empty-string $key the local language key for which to return the value
     *
     * @return string the requested local language key, might be empty
     */
    public function translate(string $key): string
    {
        // @phpstan-ignore-next-line We are explicitly checking for a contract violation here.
        if ($key === '') {
            throw new \InvalidArgumentException('$key must not be empty.', 1331489025);
        }
        if ($this->extKey === '') {
            return $key;
        }
        if (isset($this->translationCache[$key])) {
            return $this->translationCache[$key];
        }

        $this->pi_loadLL();
        if (\is_array($this->LOCAL_LANG) && $this->getFrontEndController() !== null) {
            $result = $this->translateInFrontEnd($key);
        } elseif ($this->getLanguageService() !== null) {
            $result = $this->translateInBackEnd($key);
        } else {
            $result = $key;
        }

        $this->translationCache[$key] = $result;

        return $result;
    }

    /**
     * Retrieves the localized string for the local language key $key, using the
     * BE localization methods.
     *
     * @param non-empty-string $key the local language key for which to return the value
     *
     * @return string the requested local language key, might be empty
     */
    private function translateInBackEnd(string $key): string
    {
        $languageService = $this->getLanguageService();

        if (!$languageService instanceof LanguageService) {
            throw new \RuntimeException('No initialized language service.', 1646321243);
        }

        return $languageService->getLL($key);
    }

    /**
     * Retrieves the localized string for the local language key $key, using the
     * FE localization methods.
     *
     * In $this->conf['salutation'], a suffix to the key may be set (which may
     * be either 'formal' or 'informal'). If a corresponding key exists, the
     * formal/informal localized string is used instead.
     * If the formal/informal key doesn't exist, this function just uses the
     * regular string.
     *
     * Example: key = 'greeting', suffix = 'informal'. If the key
     * 'greeting_informal' exists, that string is used.
     * If it doesn't exist, this functions tries to use the string with the key
     * 'greeting'.
     *
     * @param non-empty-string $key the local language key for which to return the value
     *
     * @return string the requested local language key, might be empty
     */
    private function translateInFrontEnd(string $key): string
    {
        $hasFoundATranslation = false;
        $result = '';

        $availableLanguages = $this->getAvailableLanguages();
        foreach ($this->getSuffixesToTry() as $suffix) {
            $completeKey = $key . $suffix;
            foreach ($availableLanguages as $language) {
                if (isset($this->LOCAL_LANG[$language][$completeKey])) {
                    $result = $this->pi_getLL($completeKey);
                    $hasFoundATranslation = true;
                    break 2;
                }
            }
        }

        if (!$hasFoundATranslation) {
            $result = $key;
        }

        return $result;
    }

    /**
     * Compiles a list of language keys for which localizations have been loaded.
     *
     * @return array<string> a list of language keys (might be empty)
     */
    private function getAvailableLanguages(): array
    {
        if ($this->availableLanguages === null) {
            $this->availableLanguages = [];

            if ($this->LLkey !== '') {
                $this->availableLanguages[] = $this->LLkey;
            }
            // The key for English is "default", not "en".
            $this->availableLanguages = \str_replace('en', 'default', $this->availableLanguages);
            // Remove duplicates in case the default language is the same as the fall-back language.
            $this->availableLanguages = \array_unique($this->availableLanguages);

            // Now check that we only keep languages for which we have translations.
            foreach ($this->availableLanguages as $index => $code) {
                if (!isset($this->LOCAL_LANG[$code])) {
                    unset($this->availableLanguages[$index]);
                }
            }
        }

        return $this->availableLanguages;
    }

    /**
     * Gets an ordered list of language label suffixes that should be tried to
     * get localizations in the preferred order of formality.
     *
     * @return list<'_formal'|'_informal'|''> ordered list of suffixes, will not be empty
     */
    private function getSuffixesToTry(): array
    {
        if ($this->suffixesToTry === null) {
            $this->suffixesToTry = [];

            if (isset($this->conf['salutation'])) {
                if ($this->conf['salutation'] === 'informal') {
                    $this->suffixesToTry[] = '_informal';
                }
                $this->suffixesToTry[] = '_formal';
            }
            $this->suffixesToTry[] = '';
        }

        return $this->suffixesToTry;
    }

    protected function getFrontEndController(): ?TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'] ?? null;
    }

    /**
     * Returns $GLOBALS['LANG'].
     */
    protected function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }

    /**
     * Returns the localized label of the LOCAL_LANG key, $key
     * Notice that for debugging purposes prefixes for the output values can be set with the internal vars
     * ->LLtestPrefixAlt and ->LLtestPrefix
     *
     * @param string $key The key from the LOCAL_LANG array for which to return the value.
     * @param string $alternativeLabel Alternative string to return IF no value is found set for the key,
     *        neither for the local language nor the default.
     * @return string|null The value from LOCAL_LANG.
     */
    // phpcs:disable
    public function pi_getLL(string $key, string $alternativeLabel = ''): ?string
    {
        $word = null;
        if (
            !empty($this->LOCAL_LANG[$this->LLkey][$key][0]['target'])
            || isset($this->LOCAL_LANG_UNSET[$this->LLkey][$key])
        ) {
            $word = $this->LOCAL_LANG[$this->LLkey][$key][0]['target'];
        } elseif ($this->altLLkey) {
            $alternativeLanguageKeys = GeneralUtility::trimExplode(',', $this->altLLkey, true);
            $alternativeLanguageKeys = array_reverse($alternativeLanguageKeys);
            foreach ($alternativeLanguageKeys as $languageKey) {
                if (
                    !empty($this->LOCAL_LANG[$languageKey][$key][0]['target'])
                    || isset($this->LOCAL_LANG_UNSET[$languageKey][$key])
                ) {
                    // Alternative language translation for key exists
                    $word = $this->LOCAL_LANG[$languageKey][$key][0]['target'];
                    break;
                }
            }
        }
        if ($word === null) {
            if (
                !empty($this->LOCAL_LANG['default'][$key][0]['target'])
                || isset($this->LOCAL_LANG_UNSET['default'][$key])
            ) {
                // Get default translation (without charset conversion, english)
                $word = $this->LOCAL_LANG['default'][$key][0]['target'];
            } else {
                // Return alternative string or empty
                $word = isset($this->LLtestPrefixAlt) ? $this->LLtestPrefixAlt . $alternativeLabel : $alternativeLabel;
            }
        }
        return isset($this->LLtestPrefix) ? $this->LLtestPrefix . $word : $word;
    }

    /**
     * Loads local-language values from the file passed as a parameter or
     * by looking for a "locallang" file in the
     * plugin class directory ($this->scriptRelPath).
     * Also locallang values set in the TypoScript property "_LOCAL_LANG" are
     * merged onto the values found in the "locallang" file.
     * Supported file extensions xlf
     */
    // phpcs:disable
    public function pi_loadLL(): void
    {
        if ($this->LOCAL_LANG_loaded) {
            return;
        }

        if ($this->scriptRelPath !== '') {
            $languageFilePath = 'EXT:' . $this->extKey . '/'
                . PathUtility::dirname($this->scriptRelPath) . '/locallang.xlf';
        } else {
            $languageFilePath = '';
        }
        if ($languageFilePath !== '') {
            $languageFactory = GeneralUtility::makeInstance(LocalizationFactory::class);
            $this->LOCAL_LANG = $languageFactory->getParsedData($languageFilePath, $this->LLkey);
            $alternativeLanguageKeys = GeneralUtility::trimExplode(',', $this->altLLkey, true);
            foreach ($alternativeLanguageKeys as $languageKey) {
                $tempLL = $languageFactory->getParsedData($languageFilePath, $languageKey);
                if ($this->LLkey !== 'default' && isset($tempLL[$languageKey])) {
                    $this->LOCAL_LANG[$languageKey] = $tempLL[$languageKey];
                }
            }
            // Overlaying labels from TypoScript (including fictitious language keys for non-system languages!):
            if (isset($this->conf['_LOCAL_LANG.'])) {
                // Clear the "unset memory"
                $this->LOCAL_LANG_UNSET = [];
                foreach ($this->conf['_LOCAL_LANG.'] as $languageKey => $languageArray) {
                    // Remove the dot after the language key
                    $languageKey = substr($languageKey, 0, -1);
                    // Don't process label if the language is not loaded
                    if (is_array($languageArray) && isset($this->LOCAL_LANG[$languageKey])) {
                        foreach ($languageArray as $labelKey => $labelValue) {
                            if (!is_array($labelValue)) {
                                $this->LOCAL_LANG[$languageKey][$labelKey][0]['target'] = $labelValue;
                                if ($labelValue === '') {
                                    $this->LOCAL_LANG_UNSET[$languageKey][$labelKey] = '';
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->LOCAL_LANG_loaded = true;
    }

    /**
     * Return value from somewhere inside a FlexForm structure
     *
     * @param array $T3FlexForm_array FlexForm data
     * @param string $fieldName Field name to extract. Can be given like "test/el/2/test/el/field_templateObject" where each part will dig a level deeper in the FlexForm data.
     * @param string $sheet Sheet pointer, eg. "sDEF
     * @param string $lang Language pointer, eg. "lDEF
     * @param string $value Value pointer, eg. "vDEF
     * @return string|null The content.
     */
    // phpcs:disable
    public function pi_getFFvalue(
        array $T3FlexForm_array,
        string $fieldName,
        string $sheet = 'sDEF',
        string $lang = 'lDEF',
        string $value = 'vDEF'
    ): ?string {
        $sheetArray = $T3FlexForm_array['data'][$sheet][$lang] ?? '';
        if (is_array($sheetArray)) {
            return $this->pi_getFFvalueFromSheetArray($sheetArray, explode('/', $fieldName), $value);
        }
        return null;
    }

    /**
     * Returns part of $sheetArray pointed to by the keys in $fieldNameArray
     *
     * @param array $sheetArray Multidimensional array, typically FlexForm contents
     * @param array $fieldNameArr Array where each value points to a key in the FlexForms content - the input array will have the value returned pointed to by these keys. All integer keys will not take their integer counterparts, but rather traverse the current position in the array and return element number X (whether this is right behavior is not settled yet...)
     * @param string $value Value for outermost key, typ. "vDEF" depending on language.
     * @internal
     * @see pi_getFFvalue()
     */
    // phpcs:disable
    public function pi_getFFvalueFromSheetArray(array $sheetArray, array $fieldNameArr, string $value): string
    {
        $tempArr = $sheetArray;
        foreach ($fieldNameArr as $v) {
            if (MathUtility::canBeInterpretedAsInteger($v)) {
                $integerValue = (int)$v;
                if (is_array($tempArr)) {
                    $c = 0;
                    foreach ($tempArr as $values) {
                        if ($c === $integerValue) {
                            $tempArr = $values;
                            break;
                        }
                        $c++;
                    }
                }
            } elseif (isset($tempArr[$v])) {
                $tempArr = $tempArr[$v];
            }
        }
        return $tempArr[$value] ?? '';
    }

    /**
     * Converts $this->cObj->data['pi_flexform'] from XML string to flexForm array.
     *
     * @param string $field Field name to convert
     */
    // phpcs:disable
    public function pi_initPIflexForm(string $field = 'pi_flexform'): void
    {
        if (!$this->cObj instanceof ContentObjectRenderer) {
            throw new \RuntimeException('No cObj.', 1703017462);
        }

        // Converting flexform data into array
        $fieldData = $this->cObj->data[$field] ?? null;
        if (!is_array($fieldData) && $fieldData) {
            $this->cObj->data[$field] = GeneralUtility::xml2array((string)$fieldData);
            if (!is_array($this->cObj->data[$field])) {
                $this->cObj->data[$field] = [];
            }
        }
    }

    /**
     * Returns a class-name prefixed with $this->prefixId and with all underscores substituted to dashes (-)
     *
     * @param string $class The class name (or the END of it since it will be prefixed by $this->prefixId.'-')
     * @return string The combined class name (with the correct prefix)
     */
    // phpcs:disable
    public function pi_getClassName(string $class): string
    {
        return str_replace('_', '-', $this->prefixId) . ($this->prefixId ? '-' : '') . $class;
    }

    /**
     * Link string to the current page.
     * Returns the $str wrapped in <a>-tags with a link to the CURRENT page, but with $urlParameters set as extra parameters for the page.
     *
     * @param string $str The content string to wrap in <a> tags
     * @param array $urlParameters Array with URL parameters as key/value pairs. They will be "imploded" and added to the list of parameters defined in the plugins TypoScript property "parent.addParams" plus $this->pi_moreParams.
     * @param bool $cache If $cache is set (0/1), the page is asked to be cached by a &cHash value (unless the current plugin using this class is a USER_INT). Otherwise the no_cache-parameter will be a part of the link.
     * @param int $altPageId Alternative page ID for the link. (By default this function links to the SAME page!)
     * @return string The input string wrapped in <a> tags
     * @see pi_linkTP_keepPIvars()
     * @see ContentObjectRenderer::typoLink()
     */
    // phpcs:disable
    public function pi_linkTP(string $str, array $urlParameters = [], bool $cache = false, int $altPageId = 0): string
    {
        if (!$this->cObj instanceof ContentObjectRenderer) {
            throw new \RuntimeException('No cObj.', 1703017453);
        }

        $conf = [];
        if (!$cache) {
            $conf['no_cache'] = true;
        }
        $conf['parameter'] = $altPageId ?: ($this->pi_tmpPageId ?: 'current');
        $conf['additionalParams'] = ($this->conf['parent.']['addParams'] ?? '')
            . HttpUtility::buildQueryString($urlParameters, '&', true) . $this->pi_moreParams;
        return $this->cObj->typoLink($str, $conf);
    }

    /**
     * Returns a results browser. This means a bar of page numbers plus a "previous" and "next" link. For each entry in the bar the piVars "pointer" will be pointing to the "result page" to show.
     * Using $this->piVars['pointer'] as pointer to the page to display. Can be overwritten with another string ($pointerName) to make it possible to have more than one pagebrowser on a page)
     * Using $this->internal['res_count'], $this->internal['results_at_a_time'] and $this->internal['maxPages'] for count number, how many results to show and the max number of pages to include in the browse bar.
     * Using $this->internal['dontLinkActivePage'] as switch if the active (current) page should be displayed as pure text or as a link to itself
     * Using $this->internal['showFirstLast'] as switch if the two links named "<< First" and "LAST >>" will be shown and point to the first or last page.
     * Using $this->internal['pagefloat']: this defines were the current page is shown in the list of pages in the Pagebrowser. If this var is an integer it will be interpreted as position in the list of pages. If its value is the keyword "center" the current page will be shown in the middle of the pagelist.
     * Using $this->internal['showRange']: this var switches the display of the pagelinks from pagenumbers to ranges f.e.: 1-5 6-10 11-15... instead of 1 2 3...
     * Using $this->pi_isOnlyFields: this holds a comma-separated list of fieldnames which - if they are among the GETvars - will not disable caching for the page with pagebrowser.
     *
     * The third parameter is an array with several wraps for the parts of the pagebrowser. The following elements will be recognized:
     * disabledLinkWrap, inactiveLinkWrap, activeLinkWrap, browseLinksWrap, showResultsWrap, showResultsNumbersWrap, browseBoxWrap.
     *
     * If $wrapArr['showResultsNumbersWrap'] is set, the formatting string is expected to hold template markers (###FROM###, ###TO###, ###OUT_OF###, ###FROM_TO###, ###CURRENT_PAGE###, ###TOTAL_PAGES###)
     * otherwise the formatting string is expected to hold sprintf-markers (%s) for from, to, outof (in that sequence)
     *
     * @param int $showResultCount Determines how the results of the page browser will be shown. See description below
     * @param string $tableParams Attributes for the table tag which is wrapped around the table cells containing the browse links
     * @param array $wrapArr Array with elements to overwrite the default $wrapper-array.
     * @param string $pointerName varname for the pointer.
     * @param bool $hscText Enable htmlspecialchars() on language labels
     * @param bool $forceOutput Forces the output of the page browser if you set this option to "TRUE" (otherwise it's only drawn if enough entries are available)
     * @return string Output HTML-Table, wrapped in <div>-tags with a class attribute (if $wrapArr is not passed,
     */
    // phpcs:disable
    public function pi_list_browseresults(
        int $showResultCount = 1,
        string $tableParams = '',
        array $wrapArr = [],
        string $pointerName = 'pointer',
        bool $hscText = true,
        bool $forceOutput = false
    ): string {
        if (!$this->cObj instanceof ContentObjectRenderer) {
            throw new \RuntimeException('No cObj.', 1703017658);
        }

        $wrapper = [];
        $markerArray = [];
        // example $wrapArr-array how it could be traversed from an extension
        /* $wrapArr = array(
        'browseBoxWrap' => '<div class="browseBoxWrap">|</div>',
        'showResultsWrap' => '<div class="showResultsWrap">|</div>',
        'browseLinksWrap' => '<div class="browseLinksWrap">|</div>',
        'showResultsNumbersWrap' => '<span class="showResultsNumbersWrap">|</span>',
        'disabledLinkWrap' => '<span class="disabledLinkWrap">|</span>',
        'inactiveLinkWrap' => '<span class="inactiveLinkWrap">|</span>',
        'activeLinkWrap' => '<span class="activeLinkWrap">|</span>'
        );*/
        // Initializing variables:
        $pointer = (int)($this->piVars[$pointerName] ?? 0);
        $count = (int)($this->internal['res_count'] ?? 0);
        $results_at_a_time = MathUtility::forceIntegerInRange(($this->internal['results_at_a_time'] ?? 1), 1, 1000);
        $totalPages = (int)ceil($count / $results_at_a_time);
        $maxPages = MathUtility::forceIntegerInRange($this->internal['maxPages'], 1, 100);
        $pi_isOnlyFields = (bool)$this->pi_isOnlyFields($this->pi_isOnlyFields);
        if (!$forceOutput && $count <= $results_at_a_time) {
            return '';
        }
        // $showResultCount determines how the results of the pagerowser will be shown.
        // If set to 0: only the result-browser will be shown
        //	 		 1: (default) the text "Displaying results..." and the result-browser will be shown.
        //	 		 2: only the text "Displaying results..." will be shown
        $showResultCount = (int)$showResultCount;
        // If this is set, two links named "<< First" and "LAST >>" will be shown and point to the very first or last page.
        $showFirstLast = !empty($this->internal['showFirstLast']);
        // If this has a value the "previous" button is always visible (will be forced if "showFirstLast" is set)
        $alwaysPrev = $showFirstLast ? 1 : $this->pi_alwaysPrev;
        if (isset($this->internal['pagefloat'])) {
            if (strtoupper($this->internal['pagefloat']) === 'CENTER') {
                $pagefloat = ceil(($maxPages - 1) / 2);
            } else {
                // pagefloat set as integer. 0 = left, value >= $this->internal['maxPages'] = right
                $pagefloat = MathUtility::forceIntegerInRange($this->internal['pagefloat'], -1, $maxPages - 1);
            }
        } else {
            // pagefloat disabled
            $pagefloat = -1;
        }
        // Default values for "traditional" wrapping with a table. Can be overwritten by vars from $wrapArr
        $wrapper['disabledLinkWrap'] = '<td class="nowrap"><p>|</p></td>';
        $wrapper['inactiveLinkWrap'] = '<td class="nowrap"><p>|</p></td>';
        $wrapper['activeLinkWrap'] = '<td' . $this->pi_classParam('browsebox-SCell') . ' class="nowrap"><p>|</p></td>';
        $wrapper['browseLinksWrap'] = rtrim('<table ' . $tableParams) . '><tr>|</tr></table>';
        $wrapper['showResultsWrap'] = '<p>|</p>';
        $wrapper['browseBoxWrap'] = '
		<!--
			List browsing box:
		-->
		<div ' . $this->pi_classParam('browsebox') . '>
			|
		</div>';
        // Now overwrite all entries in $wrapper which are also in $wrapArr
        $wrapper = array_merge($wrapper, $wrapArr);
        // Show pagebrowser
        if ($showResultCount != 2) {
            if ($pagefloat > -1) {
                $lastPage = min($totalPages, max($pointer + 1 + $pagefloat, $maxPages));
                $firstPage = max(0, $lastPage - $maxPages);
            } else {
                $firstPage = 0;
                $lastPage = MathUtility::forceIntegerInRange($totalPages, 1, $maxPages);
            }
            $links = [];
            // Make browse-table/links:
            // Link to first page
            if ($showFirstLast) {
                if ($pointer > 0) {
                    $label = $this->pi_getLL('pi_list_browseresults_first', '<< First');
                    $links[] = $this->cObj->wrap(
                        $this->pi_linkTP_keepPIvars(
                            $hscText ? htmlspecialchars(
                                $label
                            ) : $label,
                            [$pointerName => null],
                            $pi_isOnlyFields
                        ),
                        $wrapper['inactiveLinkWrap']
                    );
                } else {
                    $label = $this->pi_getLL('pi_list_browseresults_first', '<< First');
                    $links[] = $this->cObj->wrap(
                        $hscText ? htmlspecialchars($label) : $label,
                        $wrapper['disabledLinkWrap']
                    );
                }
            }
            // Link to previous page
            if ($alwaysPrev >= 0) {
                if ($pointer > 0) {
                    $label = $this->pi_getLL('pi_list_browseresults_prev', '< Previous');
                    $links[] = $this->cObj->wrap(
                        $this->pi_linkTP_keepPIvars(
                            $hscText ? htmlspecialchars(
                                $label
                            ) : $label,
                            [$pointerName => ($pointer - 1) ?: ''],
                            $pi_isOnlyFields
                        ),
                        $wrapper['inactiveLinkWrap']
                    );
                } elseif ($alwaysPrev) {
                    $label = $this->pi_getLL('pi_list_browseresults_prev', '< Previous');
                    $links[] = $this->cObj->wrap(
                        $hscText ? htmlspecialchars($label) : $label,
                        $wrapper['disabledLinkWrap']
                    );
                }
            }
            // Links to pages
            for ($a = $firstPage; $a < $lastPage; $a++) {
                if ($this->internal['showRange'] ?? false) {
                    $pageText = ($a * $results_at_a_time + 1) . '-' . min($count, ($a + 1) * $results_at_a_time);
                } else {
                    $label = $this->pi_getLL('pi_list_browseresults_page', 'Page');
                    $pageText = trim(($hscText ? htmlspecialchars($label) : $label) . ' ' . ($a + 1));
                }
                // Current page
                if ($pointer == $a) {
                    if ($this->internal['dontLinkActivePage'] ?? false) {
                        $links[] = $this->cObj->wrap($pageText, $wrapper['activeLinkWrap']);
                    } else {
                        $links[] = $this->cObj->wrap(
                            $this->pi_linkTP_keepPIvars(
                                $pageText,
                                [$pointerName => $a ?: ''],
                                $pi_isOnlyFields
                            ),
                            $wrapper['activeLinkWrap']
                        );
                    }
                } else {
                    $links[] = $this->cObj->wrap(
                        $this->pi_linkTP_keepPIvars(
                            $pageText,
                            [$pointerName => $a ?: ''],
                            $pi_isOnlyFields
                        ),
                        $wrapper['inactiveLinkWrap']
                    );
                }
            }
            if ($pointer < $totalPages - 1 || $showFirstLast) {
                // Link to next page
                if ($pointer >= $totalPages - 1) {
                    $label = $this->pi_getLL('pi_list_browseresults_next', 'Next >');
                    $links[] = $this->cObj->wrap(
                        $hscText ? htmlspecialchars($label) : $label,
                        $wrapper['disabledLinkWrap']
                    );
                } else {
                    $label = $this->pi_getLL('pi_list_browseresults_next', 'Next >');
                    $links[] = $this->cObj->wrap(
                        $this->pi_linkTP_keepPIvars(
                            $hscText ? htmlspecialchars(
                                $label
                            ) : $label,
                            [$pointerName => $pointer + 1],
                            $pi_isOnlyFields
                        ),
                        $wrapper['inactiveLinkWrap']
                    );
                }
            }
            // Link to last page
            if ($showFirstLast) {
                if ($pointer < $totalPages - 1) {
                    $label = $this->pi_getLL('pi_list_browseresults_last', 'Last >>');
                    $links[] = $this->cObj->wrap(
                        $this->pi_linkTP_keepPIvars(
                            $hscText ? htmlspecialchars(
                                $label
                            ) : $label,
                            [$pointerName => $totalPages - 1],
                            $pi_isOnlyFields
                        ),
                        $wrapper['inactiveLinkWrap']
                    );
                } else {
                    $label = $this->pi_getLL('pi_list_browseresults_last', 'Last >>');
                    $links[] = $this->cObj->wrap(
                        $hscText ? htmlspecialchars($label) : $label,
                        $wrapper['disabledLinkWrap']
                    );
                }
            }
            $theLinks = $this->cObj->wrap(implode(LF, $links), $wrapper['browseLinksWrap']);
        } else {
            $theLinks = '';
        }
        $pR1 = $pointer * $results_at_a_time + 1;
        $pR2 = $pointer * $results_at_a_time + $results_at_a_time;
        if ($showResultCount) {
            if ($wrapper['showResultsNumbersWrap'] ?? false) {
                // This will render the resultcount in a more flexible way using markers (new in TYPO3 3.8.0).
                // The formatting string is expected to hold template markers (see function header). Example: 'Displaying results ###FROM### to ###TO### out of ###OUT_OF###'
                $markerArray['###FROM###'] = $this->cObj->wrap(
                    ($this->internal['res_count'] ?? 0) > 0 ? $pR1 : 0,
                    $wrapper['showResultsNumbersWrap']
                );
                $markerArray['###TO###'] = $this->cObj->wrap(
                    min(($this->internal['res_count'] ?? 0), $pR2),
                    $wrapper['showResultsNumbersWrap']
                );
                $markerArray['###OUT_OF###'] = $this->cObj->wrap(
                    ($this->internal['res_count'] ?? 0),
                    $wrapper['showResultsNumbersWrap']
                );
                $markerArray['###FROM_TO###'] = $this->cObj->wrap(
                    (($this->internal['res_count'] ?? 0) > 0 ? $pR1 : 0) . ' ' . $this->pi_getLL(
                        'pi_list_browseresults_to',
                        'to'
                    ) . ' ' . min($this->internal['res_count'] ?? 0, $pR2),
                    $wrapper['showResultsNumbersWrap']
                );
                $markerArray['###CURRENT_PAGE###'] = $this->cObj->wrap(
                    $pointer + 1,
                    $wrapper['showResultsNumbersWrap']
                );
                $markerArray['###TOTAL_PAGES###'] = $this->cObj->wrap($totalPages, $wrapper['showResultsNumbersWrap']);
                // Substitute markers
                $resultCountMsg = $this->templateService->substituteMarkerArray(
                    $this->pi_getLL(
                        'pi_list_browseresults_displays',
                        'Displaying results ###FROM### to ###TO### out of ###OUT_OF###'
                    ),
                    $markerArray
                );
            } else {
                // Render the resultcount in the "traditional" way using sprintf
                $resultCountMsg = sprintf(
                    str_replace(
                        '###SPAN_BEGIN###',
                        '<span' . $this->pi_classParam('browsebox-strong') . '>',
                        $this->pi_getLL(
                            'pi_list_browseresults_displays',
                            'Displaying results ###SPAN_BEGIN###%s to %s</span> out of ###SPAN_BEGIN###%s</span>'
                        )
                    ),
                    $count > 0 ? $pR1 : 0,
                    min($count, $pR2),
                    $count
                );
            }
            $resultCountMsg = $this->cObj->wrap($resultCountMsg, $wrapper['showResultsWrap']);
        } else {
            $resultCountMsg = '';
        }
        $sTables = $this->cObj->wrap($resultCountMsg . $theLinks, $wrapper['browseBoxWrap']);
        return $sTables;
    }

    /**
     * Wraps the input string in a <div> tag with the class attribute set to the prefixId.
     * All content returned from your plugins should be returned through this function so all content from your plugin is encapsulated in a <div>-tag nicely identifying the content of your plugin.
     *
     * @param string $str HTML content to wrap in the div-tags with the "main class" of the plugin
     * @return string HTML content wrapped, ready to return to the parent object.
     */
    // phpcs:disable
    public function pi_wrapInBaseClass(string $str): string
    {
        $content = '<div class="' . str_replace('_', '-', $this->prefixId) . '">
		' . $str . '
	</div>
	';
        if (!($this->frontendController->config['config']['disablePrefixComment'] ?? false)) {
            $content = '


	<!--

		BEGIN: Content of extension "' . $this->extKey . '", plugin "' . $this->prefixId . '"

	-->
	' . $content . '
	<!-- END: Content of extension "' . $this->extKey . '", plugin "' . $this->prefixId . '" -->

	';
        }
        return $content;
    }

    /**
     * Returns TRUE if the piVars array has ONLY those fields entered that is set in the $fList (commalist) AND if none of those fields value is greater than $lowerThan field if they are integers.
     * Notice that this function will only work as long as values are integers.
     *
     * @param string $fList List of fields (keys from piVars) to evaluate on
     * @param int $lowerThan Limit for the values.
     * @return int|null Returns TRUE (1) if conditions are met.
     */
    // phpcs:disable
    public function pi_isOnlyFields(string $fList, int $lowerThan = -1)
    {
        $lowerThan = $lowerThan == -1 ? $this->pi_lowerThan : $lowerThan;
        $fList = GeneralUtility::trimExplode(',', $fList, true);
        $tempPiVars = $this->piVars;
        foreach ($fList as $k) {
            if (isset($tempPiVars[$k]) && (!MathUtility::canBeInterpretedAsInteger(
                $tempPiVars[$k]
            ) || $tempPiVars[$k] < $lowerThan)) {
                unset($tempPiVars[$k]);
            }
        }
        if (empty($tempPiVars)) {
            // @TODO: How do we deal with this? return TRUE would be the right thing to do here but that might be breaking
            return 1;
        }
        return null;
    }

    /**
     * Returns the class-attribute with the correctly prefixed classname
     * Using pi_getClassName()
     *
     * @param string $class The class name(s) (suffix) - separate multiple classes with commas
     * @param string $addClasses Additional class names which should not be prefixed - separate multiple classes with commas
     * @return string A "class" attribute with value and a single space char before it.
     * @see pi_getClassName()
     */
    // phpcs:disable
    public function pi_classParam(string $class, string $addClasses = ''): string
    {
        $output = '';
        $classNames = GeneralUtility::trimExplode(',', $class);
        foreach ($classNames as $className) {
            $output .= ' ' . $this->pi_getClassName($className);
        }
        $additionalClassNames = GeneralUtility::trimExplode(',', $addClasses);
        foreach ($additionalClassNames as $additionalClassName) {
            $output .= ' ' . $additionalClassName;
        }
        return ' class="' . trim($output) . '"';
    }

    /**
     * Link a string to the current page while keeping currently set values in piVars.
     * Like pi_linkTP, but $urlParameters is by default set to $this->piVars with $overrulePIvars overlaid.
     * This means any current entries from this->piVars are passed on (except the key "DATA" which will be unset before!) and entries in $overrulePIvars will OVERRULE the current in the link.
     *
     * @param string $str The content string to wrap in <a> tags
     * @param array $overrulePIvars Array of values to override in the current piVars. Contrary to pi_linkTP the keys in this array must correspond to the real piVars array and therefore NOT be prefixed with the $this->prefixId string. Further, if a value is a blank string it means the piVar key will not be a part of the link (unset)
     * @param bool $cache If $cache is set, the page is asked to be cached by a &cHash value (unless the current plugin using this class is a USER_INT). Otherwise the no_cache-parameter will be a part of the link.
     * @param bool $clearAnyway If set, then the current values of piVars will NOT be preserved anyways... Practical if you want an easy way to set piVars without having to worry about the prefix, "tx_xxxxx[]
     * @param int $altPageId Alternative page ID for the link. (By default this function links to the SAME page!)
     * @return string The input string wrapped in <a> tags
     * @see pi_linkTP()
     */
    // phpcs:disable
    public function pi_linkTP_keepPIvars(
        string $str,
        array $overrulePIvars = [],
        bool $cache = false,
        bool $clearAnyway = false,
        int $altPageId = 0
    ): string {
        if (is_array($this->piVars) && is_array($overrulePIvars) && !$clearAnyway) {
            $piVars = $this->piVars;
            unset($piVars['DATA']);
            ArrayUtility::mergeRecursiveWithOverrule($piVars, $overrulePIvars);
            $overrulePIvars = $piVars;
            if ($this->pi_autoCacheEn) {
                $cache = (bool)$this->pi_autoCache($overrulePIvars);
            }
        }
        return $this->pi_linkTP($str, [$this->prefixId => $overrulePIvars], $cache, $altPageId);
    }

    /**
     * Returns TRUE if the array $inArray contains only values allowed to be cached based on the configuration in $this->pi_autoCacheFields
     * Used by ->pi_linkTP_keepPIvars
     * This is an advanced form of evaluation of whether a URL should be cached or not.
     *
     * @param array $inArray An array with piVars values to evaluate
     * @return int|null Returns TRUE (1) if conditions are met.
     * @see pi_linkTP_keepPIvars()
     */
    // phpcs:disable
    public function pi_autoCache(array $inArray)
    {
        if (is_array($inArray)) {
            foreach ($inArray as $fN => $fV) {
                if (!strcmp($inArray[$fN], '')) {
                    unset($inArray[$fN]);
                } elseif (is_array($this->pi_autoCacheFields[$fN])) {
                    if (is_array(
                        $this->pi_autoCacheFields[$fN]['range']
                    ) && (int)$inArray[$fN] >= (int)$this->pi_autoCacheFields[$fN]['range'][0] && (int)$inArray[$fN] <= (int)$this->pi_autoCacheFields[$fN]['range'][1]) {
                        unset($inArray[$fN]);
                    }
                    if (is_array($this->pi_autoCacheFields[$fN]['list']) && in_array(
                        $inArray[$fN],
                        $this->pi_autoCacheFields[$fN]['list']
                    )) {
                        unset($inArray[$fN]);
                    }
                }
            }
        }
        if (empty($inArray)) {
            // @TODO: How do we deal with this? return TRUE would be the right thing to do here but that might be breaking
            return 1;
        }
        return null;
    }
}
