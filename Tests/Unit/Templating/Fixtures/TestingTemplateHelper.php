<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Unit\Templating\Fixtures;

use OliverKlee\Seminars\Templating\TemplateHelper;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This is mere a class used for unit tests. Don't use it for any other purpose.
 */
final class TestingTemplateHelper extends TemplateHelper
{
    /**
     * The constructor.
     *
     * @param array<string, mixed> $configuration TypoScript setup configuration, may be empty
     *
     * @phpstan-ignore-next-line Yes, we explicitly do not want to call the parent constructor.
     */
    public function __construct(array $configuration = [])
    {
        $this->init($configuration);
    }

    /**
     * Sets the salutation mode.
     *
     * @param string $salutation the salutation mode to use ("formal" or "informal")
     */
    public function setSalutationMode(string $salutation): void
    {
        $this->setConfigurationValue('salutation', $salutation);
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

    public function getContentObjectRenderer(): ?ContentObjectRenderer
    {
        return $this->cObj;
    }

    public function setContentObjectRenderer(ContentObjectRenderer $contentObjectRenderer): void
    {
        $this->cObj = $contentObjectRenderer;
    }

    public function dropContentObjectRenderer(): void
    {
        $this->cObj = null;
    }
}
