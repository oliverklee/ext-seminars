<?php
declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks;

use OliverKlee\Seminars\Interfaces\Hook;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class provides functions for unified hooks.
 *
 * A hook allows to add functionality at certain points of the program path. These points are
 * grouped using an interface, declaring the methods to implement to hook in.
 *
 * Hooking in
 * The FQCN of the interface is the key to `$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']`
 * where you register your hooked-in classes. Your class will be instantiated once when the first
 * point is reached and re-used for all other points.
 *
 * Implementing hook points
 * Instantiate this class with the interface you need implemented. First call to `getHooks()` will
 * instantiate the registered classes. Every further call will return the same instances. On each
 * member call the method required at the point in your program.
 *
 * There is an optional index to `$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']`, provided
 * for easier conversion of existing hooks to this class.
 *
 * TODO: There should be `->executeHook(string $method, ...$params)` instead of returning the array of objects.
 * How to handle return values in that case? Is there a need for ensured sequence of execution?
 *
 * @author Michael Kramer <m.kramer@mxp.de>
 */
class HookProvider
{
    /**
     * @var string
     */
    protected $interfaceName = '';

    /**
     * Index in `$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']` of hooked-in classes
     *
     * @var string
     */
    protected $index = '';

    /**
     * @var array
     */
    protected $hookObjects = [];

    /**
     * @var bool
     */
    protected $hooksHaveBeenRetrieved = false;

    /**
     * The constructor.
     *
     * @param $interfaceName interface the hook needs implemented
     * @param $index optional index to `$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']`
     *               if not using the interface name (for backwards compatibility)
     *               (the interface name is recommended)
     *
     * @throws \UnexpectedValueException
     *         if $interfaceName does not extend `\OliverKlee\Seminars\Interfaces\Hook` interface
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
        $this->index = $index === '' ? $interfaceName : $index;
    }

    /**
     * Gets the hook objects for the interface.
     *
     * @return array Hook[]
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
     */
    protected function retrieveHooks()
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

