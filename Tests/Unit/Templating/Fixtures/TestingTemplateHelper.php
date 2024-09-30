<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Templating\Fixtures;

use OliverKlee\Seminars\Templating\TemplateHelper;

/**
 * This is mere a class used for unit tests. Don't use it for any other purpose.
 */
final class TestingTemplateHelper extends TemplateHelper
{
    /**
     * The constructor.
     *
     * @param array<string, mixed> $configuration TypoScript setup configuration, may be empty
     */
    public function __construct(array $configuration = [])
    {
        $this->init($configuration);
    }

    /**
     * Intvals all piVars that are supposed to be integers:
     * showUid, pointer, mode
     *
     * If some piVars are not set or no piVars array is defined yet, this
     * function will set the not yet existing piVars to zero.
     *
     * @param array<array-key, string> $additionalPiVars keys for $this->piVars that will be ensured to exist
     *        as integers in `$this->piVars` as well
     */
    public function ensureIntegerPiVars(array $additionalPiVars = []): void
    {
        parent::ensureIntegerPiVars($additionalPiVars);
    }

    /**
     * Ensures that $this->cObj points to a valid content object.
     *
     * If this object already has a valid cObj, this function does nothing.
     *
     * If there is a front end and this object does not have a cObj yet, the
     * cObj from the front end is used.
     *
     * If this object has no cObj and there is no front end, this function will
     * do nothing.
     */
    public function ensureContentObject(): void
    {
        parent::ensureContentObject();
    }

    public function dropContentObjectRenderer(): void
    {
        $this->cObj = null;
    }
}
