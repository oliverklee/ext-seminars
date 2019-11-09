<?php
declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks;

use OliverKlee\Seminars\Interfaces\Hook;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class provides functions for unified hooks.
 *
 * It unifies the wide-spread use of this type of hooks in seminars.
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
class HookService
{
    /**
     * Interface name to this hook
     *
     * @var string
     */
    protected $interfaceName = '';

    /**
     * Index in $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'] of hooked-in classes
     *
     * @var string
     */
    protected $index = '';

    /**
     * Hook objects built
     *
     * @var array
     */
    protected $hookObjects = [];

    /**
     * @var boolean
     */
    protected $hooksHaveBeenRetrieved = false;

    /**
     * The constructor.
     *
     * @param $interfaceName interface the hook needs implemented
     * @param $index optional index to $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']
     *               if not using the interface name (for backwards compatibility)
     *               (the interface name is recommended)
     *
     * @throws \UnexpectedValueException
     *         if $interfaceName does not extend Hook interface
     */
    public function __construct(string $interfaceName, string $index = '')
    {
        if (!\interface_exists($interfaceName)) {
            throw new \UnexpectedValueException(
                'The interface ' . $interfaceName . ' does not exist.',
                1565089078
            );
        }
        if (!\in_array(Hook::class, \class_implements($interfaceName), true)) {
            throw new \UnexpectedValueException(
                'The interface ' . $interfaceName . ' does not extend ' . Hook::class . ' interface.',
                1565088963
            );
        }

        $this->interfaceName = $interfaceName;
        $this->index = empty($index) ? $interfaceName : $index;
    }

    /**
     * Gets the hook objects for the interface.
     *
     * @return array
     *         the hook objects, will be empty if no hooks have been set
     */
    public function getHooks(): array
    {
        $this->retrieveHooks();

        return $this->hookObjects;
    }

    /**
     * Retrieves the hook objects for the interface.
     *
     * @throws \UnexpectedValueException
     *         if there are registered hook classes that do not implement the
     *         $this->interfaceName interface
     */
    protected function getHooks(): array
    {
        if ($this->hooksHaveBeenRetrieved) {
            return;
        }

        $hookClasses = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][$this->index] ?? [];
        foreach ((array)$hookClasses as $hookClass) {
            $hookInstance = GeneralUtility::makeInstance($hookClass);
            if (!($hookInstance instanceof $this->interfaceName)) {
                throw new \UnexpectedValueException(
                    'The class ' . \get_class($hookInstance) . ' is registered for the ' . $this->index .
                        ' hook list, but does not implement the ' . $this->interfaceName . ' interface.',
                    1448901897
                );
            }
            $this->hookObjects[] = $hookInstance;
        }

        $this->hooksHaveBeenRetrieved = true;
    }
}

if (\defined('TYPO3_MODE')
    && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Classes/Hooks/Hook.php'])
) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Classes/Hooks/Hook.php']);
}
