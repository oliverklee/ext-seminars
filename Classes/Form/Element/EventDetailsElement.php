<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Form\Element;

use TYPO3\CMS\Backend\Form\Element\GroupElement;

class EventDetailsElement extends GroupElement
{
    public function render(): array
    {
        $result = parent::render();
        $result['html'] = '<p>Hello world!</p>' . $result['html'];

        return $result;
    }
}
