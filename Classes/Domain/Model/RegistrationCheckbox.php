<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Domain\Model;

use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * This class represents any arbitrary checkbox in the registration form, e.g., "will arrive late" or "brings a dog".
 */
class RegistrationCheckbox extends AbstractEntity
{
    /**
     * @Validate("StringLength", options={"maximum": 255})
     */
    protected string $title = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $name): void
    {
        $this->title = $name;
    }
}
