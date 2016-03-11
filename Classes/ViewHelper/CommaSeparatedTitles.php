<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * This class represents a view helper for rendering the elements of a list as comma-separated titles.
 *
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
     * @param Tx_Oelib_List<Tx_Seminars_Interface_Titled> $list
     *
     * @return string the titles of the elements in $list as a comma-separated list or an empty string if the list is empty
     */
    public function render(Tx_Oelib_List $list)
    {
        $titles = array();

        /** @var Tx_Seminars_Interface_Titled $element */
        foreach ($list as $element) {
            if (!$element instanceof Tx_Seminars_Interface_Titled) {
                throw new InvalidArgumentException(
                    'All elements in $list must implement the interface Tx_Seminars_Interface_Titled.', 1333658899
                );
            }

            $titles[] = htmlspecialchars($element->getTitle());
        }

        return implode(', ', $titles);
    }
}
