<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\FrontEndUserMapper as OelibFrontEndUserMapper;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Model\Event;
use TYPO3\CMS\Core\Database\Connection;

/**
 * This class represents a mapper for events.
 *
 * @extends AbstractDataMapper<Event>
 */
class EventMapper extends AbstractDataMapper
{
    protected $tableName = 'tx_seminars_seminars';

    protected $modelClassName = Event::class;

    protected $relations = [
        'topic' => EventMapper::class,
        'categories' => CategoryMapper::class,
        'event_type' => EventTypeMapper::class,
        'place' => PlaceMapper::class,
        'lodgings' => LodgingMapper::class,
        'foods' => FoodMapper::class,
        'speakers' => SpeakerMapper::class,
        'partners' => SpeakerMapper::class,
        'tutors' => SpeakerMapper::class,
        'leaders' => SpeakerMapper::class,
        'payment_methods' => PaymentMethodMapper::class,
        'organizers' => OrganizerMapper::class,
        'organizing_partners' => OrganizerMapper::class,
        'target_groups' => TargetGroupMapper::class,
        'vips' => OelibFrontEndUserMapper::class,
        'checkboxes' => CheckboxMapper::class,
        'requirements' => EventMapper::class,
        'dependencies' => EventMapper::class,
        // @deprecated #1324 will be removed in seminars 6.0
        'registrations' => RegistrationMapper::class,
    ];

    /**
     * Retrieves all events that have a begin date of at least $minimum up to
     * $maximum.
     *
     * These boundaries are inclusive, i.e., events with a begin date of
     * exactly $minimum or $maximum will also be retrieved.
     *
     * @param int $minimum minimum begin date as a UNIX timestamp, must be >= 0
     * @param int $maximum maximum begin date as a UNIX timestamp, must be >= $minimum
     *
     * @return Collection<Event> the found event models, will be empty if there are no matches
     */
    public function findAllByBeginDate(int $minimum, int $maximum): Collection
    {
        if ($minimum < 0) {
            throw new \InvalidArgumentException('$minimum must be >= 0.', 9971424020);
        }
        if ($maximum <= 0) {
            throw new \InvalidArgumentException('$maximum must be > 0.', 6723294479);
        }
        if ($minimum > $maximum) {
            throw new \InvalidArgumentException('$minimum must be <= $maximum.', 3835793617);
        }

        $queryBuilder = $this->getQueryBuilderForTable($this->getTableName());
        $queryResult = $queryBuilder
            ->select('*')
            ->from($this->getTableName())
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->gte(
                        'begin_date',
                        $queryBuilder->createNamedParameter($minimum, Connection::PARAM_INT)
                    ),
                    $queryBuilder->expr()->lte(
                        'begin_date',
                        $queryBuilder->createNamedParameter($maximum, Connection::PARAM_INT)
                    )
                )
            )
            ->orderBy('begin_date')
            ->executeQuery();
        $rows = $queryResult->fetchAllAssociative();

        return $this->getListOfModels($rows);
    }

    /**
     * Finds events that have the status "planned" and that have the automatic status change enabled.
     *
     * @return Collection<Event>
     */
    public function findForAutomaticStatusChange(): Collection
    {
        $queryBuilder = $this->getQueryBuilderForTable($this->getTableName());
        $queryResult = $queryBuilder
            ->select('*')
            ->from($this->getTableName())
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq(
                        'cancelled',
                        $queryBuilder->createNamedParameter(EventInterface::STATUS_PLANNED, Connection::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        'automatic_confirmation_cancelation',
                        $queryBuilder->createNamedParameter(1, Connection::PARAM_INT)
                    )
                )
            )
            ->orderBy('uid')
            ->executeQuery();
        $rows = $queryResult->fetchAllAssociative();

        return $this->getListOfModels($rows);
    }

    /**
     * Finds events that have registrations that have been created after when the last registration digest email
     * has been sent. This will also find events that have registrations, but never got a registration email sent
     * so far.
     *
     * This method will only find complete events and dates, but no topics.
     *
     * @return Collection<Event>
     */
    public function findForRegistrationDigestEmail(): Collection
    {
        $whereClause = 'object_type <> ' . EventInterface::TYPE_EVENT_TOPIC .
            ' AND hidden = 0 AND deleted = 0 ' .
            ' AND EXISTS (' .
            'SELECT * FROM tx_seminars_attendances ' .
            'WHERE tx_seminars_attendances.deleted = 0 ' .
            ' AND tx_seminars_attendances.seminar = tx_seminars_seminars.uid' .
            ' AND tx_seminars_attendances.crdate > tx_seminars_seminars.date_of_last_registration_digest' .
            ')';

        $sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE ' . $whereClause . ' ORDER BY begin_date ASC';

        $connection = $this->getConnectionForTable($this->getTableName());
        $statement = $connection->prepare($sql);
        $rows = $statement->executeQuery()->fetchAllAssociative();

        return $this->getListOfModels($rows);
    }
}
