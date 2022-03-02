<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\UpgradeWizards;

use Doctrine\DBAL\DBALException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\ExpressionBuilder;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Base class for FAL upgrade wizards.
 *
 * Most of this code is copied from `sysext/install/Classes/Updates/BackendLayoutIconUpdateWizard.php`.
 */
abstract class AbstractFalUpgradeWizard implements UpgradeWizardInterface, ChattyInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * the folder where legacy uploaded images get stored
     *
     * @var non-empty-string
     */
    private const SOURCE_PATH = 'uploads/tx_seminars/';

    /**
     * @var non-empty-string
     */
    protected $identifier;

    /**
     * @var non-empty-string
     */
    protected $title;

    /**
     * @var non-empty-string
     */
    protected $description;

    /**
     * @var non-empty-string
     */
    protected $table;

    /**
     * @var non-empty-string
     */
    protected $fieldToMigrate;

    /**
     * target folder after migration, relative to fileadmin
     *
     * @var non-empty-string
     */
    protected $targetPath;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var ResourceStorage
     */
    private $storage;

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return array<int, class-string>
     */
    public function getPrerequisites(): array
    {
        return [DatabaseUpdatedPrerequisite::class];
    }

    public function updateNecessary(): bool
    {
        return !empty($this->getRecordsFromTable());
    }

    public function executeUpdate(): bool
    {
        $result = true;
        try {
            $storages = GeneralUtility::makeInstance(StorageRepository::class)->findAll();
            $this->storage = $storages[0];
            $records = $this->getRecordsFromTable();
            foreach ($records as $record) {
                $this->migrateField($record);
            }
        } catch (\Exception $e) {
            // If something goes wrong, migrateField() logs an error
            $result = false;
        }
        return $result;
    }

    /**
     * Get records from table where the field to migrate is not empty (NOT NULL and != '')
     * and also not numeric (which means that it is migrated)
     *
     * @return array<int, array<string, string|int>>
     *
     * @throws \RuntimeException
     */
    protected function getRecordsFromTable(): array
    {
        $queryBuilder = $this->getQueryBuilderForTable($this->table);
        $queryBuilder->getRestrictions()->removeAll();
        try {
            return $queryBuilder
                ->select('uid', 'pid', $this->fieldToMigrate)
                ->from($this->table)
                ->where(
                    $queryBuilder->expr()->isNotNull($this->fieldToMigrate),
                    $queryBuilder->expr()->neq(
                        $this->fieldToMigrate,
                        $queryBuilder->createNamedParameter('', \PDO::PARAM_STR)
                    ),
                    $queryBuilder->expr()->comparison(
                        'CAST(CAST(' . $queryBuilder->quoteIdentifier($this->fieldToMigrate) . ' AS DECIMAL) AS CHAR)',
                        ExpressionBuilder::NEQ,
                        'CAST(' . $queryBuilder->quoteIdentifier($this->fieldToMigrate) . ' AS CHAR)'
                    )
                )
                ->orderBy('uid')
                ->execute()
                ->fetchAll();
        } catch (DBALException $e) {
            throw new \RuntimeException(
                'Database query failed. Error was: ' . $e->getPrevious()->getMessage(),
                1633544906
            );
        }
    }

    /**
     * Migrates a single field.
     *
     * @param array<string, string|int> $row
     *
     * @throws \Exception
     */
    protected function migrateField(array $row): void
    {
        /** @var array<int, non-empty-string> $fieldItems */
        $fieldItems = GeneralUtility::trimExplode(',', $row[$this->fieldToMigrate], true);
        if (empty($fieldItems) || is_numeric($row[$this->fieldToMigrate])) {
            return;
        }
        $fileAdminDirectory = rtrim($GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'], '/') . '/';
        $i = 0;

        $storageUid = (int)$this->storage->getUid();
        foreach ($fieldItems as $item) {
            /** @var int|null $fileUid */
            $fileUid = null;
            $sourcePath = Environment::getPublicPath() . '/' . self::SOURCE_PATH . $item;
            $targetDirectory = Environment::getPublicPath() . '/' . $fileAdminDirectory . $this->targetPath;
            $targetPath = $targetDirectory . PathUtility::basenameDuringBootstrap($item);

            // maybe the file was already moved, so check if the original file still exists
            if (file_exists($sourcePath)) {
                if (!is_dir($targetDirectory)) {
                    GeneralUtility::mkdir_deep($targetDirectory);
                }

                // see if the file already exists in the storage
                $fileSha1 = sha1_file($sourcePath);

                $queryBuilder = $this->getQueryBuilderForTable('sys_file');
                $queryBuilder->getRestrictions()->removeAll();
                $existingFileRecord = $queryBuilder->select('uid')->from('sys_file')->where(
                    $queryBuilder->expr()->eq(
                        'sha1',
                        $queryBuilder->createNamedParameter($fileSha1, \PDO::PARAM_STR)
                    ),
                    $queryBuilder->expr()->eq(
                        'storage',
                        $queryBuilder->createNamedParameter($storageUid, \PDO::PARAM_INT)
                    )
                )->execute()->fetch();

                // the file exists, the file does not have to be moved again
                if (\is_array($existingFileRecord)) {
                    $fileUid = (int)$existingFileRecord['uid'];
                } else {
                    // just move the file (no duplicate)
                    \rename($sourcePath, $targetPath);
                }
            }

            if (!\is_int($fileUid)) {
                // get the File object if it hasn't been fetched before
                try {
                    // if the source file does not exist, we should just continue, but leave a message in the docs;
                    // ideally, the user would be informed after the update as well.
                    /** @var File $file */
                    $file = $this->storage->getFile($this->targetPath . $item);
                    $fileUid = $file->getUid();
                } catch (\InvalidArgumentException $e) {
                    // no file found, no reference can be set
                    $this->logger->notice(
                        'File "' . self::SOURCE_PATH . $item . '" does not exist. Reference was not migrated.',
                        [
                            'table' => $this->table,
                            'record' => $row,
                            'field' => $this->fieldToMigrate,
                        ]
                    );
                    $format = 'File "%s"" does not exist. Referencing field: %s.%d.%s. The reference was not migrated.';
                    $this->output->writeln(
                        sprintf(
                            $format,
                            self::SOURCE_PATH . $item,
                            $this->table,
                            $row['uid'],
                            $this->fieldToMigrate
                        )
                    );
                    continue;
                }
            }

            if ($fileUid > 0) {
                $fields = [
                    'fieldname' => $this->fieldToMigrate,
                    'table_local' => 'sys_file',
                    'pid' => $row['pid'],
                    'uid_foreign' => $row['uid'],
                    'uid_local' => $fileUid,
                    'tablenames' => $this->table,
                    'crdate' => time(),
                    'tstamp' => time(),
                    'sorting_foreign' => $i,
                ];

                $queryBuilder = $this->getQueryBuilderForTable('sys_file_reference');
                try {
                    $queryBuilder->insert('sys_file_reference')->values($fields)->execute();
                } catch (\Exception $e) {
                    $this->output->writeln($e->getMessage());
                    throw $e;
                }
                ++$i;
            }
        }

        // Update referencing table's original field to now contain the count of references,
        // but only if all new references could be set
        if ($i === \count($fieldItems)) {
            $queryBuilder = $this->getQueryBuilderForTable($this->table);
            $queryBuilder->update($this->table)->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($row['uid'], \PDO::PARAM_INT)
                )
            )->set($this->fieldToMigrate, (string)$i)->execute();
        }
    }

    private function getQueryBuilderForTable(string $table): QueryBuilder
    {
        return $this->getConnectionPool()->getQueryBuilderForTable($table);
    }

    private function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
