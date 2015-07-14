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
class tx_seminars_Mapper_Registration extends tx_oelib_DataMapper {
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
		'currency' => 'tx_oelib_Mapper_Currency',
		'method_of_payment' => 'tx_seminars_Mapper_PaymentMethod',
		'lodgings' => 'tx_seminars_Mapper_Lodging',
		'foods' => 'tx_seminars_Mapper_Food',
		'checkboxes' => 'tx_seminars_Mapper_Checkbox',
		'additional_persons' => 'tx_seminars_Mapper_FrontEndUser',
	);
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Mapper/Registration.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Mapper/Registration.php']);
}