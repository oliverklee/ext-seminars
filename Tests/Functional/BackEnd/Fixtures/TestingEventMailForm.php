<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Tests\Functional\BackEnd\Fixtures;

use OliverKlee\Seminars\BackEnd\AbstractEventMailForm;

/**
 * This class represents a testing implementation of the AbstractEventMailForm class.
 */
final class TestingEventMailForm extends AbstractEventMailForm
{
    /**
     * Returns the label for the submit button.
     *
     * @return string label for the submit button, will not be empty
     */
    protected function getSubmitButtonLabel(): string
    {
        return $this->getLanguageService()->getLL('eventMailForm_confirmButton');
    }
}
