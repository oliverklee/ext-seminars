<?php

declare(strict_types=1);

use OliverKlee\Seminars\Model\Interfaces\Titled;

/**
 * This class represents a view helper for rendering the elements of a list as comma-separated titles.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_ViewHelper_CommaSeparatedTitles
{
    /**
     * Gets the titles of the elements in $list as a comma-separated list.
     *
     * The titles will be htmlspecialchared before being returned.
     *
     * @param \Tx_Oelib_List<Tx_Seminars_Interface_Titled> $list
     *
     * @return string the titles of the elements in $list as a comma-separated list or an empty string if the list is empty
     */
    public function render(\Tx_Oelib_List $list): string
    {
        $titles = [];

        /** @var Titled $element */
        foreach ($list as $element) {
            if (!$element instanceof Titled) {
                throw new \InvalidArgumentException(
                    'All elements in $list must implement the interface Tx_Seminars_Interface_Titled.',
                    1333658899
                );
            }

            $titles[] = \htmlspecialchars($element->getTitle(), ENT_QUOTES | ENT_HTML5);
        }

        return implode(', ', $titles);
    }
}
