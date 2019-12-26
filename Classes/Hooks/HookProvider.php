<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Hooks;

use OliverKlee\Seminars\Hooks\Interfaces\Hook;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Provides functions for unified hooks.
 *
 * A hook allows adding functionality at certain points of the program path. These points are
 * grouped using an interface, declaring the methods to implement to hook in.
 *
 * Hooking in
 * The FQCN of the interface is the key to `$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']`
 * where you register your hooked-in classes. Your class will be instantiated once when the first
 * point is reached and re-used for all other points.
 *
 * Implementing hook points
 * Instantiate this class with the interface you need implemented. First call to `executeHook()` will
 * instantiate the registered classes. Every further call will reuse the same instances. On each
 * call provide the method required at the point in your program.
 *
 * There is an optional index to `$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']`, provided
 * for easier conversion of existing hooks to this class.
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
     * @var Hook[]
     */
    protected $hookObjects = [];

    /**
     * @var bool
     */
    protected $hooksHaveBeenRetrieved = false;

    /**
     * @param string $interfaceName interface the hook needs implemented
     * @param string $index index to `$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']`
     *               if not using the interface name (for backwards compatibility)
     *               (the interface name is recommended)
     *
     * @throws \UnexpectedValueException
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
     * Executes the hooked-in methods.
     *
     * @param string $method the method to execute
     * @param mixed $params parameters to $method()
     *
     * @return void
     */
    public function executeHook(string $method, ...$params)
    {
        $this->validateHookMethod($method);

        foreach ($this->getHooks() as $hook) {
            $hook->$method(...$params);
        }
    }

    /**
     * Gets the hook objects for the interface.
     *
     * @return Hook[]
     */
    protected function getHooks(): array
    {
        $this->retrieveHooks();

        return $this->hookObjects;
    }

    /**
     * Retrieves the hook objects for the interface.
     *
     * @return void
     *
     * @throws \UnexpectedValueException
     */
    protected function retrieveHooks()
    {
        if ($this->hooksHaveBeenRetrieved) {
            return;
        }

        $hookClasses = (array)($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars'][$this->index] ?? []);
        foreach ($hookClasses as $hookClass) {
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

    /**
     * Validates the requested hooked-in methods.
     *
     * @param string $method the method to execute
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    protected function validateHookMethod(string $method)
    {
        if ($method === '') {
            throw new \InvalidArgumentException('The parameter $method must not be empty.', 1573479911);
        }
        if (!\in_array($method, \get_class_methods($this->interfaceName), true)) {
            throw new \UnexpectedValueException(
                'The interface ' . $this->interfaceName . ' does not contain method ' . $method . '.',
                1573480302
            );
        }
    }
}
