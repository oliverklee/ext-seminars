<?php

/**
 * This builder class creates customized category bag objects.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_BagBuilder_Category extends Tx_Seminars_BagBuilder_Abstract
{
    /**
     * @var string class name of the bag class that will be built
     */
    protected $bagClassName = Tx_Seminars_Bag_Category::class;

    /**
     * @var string the table name of the bag to build
     */
    protected $tableName = 'tx_seminars_categories';

    /**
     * @var string the sorting field
     */
    protected $orderBy = 'title';

    /**
     * @var string the UIDs of the current events as comma-separated list,
     *             will be set by limitToEvents
     */
    protected $eventUids = '';

    /**
     * Limits the bag to the categories of the events provided by the parameter
     * $eventUids.
     *
     * Example: The events with the provided UIDs reference categories 9 and 12.
     * So the bag will be limited to categories 9 and 12 (plus any additional
     * limits).
     *
     * @param string $eventUids
     *        comma-separated list of UID of the events to which the category selection should be limited, may be empty,
     *        all UIDs must be > 0
     *
     * @return void
     */
    public function limitToEvents($eventUids)
    {
        if ($eventUids == '') {
            return;
        }

        if (!preg_match('/^(\\d+,)*\\d+$/', $eventUids)
            || preg_match('/(^|,)0+(,|$)/', $eventUids)
        ) {
            throw new InvalidArgumentException('$eventUids must be a comma-separated list of positive integers.', 1333292640);
        }

        $this->whereClauseParts['event'] = 'EXISTS (' .
            'SELECT * FROM tx_seminars_seminars_categories_mm' .
            ' WHERE tx_seminars_seminars_categories_mm.uid_local IN(' .
            $eventUids . ') AND tx_seminars_seminars_categories_mm' .
            '.uid_foreign = tx_seminars_categories.uid)';

        $this->eventUids = $eventUids;
    }

    /**
     * Sets the values of additionalTables, whereClauseParts and orderBy for the
     * category bag.
     * These changes are made so that the categories are sorted by the relation
     * sorting set in the back end.
     *
     * Before this function can be called, limitToEvents has to be called.
     *
     * @return void
     */
    public function sortByRelationOrder()
    {
        if ($this->eventUids == '') {
            throw new BadMethodCallException(
                'The event UIDs were empty. This means limitToEvents has not been called. LimitToEvents has to be called ' .
                    'before calling this function.',
                1333292662
            );
        }

        $this->addAdditionalTableName('tx_seminars_seminars_categories_mm');
        $this->whereClauseParts['category'] = 'tx_seminars_categories.uid = ' .
            'tx_seminars_seminars_categories_mm.uid_foreign AND ' .
            'tx_seminars_seminars_categories_mm.uid_local IN (' .
            $this->eventUids . ')';
        $this->orderBy = 'tx_seminars_seminars_categories_mm.sorting ASC';
    }
}
