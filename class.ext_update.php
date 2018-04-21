<?php

/**
 * This class offers functions to update the database from one version to another.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ext_update
{
    /**
     * Returns the update module content.
     *
     * @return string the update module content, will be empty if nothing was updated
     */
    public function main()
    {
        return '';
    }

    /**
     * Checks whether the update module may be accessed.
     *
     * @return bool true if the update module may be accessed, false otherwise
     */
    public function access()
    {
        return false;
    }
}
