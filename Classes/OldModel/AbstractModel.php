<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\OldModel;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents an object that is created from a DB record or can be written to a DB record.
 *
 * It will hold the corresponding data and can commit that data to the DB.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class AbstractModel extends \Tx_Oelib_TemplateHelper
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
     * @var string the name of the SQL table this class corresponds to
     */
    protected static $tableName = '';

    /**
     * @var bool whether to call `TemplateHelper::init()` during construction
     */
    protected $needsTemplateHelperInitialization = true;

    /**
     * @var array the values from/for the DB
     */
    protected $recordData = [];

    /**
     * @var bool
     */
    protected $isPersisted = false;

    /**
     * The constructor. Creates a test instance from a DB record.
     *
     * @param int $uid
     *        The UID of the record to retrieve from the DB. This parameter will be ignored if $dbResult is provided.
     * @param \mysqli_result|bool $dbResult
     *        MySQL result (of SELECT query) object. If this parameter is provided, $uid will be ignored.
     * @param bool $allowHidden
     *        whether it is possible to create an object from a hidden record
     */
    public function __construct(int $uid = 0, $dbResult = false, bool $allowHidden = false)
    {
        if ($uid > 0) {
            $data = self::fetchDataByUid($uid, $allowHidden);
            if (\is_array($data)) {
                $this->setData($data);
            }
        } elseif ($dbResult !== false) {
            $this->retrieveDataFromDatabase($dbResult);
        }

        if ($this->needsTemplateHelperInitialization) {
            $this->init();
        }
    }

    /**
     * Creates a new instance that has the given data.
     *
     * @param array $data
     *
     * @return AbstractModel
     */
    public static function fromData(array $data): AbstractModel
    {
        $model = new static();
        $model->setData($data);

        return $model;
    }

    /**
     * Instantiates a model from the database. If there is no record with the given UID, this method will return null.
     *
     * @param int $uid
     * @param bool $allowHidden
     *
     * @return AbstractModel|null
     */
    public static function fromUid(int $uid, bool $allowHidden = false)
    {
        $data = self::fetchDataByUid($uid, $allowHidden);

        return \is_array($data) ? self::fromData($data) : null;
    }

    /**
     * @param int $uid
     * @param bool $allowHidden
     *
     * @return array|false
     */
    protected static function fetchDataByUid(int $uid, bool $allowHidden)
    {
        $query = self::getQueryBuilderForOwnTable();
        if ($allowHidden) {
            $query->getRestrictions()->removeByType(HiddenRestriction::class);
        }
        $query->select('*')->from(static::$tableName);
        $query->andWhere($query->expr()->eq('uid', $query->createNamedParameter($uid)));

        return $query->execute()->fetch();
    }

    /**
     * Retrieves this record's data the DB result.
     *
     * @param \mysqli_result|bool $dbResult
     *        MySQL result (of SELECT query) object. If this parameter is provided, $uid will be ignored.
     *
     * @return void
     */
    protected function retrieveDataFromDatabase($dbResult)
    {
        if ($dbResult === false) {
            return;
        }

        $data = \Tx_Oelib_Db::getDatabaseConnection()->sql_fetch_assoc($dbResult);
        if (\is_array($data)) {
            $this->setData($data);
        }
    }

    /**
     * Sets the record data from an DB query result represented as an
     * associative array and stores it in $this->recordData.
     * The column names will be used as array keys.
     * The column names must *not* be prefixed with the table name.
     *
     * If at least one element is taken, this function sets $this->isInDb to TRUE.
     *
     * Example:
     * $dbResultRow['name'] => $this->recordData['name']
     *
     * @param array $data associative array of a DB query result
     *
     * @return void
     */
    protected function setData(array $data)
    {
        $this->recordData = $data;
        if (!empty($data['uid'])) {
            $this->isPersisted = true;
        }
    }

    /**
     * Checks whether this object has been properly initialized,
     * has a non-empty table name set and thus is basically usable.
     *
     * @return bool TRUE if the object has been initialized, FALSE otherwise
     */
    public function isOk(): bool
    {
        return !empty($this->recordData) && static::$tableName !== '';
    }

    /**
     * Checks whether this model has been read from the database.
     *
     * @return bool
     */
    public function comesFromDatabase(): bool
    {
        return $this->hasUid();
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
    public function getRecordPropertyString(string $key): string
    {
        return $this->hasKey($key) ? \trim((string)$this->recordData[$key]) : '';
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
    public function getRecordPropertyDecimal(string $key): string
    {
        return $this->hasKey($key) ? \trim((string)$this->recordData[$key]) : '0.00';
    }

    /**
     * Checks a string element of the record data array for existence and
     * non-emptiness.
     *
     * @param string $key key of the element to check
     *
     * @return bool TRUE if the corresponding string exists and is non-empty
     */
    public function hasRecordPropertyString(string $key): bool
    {
        return $this->getRecordPropertyString($key) !== '';
    }

    /**
     * Checks an integer element of the record data array for existence and
     * non-zeroness.
     *
     * @param string $key key of the element to check
     *
     * @return bool TRUE if the corresponding value exists and is non-zero
     */
    public function hasRecordPropertyInteger(string $key): bool
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
    public function hasRecordPropertyDecimal(string $key): bool
    {
        $emptyValues = ['', '0.00', '0.0', '0'];

        return !\in_array($this->getRecordPropertyDecimal($key), $emptyValues, true);
    }

    /**
     * Gets an int element of the record data array.
     * If the array has not been initialized properly, 0 is returned instead.
     *
     * @param string $key key of the element to return
     *
     * @return int the corresponding element from the record data array
     */
    public function getRecordPropertyInteger(string $key): int
    {
        return $this->hasKey($key) ? (int)$this->recordData[$key] : 0;
    }

    /**
     * @param string $key
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    private function assertNonEmptyKey(string $key)
    {
        if ($key === '') {
            throw new \InvalidArgumentException('$key must not be empty.', 1574548978);
        }
    }

    /**
     * Sets an int element of the record data array.
     *
     * @param string $key key of the element to set (must be non-empty)
     * @param int $value the value that will be written into the element
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    protected function setRecordPropertyInteger(string $key, int $value)
    {
        $this->assertNonEmptyKey($key);

        $this->recordData[$key] = $value;
    }

    /**
     * Sets a string element of the record data array (and trims it).
     *
     * @param string $key key of the element to set (must be non-empty)
     * @param string $value the value that will be written into the element
     *
     * @return void
     */
    protected function setRecordPropertyString(string $key, string $value)
    {
        $this->assertNonEmptyKey($key);

        $this->recordData[$key] = \trim($value);
    }

    /**
     * Sets a boolean element of the record data array.
     *
     * @param string $key key of the element to set (must be non-empty)
     * @param bool $value the value that will be written into the element
     *
     * @return void
     */
    protected function setRecordPropertyBoolean(string $key, $value)
    {
        $this->assertNonEmptyKey($key);

        $this->recordData[$key] = (bool)$value;
    }

    /**
     * Gets an element of the record data array, converted to a boolean.
     * If the array has not been initialized properly, FALSE is returned.
     *
     * @param string $key key of the element to return
     *
     * @return bool the corresponding element from the record data array
     */
    public function getRecordPropertyBoolean(string $key): bool
    {
        return $this->hasKey($key) ? (bool)$this->recordData[$key] : false;
    }

    /**
     * Checks whether $this->recordData is initialized at all and whether a given key exists.
     *
     * @param string $key the array key to search for
     *
     * @return bool TRUE if $this->recordData has been initialized and the array key exists, FALSE otherwise
     *
     * @throws \InvalidArgumentException
     */
    private function hasKey(string $key): bool
    {
        $this->assertNonEmptyKey($key);

        return isset($this->recordData[$key]);
    }

    /**
     * Writes this record to the DB.
     *
     * The UID of the parent page must be set in $this->recordData['pid'].
     * (otherwise the record will be created in the root page).
     *
     * @return bool TRUE if everything went OK, FALSE otherwise
     */
    public function commitToDb(): bool
    {
        if (!$this->isOk()) {
            return false;
        }

        // Saves the current time so that tstamp and crdate will be the same.
        $now = $GLOBALS['SIM_EXEC_TIME'];
        $this->setRecordPropertyInteger('tstamp', $now);

        if (!$this->isPersisted || !$this->hasUid()) {
            $this->setRecordPropertyInteger('crdate', $now);
            \Tx_Oelib_Db::insert(static::$tableName, $this->recordData);
        } else {
            \Tx_Oelib_Db::update(static::$tableName, 'uid = ' . $this->getUid(), $this->recordData);
        }

        $this->isPersisted = true;
        return true;
    }

    /**
     * Commits the changes of an existing record to the database.
     *
     * @param array $data associative array
     *
     * @return void
     */
    public function saveToDatabase(array $data)
    {
        if (empty($data)) {
            return;
        }

        self::getConnectionForOwnTable()->update(static::$tableName, $data, ['uid' => $this->getUid()]);
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
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    protected function createMmRecords(string $mmTable, array $references): int
    {
        if ($mmTable == '') {
            throw new \InvalidArgumentException('$mmTable must not be empty.', 1333292359);
        }
        if (!$this->hasUid()) {
            throw new \BadMethodCallException(
                'createMmRecords may only be called on objects that have a UID.',
                1333292371
            );
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
                \Tx_Oelib_Db::insert(
                    $mmTable,
                    $dataToInsert
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
     * @param int $uid
     * @param string $tableName string with the table name where the UID should be searched for
     * @param bool $allowHiddenRecords whether hidden records should be found as well
     *
     * @return bool true if a visible record with that UID exists, false otherwise
     */
    public static function recordExists($uid, string $tableName, bool $allowHiddenRecords = false): bool
    {
        if ($uid <= 0 || $tableName === '') {
            return false;
        }

        $whereClause = 'uid = ' . $uid . \Tx_Oelib_Db::enableFields($tableName, (int)$allowHiddenRecords);

        return \Tx_Oelib_Db::existsRecord($tableName, $whereClause);
    }

    /**
     * Retrieves a record from the database.
     *
     * The record is retrieved from static::$tableName. Therefore static::$tableName
     * has to be set before calling this method.
     *
     * @param int $uid the UID of the record to retrieve from the DB
     * @param bool $allowHiddenRecords whether to allow hidden records
     *
     * @return \mysqli_result|bool MySQL result (of SELECT query) object, will be false if the UID is invalid
     */
    protected function retrieveRecord(int $uid, bool $allowHiddenRecords = false)
    {
        return $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            '*',
            static::$tableName,
            'uid=' . $uid . \Tx_Oelib_Db::enableFields(static::$tableName, (int)$allowHiddenRecords),
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
    public function getUid(): int
    {
        return $this->getRecordPropertyInteger('uid');
    }

    /**
     * Checks whether this object has a UID.
     *
     * @return bool TRUE if this object has a UID, FALSE otherwise
     */
    public function hasUid(): bool
    {
        return $this->hasRecordPropertyInteger('uid');
    }

    /**
     * Gets our title.
     *
     * @return string our title (or '' if there is an error)
     */
    public function getTitle(): string
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
    public function setTitle(string $title)
    {
        $this->setRecordPropertyString('title', $title);
    }

    /**
     * Gets our PID.
     *
     * @return int our PID (or 0 if there is an error)
     */
    public function getCurrentBePageId(): int
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
    public function getRecordIcon(): string
    {
        /** @var \TYPO3\CMS\Core\Imaging\IconFactory $iconFactory */
        $iconFactory = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconFactory::class);
        return $iconFactory->getIconForRecord(static::$tableName, $this->recordData, Icon::SIZE_SMALL)->render();
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
     * Returns this record's page UID.
     *
     * @return int the page UID for this record, will be >= 0
     */
    public function getPageUid(): int
    {
        return $this->getRecordPropertyInteger('pid');
    }

    /**
     * Returns the prefix for the configuration to check, e.g. "plugin.tx_seminars_pi1.".
     *
     * @return string the namespace prefix, will end with a dot
     */
    public function getTypoScriptNamespace(): string
    {
        return 'plugin.tx_seminars.';
    }

    protected static function getQueryBuilderForOwnTable(): QueryBuilder
    {
        return self::getConnectionPool()->getQueryBuilderForTable(static::$tableName);
    }

    protected static function getQueryBuilderForTable(string $table): QueryBuilder
    {
        return self::getConnectionPool()->getQueryBuilderForTable($table);
    }

    protected static function getConnectionForOwnTable(): Connection
    {
        return self::getConnectionForTable(static::$tableName);
    }

    private static function getConnectionForTable(string $table): Connection
    {
        return self::getConnectionPool()->getConnectionForTable($table);
    }

    private static function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
