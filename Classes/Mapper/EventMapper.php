<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Mapper;

use Doctrine\DBAL\Driver\Connection;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\FrontEndUserMapper as OelibFrontEndUserMapper;
use OliverKlee\Seminars\Domain\Model\Event\EventInterface;
use OliverKlee\Seminars\Model\Event;

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
        'timeslots' => TimeSlotMapper::class,
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
        'owner_feuser' => OelibFrontEndUserMapper::class,
        'vips' => OelibFrontEndUserMapper::class,
        'checkboxes' => CheckboxMapper::class,
        'requirements' => EventMapper::class,
        'dependencies' => EventMapper::class,
        'registrations' => RegistrationMapper::class,
    ];

    /**
     * Retrieves an event model with the publication hash provided.
     */
    public function findByPublicationHash(string $publicationHash): ?Event
    {
        if ($publicationHash === '') {
            throw new \InvalidArgumentException('The given publication hash was empty.', 1333292411);
        }

        try {
            $result = $this->findSingleByWhereClause(['publication_hash' => $publicationHash]);
        } catch (NotFoundException $exception) {
            $result = null;
        }

        return $result;
    }

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
            throw new \InvalidArgumentException('$minimum must be >= 0.');
        }
        if ($maximum <= 0) {
            throw new \InvalidArgumentException('$maximum must be > 0.');
        }
        if ($minimum > $maximum) {
            throw new \InvalidArgumentException('$minimum must be <= $maximum.');
        }

        $queryBuilder = $this->getQueryBuilderForTable($this->getTableName());
        $rows = $queryBuilder
            ->select('*')
            ->from($this->getTableName())
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->gte(
                        'begin_date',
                        $queryBuilder->createNamedParameter($minimum, \PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->lte(
                        'begin_date',
                        $queryBuilder->createNamedParameter($maximum, \PDO::PARAM_INT)
                    )
                )
            )
            ->orderBy('begin_date')
            ->execute()
            ->fetchAll();

        return $this->getListOfModels($rows);
    }

    /**
     * Returns the next upcoming event.
     *
     * @return Event the next upcoming event
     *
     * @throws NotFoundException
     */
    public function findNextUpcoming(): Event
    {
        $columns = explode(',', $this->columns);
        $queryBuilder = $this->getQueryBuilderForTable($this->tableName);
        foreach ($columns as $column) {
            $queryBuilder->addSelect($column);
        }
        $row = $queryBuilder
            ->from($this->tableName)
            ->where(
                $queryBuilder->expr()->neq(
                    'cancelled',
                    $queryBuilder->createNamedParameter(Event::STATUS_CANCELED, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->neq(
                    'object_type',
                    $queryBuilder->createNamedParameter(EventInterface::TYPE_EVENT_TOPIC, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->gt(
                    'begin_date',
                    $queryBuilder->createNamedParameter($GLOBALS['SIM_ACCESS_TIME'], \PDO::PARAM_INT)
                )
            )
            ->orderBy('begin_date')
            ->execute()
            ->fetch();

        if ($row === false) {
            throw new NotFoundException('Not found.', 1574004668);
        }

        /** @var Event $next */
        $next = $this->getModel($row);

        return $next;
    }

    /**
     * Finds events that have the status "planned" and that have the automatic status change enabled.
     *
     * @return Collection<Event>
     */
    public function findForAutomaticStatusChange(): Collection
    {
        $queryBuilder = $this->getQueryBuilderForTable($this->getTableName());
        $rows = $queryBuilder
            ->select('*')
            ->from($this->getTableName())
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq(
                        'cancelled',
                        $queryBuilder->createNamedParameter(Event::STATUS_PLANNED, \PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        'automatic_confirmation_cancelation',
                        $queryBuilder->createNamedParameter(1, \PDO::PARAM_INT)
                    )
                )
            )
            ->orderBy('uid')
            ->execute()
            ->fetchAll();

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
        $whereClause = 'registrations <> 0 AND object_type <> ' . EventInterface::TYPE_EVENT_TOPIC .
            ' AND hidden = 0 AND deleted = 0 ' .
            ' AND EXISTS (' .
            'SELECT * FROM tx_seminars_attendances ' .
            'WHERE tx_seminars_attendances.deleted = 0 ' .
            ' AND tx_seminars_attendances.seminar = tx_seminars_seminars.uid' .
            ' AND tx_seminars_attendances.crdate > tx_seminars_seminars.date_of_last_registration_digest' .
            ')';

        $sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE ' . $whereClause . ' ORDER BY begin_date ASC';

        /** @var Connection $connection */
        $connection = $this->getConnectionForTable($this->getTableName());
        $statement = $connection->prepare($sql);
        $statement->execute();
        $rows = $statement->fetchAll();

        return $this->getListOfModels($rows);
    }
}
