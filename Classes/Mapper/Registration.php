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
 * This class represents a mapper for registrations.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Mapper_Registration extends Tx_Oelib_DataMapper {
	/**
	 * @var string the name of the database table for this mapper
	 */
	protected $tableName = 'tx_seminars_attendances';

	/**
	 * @var string the model class name for this mapper, must not be empty
	 */
	protected $modelClassName = 'tx_seminars_Model_Registration';

	/**
	 * @var string[] the (possible) relations of the created models in the format DB column name => mapper name
	 */
	protected $relations = array(
		'seminar' => 'tx_seminars_Mapper_Event',
		'user' => 'tx_seminars_Mapper_FrontEndUser',
		'currency' => Tx_Oelib_Mapper_Currency::class,
		'method_of_payment' => Tx_Seminars_Mapper_PaymentMethod::class,
		'lodgings' => Tx_Seminars_Mapper_Lodging::class,
		'foods' => 'tx_seminars_Mapper_Food',
		'checkboxes' => Tx_Seminars_Mapper_Checkbox::class,
		'additional_persons' => Tx_Seminars_Mapper_FrontEndUser::class,
	);
}