<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Oliver Klee (typo3-coding@oliverklee.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Class 'tx_seminars_registration' for the 'seminars' extension.
 * 
 * This class represents a registration/attendance.
 * It will hold the corresponding data and can commit that data to the DB.
 *
 * @author	Oliver Klee <typo-coding@oliverklee.de>
 */

class tx_seminars_registrationmanager {
	/** Same as class name */
	var $prefixId = 'tx_seminars_registration';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'class.tx_seminars_registration.php';

	/**
	 * The constructor.
	 *
	 * @access public
	 */
	function tx_seminars_registration() {
		// stub
	}
	
	/**
	 * Get our title, containing:
	 *  the attendee's full name,
	 *  the seminar title
	 *  the seminar date 
	 * 
	 * @return	string	the attendance title
	 * 
	 * @access public
	 */
	function getTitle() {
		// stub
	}
	
	/**
	 * Get the attendee's uid.
	 * 
	 * @return	integer	the attendee's feuser uid
	 * 
	 * @access public
	 */
	function getUser() {
		// stub
	}
	
	/**
	 * Get the seminar's uid.
	 * 
	 * @return	integer	the seminar's uid
	 * 
	 * @access public
	 */
	function getSeminar() {
		// stub
	}
	
	/**
	 * Get whether this attendance has already been paid for.
	 * 
	 * @return	boolean	whether this attendance has already been paid for
	 * 
	 * @access public
	 */
	function getIsPaid() {
		// stub
	}
	
	/**
	 * Get the date at which the user has paid for this attendance.
	 * 
	 * @return	integer	the date at which the user has paid for this attendance
	 * 
	 * @access public
	 */
	function getDatePaid() {
		// stub
	}
	
	/**
	 * Get the method of payment.
	 * 
	 * @return	integer	the uid of the method of payment (may be 0 if none is given)
	 * 
	 * @access public
	 */
	function getMethodOfPayment() {
		// stub
	}
	
	/**
	 * Get whether the attendee has been at the seminar.
	 * 
	 * @return	boolean	whether the attendee has attended the seminar
	 * 
	 * @access public
	 */
	function getHasBeenThere() {
		// stub
	}
	
	/**
	 * Get the attendee's special interests in the subject.
	 * 
	 * @return	string	a description of the attendee's special interests (may be empty)
	 * 
	 * @access public
	 */
	function getInterests() {
		// stub
	}
	
	/**
	 * Get the attendee's expectations for the seminar.
	 * 
	 * @return	string	a description of the attendee's expectations for the seminar (may be empty)
	 * 
	 * @access public
	 */
	function getExpectations() {
		// stub
	}
	
	/**
	 * Get the attendee's background knowledge on the subject.
	 * 
	 * @return	string	a description of the attendee's background knowledge (may be empty)
	 * 
	 * @access public
	 */
	function getKnowledge() {
		// stub
	}
	
	/**
	 * Get where the attendee has heard about this seminar.
	 * 
	 * @return	string	a description of where the attendee has heard about this seminar (may be empty)
	 * 
	 * @access public
	 */
	function getKnownForm() {
		// stub
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registration.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_registration.php']);
}
