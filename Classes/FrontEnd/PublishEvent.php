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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class publishes events which are hidden through editing or creation in the FE-editor.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_FrontEnd_PublishEvent extends tx_oelib_templatehelper {
	/**
	 * @var int
	 */
	const PUBLICATION_TYPE_NUMBER = 737;

	/**
	 * @var string the prefix used for the piVars
	 */
	public $prefixId = 'tx_seminars_publication';

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
		$this->init(array());

		if (!isset($this->piVars['hash']) || ($this->piVars['hash'] == '')) {
			return $this->translate('message_publishingFailed');
		}

		/** @var tx_seminars_Mapper_Event $eventMapper */
		$eventMapper = GeneralUtility::makeInstance('tx_seminars_Mapper_Event');
		/** @var tx_seminars_Model_Event $event */
		$event = $eventMapper->findByPublicationHash($this->piVars['hash']);

		if (($event !== NULL) && $event->isHidden()) {
			$event->markAsVisible();
			$event->purgePublicationHash();
			$eventMapper->save($event);
			$result = $this->translate('message_publishingSuccessful');
		} else {
			$result = $this->translate('message_publishingFailed');
		}

		return $result;
	}
}