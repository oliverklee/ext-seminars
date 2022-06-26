<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\ViewHelpers;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a view helper for rendering the elements of a list as comma-separated titles.
 */
class CommaSeparatedTitlesViewHelper
{
    /**
     * Gets the titles of the elements in $list as a comma-separated list.
     *
     * The titles will be htmlspecialchared before being returned.
     *
     * @param Collection<AbstractModel&Titled> $items
     *
     * @return string the titles of the elements in $list as a comma-separated list
     *         or an empty string if the list is empty
     */
    public function render(Collection $items): string
    {
        $titles = [];

        foreach ($items as $element) {
            if (!$element instanceof Titled) {
                throw new \InvalidArgumentException(
                    'All elements in $list must implement the interface OliverKlee\\Seminars\\Model\\Interfaces\\Titled.',
                    1333658899
                );
            }

            $titles[] = \htmlspecialchars($element->getTitle(), ENT_QUOTES | ENT_HTML5);
        }

        return implode(', ', $titles);
    }
}
