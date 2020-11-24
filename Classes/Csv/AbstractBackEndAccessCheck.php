<?php

declare(strict_types=1);

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * This class provides an access check for the CSV export in the back end.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class Tx_Seminars_Csv_AbstractBackEndAccessCheck implements \Tx_Seminars_Interface_CsvAccessCheck
{
    /**
     * @var int
     *
     * @see BackendUtility::getRecord
     */
    const SHOW_PAGE_PERMISSION_BITS = 1;

    /**
     * @var int
     */
    protected $pageUid = 0;

    /**
     * Sets the page UID of the records.
     *
     * @param int $pageUid the page UID of the records, must be >= 0
     *
     * @return void
     */
    public function setPageUid(int $pageUid)
    {
        $this->pageUid = $pageUid;
    }

    /**
     * Returns the page UID of the records to check.
     *
     * @return int the page UID, will be >= 0
     */
    protected function getPageUid(): int
    {
        return $this->pageUid;
    }

    /**
     * Checks whether the currently logged-in BE-User is allowed to access the given table and page.
     *
     * @param string $tableName
     *        the name of the table to check the read access for, must not be empty
     * @param int $pageUid the page to check the access for, must be >= 0
     *
     * @return bool TRUE if the user has access to the given table and page,
     *                 FALSE otherwise, will also return FALSE if no BE user is logged in
     */
    protected function canAccessTableAndPage(string $tableName, int $pageUid): bool
    {
        if (!Tx_Oelib_BackEndLoginManager::getInstance()->isLoggedIn()) {
            return false;
        }

        return $this->hasReadAccessToTable($tableName) && $this->hasReadAccessToPage($pageUid);
    }

    /**
     * Returns the logged-in back-end user.
     *
     * @return BackendUserAuthentication
     */
    protected function getLoggedInBackEndUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Checks whether the logged-in back-end user has read access to the table $tableName.
     *
     * @param string $tableName the table name to check, must not be empty
     *
     * @return bool
     */
    protected function hasReadAccessToTable(string $tableName): bool
    {
        return $this->getLoggedInBackEndUser()->check('tables_select', $tableName);
    }

    /**
     * Checks whether the logged-in back-end user has read access to the page (or folder) with the UID $pageUid.
     *
     * @param int $pageUid the page to check the access for, must be >= 0
     *
     * @return bool
     */
    protected function hasReadAccessToPage(int $pageUid): bool
    {
        return $this->getLoggedInBackEndUser()
            ->doesUserHaveAccess(BackendUtility::getRecord('pages', $pageUid), self::SHOW_PAGE_PERMISSION_BITS);
    }
}
