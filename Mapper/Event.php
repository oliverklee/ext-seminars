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

/**
 * This class represents a mapper for events.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_Mapper_Event extends tx_oelib_DataMapper {
	/**
	 * @var string the name of the database table for this mapper
	 */
	protected $tableName = 'tx_seminars_seminars';

	/**
	 * @var string the model class name for this mapper, must not be empty
	 */
	protected $modelClassName = 'tx_seminars_Model_Event';

	/**
	 * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
	 */
	protected $relations = array(
		'topic' => 'tx_seminars_Mapper_Event',
		'categories' => 'tx_seminars_Mapper_Category',
		'event_type' => 'tx_seminars_Mapper_EventType',
		'timeslots' => 'tx_seminars_Mapper_TimeSlot',
		'place' => 'tx_seminars_Mapper_Place',
		'lodgings' => 'tx_seminars_Mapper_Lodging',
		'foods' => 'tx_seminars_Mapper_Food',
		'speakers' => 'tx_seminars_Mapper_Speaker',
		'partners' => 'tx_seminars_Mapper_Speaker',
		'tutors' => 'tx_seminars_Mapper_Speaker',
		'leaders' => 'tx_seminars_Mapper_Speaker',
		'payment_methods' => 'tx_seminars_Mapper_PaymentMethod',
		'organizers' => 'tx_seminars_Mapper_Organizer',
		'organizing_partners' => 'tx_seminars_Mapper_Organizer',
		'target_groups' => 'tx_seminars_Mapper_TargetGroup',
		'owner_feuser' => 'tx_oelib_Mapper_FrontEndUser',
		'vips' => 'tx_oelib_Mapper_FrontEndUser',
		'checkboxes' => 'tx_seminars_Mapper_Checkbox',
		'requirements' => 'tx_seminars_Mapper_Event',
		'dependencies' => 'tx_seminars_Mapper_Event',
		'registrations' => 'tx_seminars_Mapper_Registration',
	);

	/**
	 * Retrieves an event model with the publication hash provided.
	 *
	 * @param string $publicationHash
	 *        the publication hash to find the event for, must not be empty
	 *
	 * @return tx_seminars_Model_Event the event with the publication hash
	 *                                 provided, will be NULL if no event could
	 *                                 be found
	 */
	public function findByPublicationHash($publicationHash) {
		if ($publicationHash == '') {
			throw new InvalidArgumentException('The given publication hash was empty.', 1333292411);
		}

		try {
			/** @var tx_seminars_Model_Event $result */
			$result = $this->findSingleByWhereClause(array('publication_hash' => $publicationHash));
		} catch (tx_oelib_Exception_NotFound $exception) {
			$result = NULL;
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
	 * @return tx_oelib_List the found tx_seminars_Model_Event models, will be
	 *                       empty if there are no matches
	 */
	public function findAllByBeginDate($minimum, $maximum) {
		if ($minimum < 0) {
			throw new InvalidArgumentException('$minimum must be >= 0.');
		}
		if ($maximum <= 0) {
			throw new InvalidArgumentException('$maximum must be > 0.');
		}
		if ($minimum > $maximum) {
			throw new InvalidArgumentException('$minimum must be <= $maximum.');
		}

		return $this->findByWhereClause(
			'begin_date BETWEEN ' . $minimum . ' AND ' . $maximum
		);
	}

	/**
	 * Returns the next upcoming event.
	 *
	 * @return tx_seminars_Model_Event the next upcoming event
	 *
	 * @throws tx_oelib_Exception_NotFound
	 */
	public function findNextUpcoming() {
		$whereClause = $this->getUniversalWhereClause() . ' AND cancelled <> ' . tx_seminars_seminar::STATUS_CANCELED .
			' AND object_type <> ' . tx_seminars_Model_Event::TYPE_TOPIC . ' AND begin_date > ' . $GLOBALS['SIM_ACCESS_TIME'];

		try {
			$row = tx_oelib_db::selectSingle(
				$this->columns,
				$this->tableName,
				$whereClause,
				'',
				'begin_date ASC'
			);
		} catch (tx_oelib_Exception_EmptyQueryResult $exception) {
			throw new tx_oelib_Exception_NotFound();
		}

		return $this->getModel($row);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Mapper/Event.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Mapper/Event.php']);
}