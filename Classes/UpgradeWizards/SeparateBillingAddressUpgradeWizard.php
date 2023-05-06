<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\UpgradeWizards;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Checks the "separate billing address" checkbox for all registrations that have a separate billing address.
 *
 * @deprecated will be removed in seminars 6.0
 */
class SeparateBillingAddressUpgradeWizard implements UpgradeWizardInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const TABLE_NAME_REGISTRATIONS = 'tx_seminars_attendances';

    public function getIdentifier(): string
    {
        return 'seminars_migrateSeparateBillingAddress';
    }

    public function getTitle(): string
    {
        return 'Marks the separate billing address in registrations';
    }

    public function getDescription(): string
    {
        return 'Checks the "separate billing address" checkbox for all registrations ' .
            'that have a separate billing address';
    }

    public function getPrerequisites(): array
    {
        return [DatabaseUpdatedPrerequisite::class];
    }

    public function updateNecessary(): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_NAME_REGISTRATIONS);

        $query = $queryBuilder
            ->count('*')
            ->from(self::TABLE_NAME_REGISTRATIONS)
            ->where(
                $queryBuilder->expr()->eq(
                    'separate_billing_address',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                )
            )
            ->andWhere(
                $queryBuilder->expr()->neq('city', $queryBuilder->createNamedParameter('', Connection::PARAM_STR))
            );

        if (\method_exists($query, 'executeQuery')) {
            $queryResult = $query->executeQuery();
        } else {
            $queryResult = $query->execute();
        }
        if (\method_exists($queryResult, 'fetchOne')) {
            $count = (int)$queryResult->fetchOne();
        } else {
            $count = (int)$queryResult->fetchColumn(0);
        }

        return $count > 0;
    }

    public function executeUpdate(): bool
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_NAME_REGISTRATIONS);

        $query = $queryBuilder
            ->update(self::TABLE_NAME_REGISTRATIONS)
            ->where(
                $queryBuilder->expr()->eq(
                    'separate_billing_address',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                )
            )
            ->andWhere(
                $queryBuilder->expr()->neq('city', $queryBuilder->createNamedParameter('', Connection::PARAM_STR))
            )
            ->set('separate_billing_address', '1');

        if (\method_exists($query, 'executeStatement')) {
            $query->executeStatement();
        } else {
            $query->execute();
        }

        if ($this->logger instanceof LoggerAwareInterface) {
            $this->logger->info(
                'The "separate billing address" checkbox has been checked for all registrations ' .
                'that have a separate billing address.'
            );
        }

        return true;
    }
}
