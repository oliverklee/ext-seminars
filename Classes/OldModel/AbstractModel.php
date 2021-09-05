<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\OldModel;

use OliverKlee\Oelib\Templating\TemplateHelper;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\EndTimeRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\StartTimeRestriction;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents an object that is created from a DB record or can be written to a DB record.
 *
 * It will hold the corresponding data and can commit that data to the DB.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class AbstractModel extends TemplateHelper
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
     * @param int $uid the UID of the record to retrieve from the DB
     * @param bool $allowHidden whether it is possible to create an object from a hidden record
     */
    public function __construct(int $uid = 0, bool $allowHidden = false)
    {
        if ($uid > 0) {
            $data = self::fetchDataByUid($uid, $allowHidden);
            if (\is_array($data)) {
                $this->setData($data);
            }
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
     * @return static
     */
    public static function fromData(array $data): AbstractModel
    {
        /** @var static $model */
        $model = GeneralUtility::makeInstance(static::class);
        $model->setData($data);

        return $model;
    }

    /**
     * Instantiates a model from the database. If there is no record with the given UID, this method will return null.
     *
     * @param int $uid
     * @param bool $allowHidden
     *
     * @return static|null
     */
    public static function fromUid(int $uid, bool $allowHidden = false)
    {
        if ($uid <= 0) {
            return null;
        }

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
            $restrictions = $query->getRestrictions();
            $restrictions->removeByType(HiddenRestriction::class);
            $restrictions->removeByType(StartTimeRestriction::class);
            $restrictions->removeByType(EndTimeRestriction::class);
        }
        $query->select('*')->from(static::$tableName);
        $query->andWhere($query->expr()->eq('uid', $query->createNamedParameter($uid)));

        return $query->execute()->fetch();
    }

    /**
     * Sets the record data from an DB query result represented as an
     * associative array and stores it in $this->recordData.
     * The column names will be used as array keys.
     * The column names must *not* be prefixed with the table name.
     *
     * If at least one element is taken, this function sets $this->isInDb to true.
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
     * @return bool true if the object has been initialized, false otherwise
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
     * @return bool true if the corresponding string exists and is non-empty
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
     * @return bool true if the corresponding value exists and is non-zero
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
     * @return bool true if the corresponding field exists and its value is not zero (with decimals)
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
     * @param mixed $value the value that will be written into the element
     *
     * @return void
     */
    protected function setRecordPropertyBoolean(string $key, $value)
    {
        $this->assertNonEmptyKey($key);

        $this->recordData[$key] = (int)(bool)$value;
    }

    /**
     * Gets an element of the record data array, converted to a boolean.
     * If the array has not been initialized properly, false is returned.
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
     * @return bool true if $this->recordData has been initialized and the array key exists, false otherwise
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
     * This method is to be preferred over `saveToDatabase`.
     *
     * @return bool true if everything went OK, false otherwise
     */
    public function commitToDatabase(): bool
    {
        if (!$this->isOk()) {
            return false;
        }

        $now = (int)$GLOBALS['SIM_EXEC_TIME'];
        $this->setRecordPropertyInteger('tstamp', $now);

        $connection = self::getConnectionForOwnTable();
        if (!$this->isPersisted || !$this->hasUid()) {
            $this->setRecordPropertyInteger('crdate', $now);
            $connection->insert(static::$tableName, $this->recordData);
            $this->setUid((int)$connection->lastInsertId(static::$tableName));
        } else {
            $connection->update(static::$tableName, $this->recordData, ['uid' => $this->getUid()]);
        }

        $this->isPersisted = true;

        return true;
    }

    /**
     * Adds m:n records that are referenced by this record.
     *
     * Before this function may be called, $this->recordData['uid'] must be set correctly.
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
        if ($mmTable === '') {
            throw new \InvalidArgumentException('$mmTable must not be empty.', 1333292359);
        }
        if (!$this->hasUid()) {
            throw new \BadMethodCallException('createMmRecords may only be called on objects with a UID.', 1333292371);
        }

        $dataTemplate = ['uid_local' => $this->getUid()];
        if ($this->getRecordPropertyBoolean('is_dummy_record')) {
            $dataTemplate['is_dummy_record'] = 1;
        }

        $connection = self::getConnectionForTable($mmTable);
        $recordCount = 0;
        foreach ($references as $foreignUid) {
            if ((int)$foreignUid <= 0) {
                continue;
            }

            $data = \array_merge(
                $dataTemplate,
                ['uid_foreign' => (int)$foreignUid, 'sorting' => $recordCount + 1]
            );
            $connection->insert($mmTable, $data);
            $recordCount++;
        }

        return $recordCount;
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
     * @param int $uid
     *
     * @return void
     */
    protected function setUid(int $uid)
    {
        $this->setRecordPropertyInteger('uid', $uid);
    }

    /**
     * Checks whether this object has a UID.
     *
     * @return bool true if this object has a UID, false otherwise
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
        /** @var IconFactory $iconFactory */
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
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

    /**
     * Gets a list of the titles of records referenced by the this record.
     *
     * @param string $foreignTable the name of the foreign table (must not be empty), having the uid and title fields
     * @param string $mmTable the name of the m:m table, having the uid_local, uid_foreign and sorting fields
     *
     * @return string[] the titles of the referenced records
     */
    protected function getMmRecordTitles(string $foreignTable, string $mmTable): array
    {
        return $this->getMmRecordTitlesByUid($foreignTable, $mmTable, $this->getUid());
    }

    /**
     * Gets a list of the titles of records referenced by the this record.
     *
     * @param string $foreignTable the name of the foreign table (must not be empty), having the uid and title fields
     * @param string $mmTable the name of the m:m table, having the uid_local, uid_foreign and sorting fields
     * @param int $uid
     *
     * @return string[] the titles of the referenced records
     */
    protected function getMmRecordTitlesByUid(string $foreignTable, string $mmTable, int $uid): array
    {
        $titles = [];
        foreach ($this->getMmRecordsByUid($foreignTable, $mmTable, $uid) as $row) {
            $titles[] = (string)$row['title'];
        }

        return $titles;
    }

    /**
     * Gets records referenced by the this record.
     *
     * @param string $foreignTable the name of the foreign table (must not be empty), having the uid and title fields
     * @param string $mmTable the name of the m:m table, having the uid_local, uid_foreign and sorting fields
     *
     * @return array[]
     */
    protected function getMmRecords(string $foreignTable, string $mmTable): array
    {
        return $this->getMmRecordsByUid($foreignTable, $mmTable, $this->getUid());
    }

    /**
     * Gets records referenced by the the record with the given UID.
     *
     * @param string $foreignTable the name of the foreign table (must not be empty), having the uid and title fields
     * @param string $mmTable the name of the m:m table, having the uid_local, uid_foreign and sorting fields
     * @param int $uid
     *
     * @return array[]
     */
    protected function getMmRecordsByUid(string $foreignTable, string $mmTable, int $uid): array
    {
        $query = self::getQueryBuilderForTable($foreignTable);
        return $query
            ->select($foreignTable . '.*')
            ->from($foreignTable)
            ->join(
                $foreignTable,
                $mmTable,
                'mm',
                $query->expr()->eq('mm.uid_foreign', $query->quoteIdentifier($foreignTable . '.uid'))
            )
            ->where($query->expr()->eq('mm.uid_local', $query->createNamedParameter($uid)))
            ->orderBy('mm.sorting')
            ->execute()->fetchAll();
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

    protected static function getConnectionForTable(string $table): Connection
    {
        return self::getConnectionPool()->getConnectionForTable($table);
    }

    private static function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
