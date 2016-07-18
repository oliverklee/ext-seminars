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
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * This class represents an object that is created from a DB record or can be written to a DB record.
 *
 * It will hold the corresponding data and can commit that data to the DB.
 *
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class Tx_Seminars_OldModel_Abstract extends Tx_Oelib_TemplateHelper implements Tx_Oelib_Interface_ConfigurationCheckable
{
    /**
     * @var string the extension key
     */
    public $extKey = 'seminars';

    /**
     * faking $this->scriptRelPath so the locallang.xlf file is found
     *
     * @var string
     */
    public $scriptRelPath = 'Resources/Private/Language/locallang.xlf';

    /**
     * @var string the charset that is used for the output
     */
    protected $renderCharset = 'utf-8';

    /**
     * @var CharsetConverter helper for charset conversion
     */
    protected $charsetConversion = null;

    /**
     * @var string the name of the SQL table this class corresponds to
     */
    protected $tableName = '';

    /**
     * @var array the values from/for the DB
     */
    protected $recordData = [];

    /**
     * @var bool whether this record already is stored in the DB
     */
    protected $isInDb = false;

    /**
     * The constructor. Creates a test instance from a DB record.
     *
     * @param int $uid
     *        The UID of the record to retrieve from the DB. This parameter will be ignored if $dbResult is provided.
     * @param mysqli_result|null $dbResult
     *        MySQL result (of SELECT query) object. If this parameter is provided, $uid will be ignored.
     * @param bool $allowHiddenRecords
     *        whether it is possible to create an object from a hidden record
     */
    public function __construct($uid, $dbResult = null, $allowHiddenRecords = false)
    {
        $this->initializeCharsetConversion();

        // In the back end, include the extension's locallang.xlf.
        if ((TYPO3_MODE === 'BE') && is_object($GLOBALS['LANG'])) {
            $GLOBALS['LANG']->includeLLFile('EXT:seminars/Resources/Private/Language/locallang.xlf');
        }

        $this->retrieveRecordAndGetData($uid, $dbResult, $allowHiddenRecords);
        $this->init();
    }

    /**
     * The destructor.
     */
    public function __destruct()
    {
        unset($this->charsetConversion);
        parent::__destruct();
    }

    /**
     * Retrieves this record's data from the DB (if it has not been retrieved
     * yet) and gets the record data from the DB result.
     *
     * @param int $uid
     *        The UID of the record to retrieve from the DB. This parameter will be ignored if $dbResult is provided.
     * @param mysqli_result|bool $dbResult
     *        MySQL result (of SELECT query) object. If this parameter is provided, $uid will be ignored.
     * @param bool $allowHiddenRecords
     *        whether it is possible to create an object from a hidden record
     *
     * @return void
     */
    protected function retrieveRecordAndGetData(
        $uid, $dbResult = false, $allowHiddenRecords = false
    ) {
        if (!$dbResult) {
            $dbResult = $this->retrieveRecord($uid, $allowHiddenRecords);
        }

        if ($dbResult) {
            $data = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
            if ($data) {
                $this->getDataFromDbResult($data);
            }
        }
    }

    /**
     * Reads the record data from an DB query result represented as an
     * associative array and stores it in $this->recordData.
     * The column names will be used as array keys.
     * The column names must *not* be prefixed with the table name.
     *
     * Before this function may be called, $this->tableName must be set
     * to the corresponding DB table name.
     *
     * If at least one element is taken, this function sets $this->isInDb to TRUE.
     *
     * Example:
     * $dbResultRow['name'] => $this->recordData['name']
     *
     * @param array $dbResultRow associative array of a DB query result
     *
     * @return void
     */
    protected function getDataFromDbResult(array $dbResultRow)
    {
        if (!empty($this->tableName) && !empty($dbResultRow)) {
            $this->recordData = $dbResultRow;
            $this->isInDb = true;
        }
    }

    /**
     * Checks whether this object has been properly initialized,
     * has a non-empty table name set and thus is basically usable.
     *
     * @return bool TRUE if the object has been initialized, FALSE otherwise
     */
    public function isOk()
    {
        return !empty($this->recordData) && ($this->tableName !== '');
    }

    /**
     * Gets a trimmed string element of the record data array.
     * If the array has not been initialized properly, an empty string is
     * returned instead.
     *
     * @param string $key key of the element to return
     *
     * @return string the corresponding element from the record data array
     */
    public function getRecordPropertyString($key)
    {
        $result = $this->hasKey($key)
            ? trim($this->recordData[$key]) : '';

        return $result;
    }

    /**
     * Gets a decimal element of the record data array.
     * If the array has not been initialized properly, '0.00' is returned
     * instead.
     *
     * @param string $key key of the element to return
     *
     * @return string the corresponding element from the record data array
     */
    public function getRecordPropertyDecimal($key)
    {
        $result = $this->hasKey($key)
            ? trim($this->recordData[$key]) : '0.00';

        return $result;
    }

    /**
     * Checks a string element of the record data array for existence and
     * non-emptiness.
     *
     * @param string $key key of the element to check
     *
     * @return bool TRUE if the corresponding string exists and is non-empty
     */
    public function hasRecordPropertyString($key)
    {
        return ($this->getRecordPropertyString($key) != '');
    }

    /**
     * Checks an integer element of the record data array for existence and
     * non-zeroness.
     *
     * @param string $key key of the element to check
     *
     * @return bool TRUE if the corresponding value exists and is non-zero
     */
    public function hasRecordPropertyInteger($key)
    {
        return $this->getRecordPropertyInteger($key) !== 0;
    }

    /**
     * Checks a decimal element of the record data array for existence and
     * value != 0.00.
     *
     * @param string $key key of the element to check
     *
     * @return bool TRUE if the corresponding field exists and its value
     *                 is not "0.00".
     */
    public function hasRecordPropertyDecimal($key)
    {
        return $this->getRecordPropertyDecimal($key) != '0.00';
    }

    /**
     * Gets an int element of the record data array.
     * If the array has not been initialized properly, 0 is returned instead.
     *
     * @param string $key key of the element to return
     *
     * @return int the corresponding element from the record data array
     */
    public function getRecordPropertyInteger($key)
    {
        return $this->hasKey($key) ? (int)$this->recordData[$key] : 0;
    }

    /**
     * Sets an int element of the record data array.
     *
     * @param string $key key of the element to set (must be non-empty)
     * @param int $value the value that will be written into the element
     *
     * @return void
     */
    protected function setRecordPropertyInteger($key, $value)
    {
        if (!empty($key)) {
            $this->recordData[$key] = (int)$value;
        }
    }

    /**
     * Sets a string element of the record data array (and trims it).
     *
     * @param string $key key of the element to set (must be non-empty)
     * @param string $value the value that will be written into the element
     *
     * @return void
     */
    protected function setRecordPropertyString($key, $value)
    {
        if (!empty($key)) {
            $this->recordData[$key] = trim((string) $value);
        }
    }

    /**
     * Sets a boolean element of the record data array.
     *
     * @param string $key key of the element to set (must be non-empty)
     * @param bool $value the value that will be written into the element
     *
     * @return void
     */
    protected function setRecordPropertyBoolean($key, $value)
    {
        if (!empty($key)) {
            $this->recordData[$key] = (bool)$value;
        }
    }

    /**
     * Gets an element of the record data array, converted to a boolean.
     * If the array has not been initialized properly, FALSE is returned.
     *
     * @param string $key key of the element to return
     *
     * @return bool the corresponding element from the record data array
     */
    public function getRecordPropertyBoolean($key)
    {
        return $this->hasKey($key) ? ((bool)$this->recordData[$key]) : false;
    }

    /**
     * Checks whether $this->recordData is initialized at all and
     * whether a given key exists.
     *
     * @param string $key the array key to search for
     *
     * @return bool TRUE if $this->recordData has been initialized
     *                 and the array key exists, FALSE otherwise
     */
    private function hasKey($key)
    {
        return ($this->isOk() && !empty($key) && isset($this->recordData[$key]));
    }

    /**
     * Writes this record to the DB.
     *
     * The UID of the parent page must be set in $this->recordData['pid'].
     * (otherwise the record will be created in the root page).
     *
     * @return bool TRUE if everything went OK, FALSE otherwise
     */
    public function commitToDb()
    {
        if (!$this->isOk()) {
            return false;
        }

        // Saves the current time so that tstamp and crdate will be the same.
        $now = $GLOBALS['SIM_EXEC_TIME'];
        $this->setRecordPropertyInteger('tstamp', $now);

        if (!$this->isInDb || !$this->hasUid()) {
            $this->setRecordPropertyInteger('crdate', $now);
            Tx_Oelib_Db::insert(
                $this->tableName, $this->recordData
            );
        } else {
            Tx_Oelib_Db::update(
                $this->tableName,
                'uid = ' . $this->getUid(),
                $this->recordData
            );
        }

        $this->isInDb = true;
        return true;
    }

    /**
     * Commits the changes of an record to the database.
     *
     * @param array $updateArray
     *        an associative array with the keys being the field names and the value being the field values, may be empty
     *
     * @return void
     */
    public function saveToDatabase(array $updateArray)
    {
        if (empty($updateArray)) {
            return;
        }

        Tx_Oelib_Db::update(
            $this->tableName,
            'uid = ' . $this->getUid(),
            $updateArray
        );
    }

    /**
     * Adds m:n records that are referenced by this record.
     *
     * Before this function may be called, $this->recordData['uid'] must be set
     * correctly.
     *
     * @param string $mmTable
     *        the name of the m:n table, having the fields uid_local, uid_foreign and sorting, must not be empty
     * @param int[] $references
     *        UIDs of records from the foreign table to which we should create references, may be empty
     *
     * @return int the number of created m:n records
     *
     * @throws InvalidArgumentException
     * @throws BadMethodCallException
     */
    protected function createMmRecords($mmTable, array $references)
    {
        if ($mmTable == '') {
            throw new InvalidArgumentException('$mmTable must not be empty.', 1333292359);
        }
        if (!$this->hasUid()) {
            throw new BadMethodCallException('createMmRecords may only be called on objects that have a UID.', 1333292371);
        }
        if (empty($references)) {
            return 0;
        }

        $numberOfCreatedMmRecords = 0;
        $isDummyRecord = $this->getRecordPropertyBoolean('is_dummy_record');

        $sorting = 1;

        foreach ($references as $currentRelationUid) {
            // We might get unsafe data here, so better be safe.
            $foreignUid = (int)$currentRelationUid;
            if ($foreignUid > 0) {
                $dataToInsert = [
                    'uid_local' => $this->getUid(),
                    'uid_foreign' => $foreignUid,
                    'sorting' => $sorting,
                    'is_dummy_record' => $isDummyRecord,
                ];
                Tx_Oelib_Db::insert(
                    $mmTable, $dataToInsert
                );
                $sorting++;
                $numberOfCreatedMmRecords++;
            }
        }

        return $numberOfCreatedMmRecords;
    }

    /**
     * Checks whether a non-deleted record with a given UID exists in the DB.
     *
     * If the parameter $allowHiddenRecords is set to TRUE, hidden records will be selected, too.
     *
     * This method may be called statically.
     *
     * @param string $uid string with a UID (need not necessarily be escaped, will be cast to int)
     * @param string $tableName string with the table name where the UID should be searched for
     * @param bool $allowHiddenRecords whether hidden records should be found as well
     *
     * @return bool TRUE if a visible record with that UID exists, FALSE otherwise
     */
    public static function recordExists($uid, $tableName, $allowHiddenRecords = false)
    {
        if (((int)$uid <= 0) || ($tableName === '')) {
            return false;
        }

        $dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'COUNT(*) AS num',
            $tableName,
            'uid = ' . (int)$uid . Tx_Oelib_Db::enableFields($tableName, (int)$allowHiddenRecords)
        );

        if ($dbResult) {
            $dbResultAssoc = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult);
            $GLOBALS['TYPO3_DB']->sql_free_result($dbResult);
            $result = (int)$dbResultAssoc['num'] === 1;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Retrieves a record from the database.
     *
     * The record is retrieved from $this->tableName. Therefore $this->tableName
     * has to be set before calling this method.
     *
     * @param int $uid the UID of the record to retrieve from the DB
     * @param bool $allowHiddenRecords whether to allow hidden records
     *
     * @return mysqli_result MySQL result (of SELECT query) object, will be FALSE if the UID is invalid
     */
    protected function retrieveRecord($uid, $allowHiddenRecords = false)
    {
        if (!self::recordExists($uid, $this->tableName, $allowHiddenRecords)) {
            return false;
        }

        return $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            '*',
            $this->tableName,
            'uid=' . (int)$uid . Tx_Oelib_Db::enableFields($this->tableName, $allowHiddenRecords),
            '',
            '',
            '1'
        );
    }

    /**
     * Gets our UID.
     *
     * @return int our UID (or 0 if there is an error)
     */
    public function getUid()
    {
        return $this->getRecordPropertyInteger('uid');
    }

    /**
     * Checks whether this object has a UID.
     *
     * @return bool TRUE if this object has a UID, FALSE otherwise
     */
    public function hasUid()
    {
        return $this->hasRecordPropertyInteger('uid');
    }

    /**
     * Gets our title.
     *
     * @return string our title (or '' if there is an error)
     */
    public function getTitle()
    {
        return $this->getRecordPropertyString('title');
    }

    /**
     * Sets the title element of the record data array.
     *
     * @param string $title the value that will be written into the title element
     *
     * @return void
     */
    public function setTitle($title)
    {
        $this->setRecordPropertyString('title', $title);
    }

    /**
     * Gets our PID.
     *
     * @return int our PID (or 0 if there is an error)
     */
    public function getCurrentBePageId()
    {
        $result = parent::getCurrentBePageId();

        if (!$result) {
            $result = $this->getRecordPropertyInteger('pid');
        }

        return $result;
    }

    /**
     * Gets an HTML image tag with the URL of the icon file of the record as
     * configured in TCA.
     *
     * @return string our HTML image tag with the URL of the icon file of
     *                the record or a "not found" icon if there's no icon
     *                for this record
     */
    public function getRecordIcon()
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 7006000) {
            return $this->createRecordIconForTypo3Version76AndUp();
        } else {
            return $this->createRecordIconForTypo3UpTo62();
        }
    }

    /**
     * @return string
     */
    protected function createRecordIconForTypo3Version76AndUp()
    {
        /** @var \TYPO3\CMS\Core\Imaging\IconFactory $iconFactory */
        $iconFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class);
        return $iconFactory->getIconForRecord(
            $this->tableName, $this->recordData, \TYPO3\CMS\Core\Imaging\Icon::SIZE_SMALL
        )->render();
    }

    /**
     * @return string
     */
    protected function createRecordIconForTypo3UpTo62()
    {
        $iconProperties = [];
        $tableConfiguration = $GLOBALS['TCA'][$this->tableName]['ctrl'];
        $hiddenColumn = $tableConfiguration['enablecolumns']['disabled'];
        $startTimeColumn = $tableConfiguration['enablecolumns']['starttime'];
        $endTimeColumn = $tableConfiguration['enablecolumns']['endtime'];
        // Checks if there are enable columns configured in TCA and sends them
        // as parameter to IconUtility::getIcon().
        if ($this->getRecordPropertyBoolean($hiddenColumn)) {
            $iconProperties[$hiddenColumn] = $this->getRecordPropertyInteger($hiddenColumn);
        }
        if ($this->hasRecordPropertyInteger($startTimeColumn)) {
            $iconProperties[$startTimeColumn] = $this->getRecordPropertyInteger($startTimeColumn);
        }
        if ($this->hasRecordPropertyInteger($endTimeColumn)) {
            $iconProperties[$endTimeColumn] = $this->getRecordPropertyInteger($endTimeColumn);
        }
        if (isset($tableConfiguration['typeicon_column'])) {
            $typeIconColumn = $tableConfiguration['typeicon_column'];
            $iconProperties[$typeIconColumn] = $this->getRecordPropertyInteger($typeIconColumn);
        }
        $imageUrl = $GLOBALS['BACK_PATH'] . IconUtility::getIcon($this->tableName, $iconProperties);
        $uid = $this->getUid();

        return '<img src="' . htmlspecialchars($imageUrl) . '" title="id=' . $uid . '" alt="' . $uid . '" />';
    }

    /**
     * Marks this object as a dummy record (when it is written to the DB).
     *
     * @return void
     */
    public function enableTestMode()
    {
        $this->setRecordPropertyBoolean('is_dummy_record', true);
    }

    /**
     * Sets the current charset in $this->renderCharset and the charset
     * conversion instance in $this->$charsetConversion.
     *
     * @return void
     *
     * @throws RuntimeException
     */
    protected function initializeCharsetConversion()
    {
        if (isset($GLOBALS['TSFE'])) {
            $this->renderCharset = $GLOBALS['TSFE']->renderCharset;
            $this->charsetConversion = $GLOBALS['TSFE']->csConvObj;
        } elseif (isset($GLOBALS['LANG'])) {
            $this->renderCharset = $GLOBALS['LANG']->charset;
            $this->charsetConversion = $GLOBALS['LANG']->csConvObj;
        } else {
            throw new RuntimeException('There was neither a front end nor a back end detected.', 1333292389);
        }

        if ($this->renderCharset === null || $this->renderCharset === '') {
            $this->renderCharset = 'utf-8';
        }
    }

    /**
     * Returns this record's page UID.
     *
     * @return int the page UID for this record, will be >= 0
     */
    public function getPageUid()
    {
        return $this->getRecordPropertyInteger('pid');
    }

    /**
     * Returns the prefix for the configuration to check, e.g. "plugin.tx_seminars_pi1.".
     *
     * @return string the namespace prefix, will end with a dot
     */
    public function getTypoScriptNamespace()
    {
        return 'plugin.tx_seminars.';
    }
}
