<?php

declare(strict_types=1);

use OliverKlee\Oelib\DataStructures\Collection;

/**
 * This class represents a mapper for events.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_Mapper_Event extends \Tx_Oelib_DataMapper
{
    /**
     * @var string the name of the database table for this mapper
     */
    protected $tableName = 'tx_seminars_seminars';

    /**
     * @var string the model class name for this mapper, must not be empty
     */
    protected $modelClassName = \Tx_Seminars_Model_Event::class;

    /**
     * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
     */
    protected $relations = [
        'topic' => \Tx_Seminars_Mapper_Event::class,
        'categories' => \Tx_Seminars_Mapper_Category::class,
        'event_type' => \Tx_Seminars_Mapper_EventType::class,
        'timeslots' => \Tx_Seminars_Mapper_TimeSlot::class,
        'place' => \Tx_Seminars_Mapper_Place::class,
        'lodgings' => \Tx_Seminars_Mapper_Lodging::class,
        'foods' => \Tx_Seminars_Mapper_Food::class,
        'speakers' => \Tx_Seminars_Mapper_Speaker::class,
        'partners' => \Tx_Seminars_Mapper_Speaker::class,
        'tutors' => \Tx_Seminars_Mapper_Speaker::class,
        'leaders' => \Tx_Seminars_Mapper_Speaker::class,
        'payment_methods' => \Tx_Seminars_Mapper_PaymentMethod::class,
        'organizers' => \Tx_Seminars_Mapper_Organizer::class,
        'organizing_partners' => \Tx_Seminars_Mapper_Organizer::class,
        'target_groups' => \Tx_Seminars_Mapper_TargetGroup::class,
        'owner_feuser' => \Tx_Oelib_Mapper_FrontEndUser::class,
        'vips' => \Tx_Oelib_Mapper_FrontEndUser::class,
        'checkboxes' => \Tx_Seminars_Mapper_Checkbox::class,
        'requirements' => \Tx_Seminars_Mapper_Event::class,
        'dependencies' => \Tx_Seminars_Mapper_Event::class,
        'registrations' => \Tx_Seminars_Mapper_Registration::class,
    ];

    /**
     * Retrieves an event model with the publication hash provided.
     *
     * @param string $publicationHash
     *        the publication hash to find the event for, must not be empty
     *
     * @return \Tx_Seminars_Model_Event|null
     */
    public function findByPublicationHash(string $publicationHash)
    {
        if ($publicationHash === '') {
            throw new \InvalidArgumentException('The given publication hash was empty.', 1333292411);
        }

        try {
            /** @var \Tx_Seminars_Model_Event $result */
            $result = $this->findSingleByWhereClause(['publication_hash' => $publicationHash]);
        } catch (\Tx_Oelib_Exception_NotFound $exception) {
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
     * @param int $minimum
     *        minimum begin date as a UNIX timestamp, must be >= 0
     * @param int $maximum
     *        maximum begin date as a UNIX timestamp, must be >= $minimum
     *
     * @return Collection the found \Tx_Seminars_Model_Event models, will be empty if there are no matches
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

        return $this->findByWhereClause(
            'begin_date BETWEEN ' . $minimum . ' AND ' . $maximum
        );
    }

    /**
     * Returns the next upcoming event.
     *
     * @return \Tx_Seminars_Model_Event the next upcoming event
     *
     * @throws \Tx_Oelib_Exception_NotFound
     */
    public function findNextUpcoming(): \Tx_Seminars_Model_Event
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
                    $queryBuilder->createNamedParameter(\Tx_Seminars_Model_Event::STATUS_CANCELED, \PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->neq(
                    'object_type',
                    $queryBuilder->createNamedParameter(\Tx_Seminars_Model_Event::TYPE_TOPIC, \PDO::PARAM_INT)
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
            throw new \Tx_Oelib_Exception_NotFound('Not found.', 1574004668);
        }

        /** @var \Tx_Seminars_Model_Event $next */
        $next = $this->getModel($row);

        return $next;
    }

    /**
     * Finds events that have the status "planned" and that have the automatic status change enabled.
     *
     * @return Collection the Collection<Tx_Seminars_Model_Event>
     */
    public function findForAutomaticStatusChange(): Collection
    {
        $whereClause = 'cancelled = ' . \Tx_Seminars_Model_Event::STATUS_PLANNED . ' AND automatic_confirmation_cancelation = 1';

        return $this->findByWhereClause($whereClause);
    }

    /**
     * Finds events that have registrations that have been created after when the last registration digest email
     * has been sent. This will also find events that have registrations, but never got a registration email sent
     * so far.
     *
     * This method will only find complete events and dates, but no topics.
     *
     * @return Collection the Collection<Tx_Seminars_Model_Event>
     */
    public function findForRegistrationDigestEmail(): Collection
    {
        $whereClause = 'registrations <> 0' .
            ' AND object_type <> ' . \Tx_Seminars_Model_Event::TYPE_TOPIC .
            ' AND EXISTS (' .
            'SELECT * FROM tx_seminars_attendances ' .
            'WHERE tx_seminars_attendances.deleted = 0 ' .
            ' AND tx_seminars_attendances.seminar = tx_seminars_seminars.uid' .
            ' AND tx_seminars_attendances.crdate > tx_seminars_seminars.date_of_last_registration_digest' .
            ')';

        return $this->findByWhereClause($whereClause, 'begin_date ASC');
    }
}
