<?php
/***************************************************************
* Copyright notice
*
* (c) 2009 Bernd Schönbach <bernd@oliverklee.de>
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');

/**
 * Class 'frontEndPublishEvent' for the 'seminars' extension.
 *
 * This class publishes events which are hidden through editing or creation in
 * the FE-editor.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_pi1_frontEndPublishEvent extends tx_oelib_templatehelper {
	/**
	 * @var string the prefix used for the piVars
	 */
	public $prefixId = 'tx_seminars_publication';

	/**
	 * @var string path to this script relative to the extension dir
	 */
	public $scriptRelPath = 'pi1/class.tx_seminars_pi1_frontEndPublishEvent.php';

	/**
	 * @var string the extension key
	 */
	public $extKey = 'seminars';

	/**
	 * Creates the HTML for the event publishing.
	 *
	 * This will just output a success or fail line for the event publishing
	 * page.
	 *
	 * @return string HTML code for the event publishing, will not be empty
	 */
	public function render() {
		try {
			$this->init(array());

			if (!isset($this->piVars['hash']) || ($this->piVars['hash'] == '')) {
				return $this->translate('message_publishingFailed');
			}

			$eventMapper = tx_oelib_ObjectFactory::make('tx_seminars_Mapper_Event');
			$event = $eventMapper->findByPublicationHash($this->piVars['hash']);

			if (($event !== null) && $event->isHidden()) {
				$event->markAsVisible();
				$event->purgePublicationHash();
				$eventMapper->save($event);
				$result = $this->translate('message_publishingSuccessful');
			} else {
				$result = $this->translate('message_publishingFailed');
			}
		} catch (Exception $exception) {
			$result = '<p style="border: 2px solid red; padding: 1em; ' .
				'font-weight: bold;">' . LF .
				htmlspecialchars($exception->getMessage()) . LF .
				'<br /><br />' . LF .
				nl2br(htmlspecialchars($exception->getTraceAsString())) . LF .
				'</p>' . LF;
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_frontEndPublishEvent.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_frontEndPublishEvent.php']);
}
?>