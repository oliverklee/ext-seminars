<?php
/***************************************************************
* Copyright notice
*
* (c) 2006-2009 Oliver Klee (typo3-coding@oliverklee.de)
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

require_once(PATH_t3lib . 'class.t3lib_basicfilefunc.php');

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');
include_once(t3lib_extMgm::extPath('seminars') . 'tx_seminars_modifiedSystemTables.php');

/**
 * Class 'tx_seminars_pi1_eventEditor' for the 'seminars' extension.
 *
 * This class is a controller which allows to create and edit events on the FE.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_pi1_eventEditor extends tx_seminars_pi1_frontEndEditor {
	/**
	 * @var string path to this script relative to the extension directory
	 */
	public $scriptRelPath = 'pi1/class.tx_seminars_pi1_eventEditor.php';

	/**
	 * @var string stores a validation error message if there was one
	 */
	private $validationError = '';

	/**
	 * @var array currently attached files
	 */
	private $attachedFiles = array();

	/**
	 * @var string the prefix used for every subpart in the FE editor
	 */
	const SUBPART_PREFIX = 'fe_editor';

	/**
	 * @var array the fields required to file a new event.
	 */
	private $requiredFormFields = array();

	/**
	 * @var string the publication hash for the event to edit/create
	 */
	private $publicationHash = '';

	/**
	 * The constructor.
	 *
	 * After the constructor has been called, hasAccessMessage() must be called
	 * to ensure that the logged-in user is allowed to edit a given seminar.
	 *
	 * @param array TypoScript configuration for the plugin
	 * @param tslib_cObj the parent cObj content, needed for the flexforms
	 */
	public function __construct(array $configuration, tslib_cObj $cObj) {
		parent::__construct($configuration, $cObj);
		$this->setRequiredFormFields();
	}

	/**
	 * Stores the currently attached files.
	 *
	 * Attached files are stored in a member variable and added to the form data
	 * afterwards, as the FORMidable renderlet is not usable for this.
	 */
	private function storeAttachedFiles() {
		if (!$this->isTestMode()) {
			$this->attachedFiles = t3lib_div::trimExplode(
				',',
				$this->getFormCreator()->oDataHandler
					->__aStoredData['attached_files'],
				true
			);
		} else {
			$this->attachedFiles = array();
		}
	}

	/**
	 * Declares the additional data handler for m:n relations.
	 */
	private function declareDataHandler() {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_formidable']
			['declaredobjects']['datahandlers']['DBMM'] = array(
				'key' => 'dh_dbmm', 'base' => true
			);
	}

	/**
	 * Creates the HTML output.
	 *
	 * @return string HTML of the create/edit form
	 */
	public function render() {
		$this->setFormConfiguration($this->conf['form.']['eventEditor.']);
		$this->declareDataHandler();

		$this->storeAttachedFiles();

		$template = tx_oelib_ObjectFactory::make('tx_oelib_Template');
		$template->processTemplate(parent::render());

		$template->hideSubpartsArray(
			$this->getHiddenSubparts(), self::SUBPART_PREFIX
		);

		$this->setRequiredFieldLabels($template);

		// The redirect to the FE editor with the current record loaded can
		// only work with the record's UID, but new records do not have a UID
		// before they are saved.
		if ($this->getObjectUid() == 0) {
			$template->hideSubparts('submit_and_stay');
		}

		return $this->getHtmlWithAttachedFilesList($template);
	}

	/**
	 * Returns the complete HTML for the FE editor.
	 *
	 * As FORMidable does not provide any formatting for the list of
	 * attachments and saves the list with the first letter snipped, we provide
	 * our own formatted list to ensure correctly displayed attachments, even if
	 * there was a validation error.
	 *
	 * @param tx_oelib_Template holds the raw HTML output, must be already
	 *                          processed by FORMidable
	 *
	 * @return string HTML for the FE editor with the formatted attachment
	 *                list if there are attached files, will not be empty
	 */
	private function getHtmlWithAttachedFilesList(tx_oelib_Template $template) {
		foreach (array(
			'label_delete', 'label_really_delete', 'label_save',
			'label_save_and_back',
		) as $label) {
			$template->setMarker($label, $this->translate($label));
		}

		$originalAttachmentList = $this->getFormCreator()->oDataHandler->oForm
			->aORenderlets['attached_files']->mForcedValue;

		if (($originalAttachmentList != '') && !empty($this->attachedFiles)) {
			$attachmentList = '';
			$fileNumber = 1;
			foreach ($this->attachedFiles as $fileName) {
				$template->setMarker('file_name', $fileName);
				$template->setMarker(
					'single_attached_file_id', 'attached_file_' . $fileNumber
				);
				$fileNumber++;
				$attachmentList .= $template->getSubpart('SINGLE_ATTACHED_FILE');
			}
			$template->setSubpart('single_attached_file', $attachmentList);
		} else {
			$template->hideSubparts('attached_files');
		}

		$result = $template->getSubpart();

		// Removes FORMidable's original attachment list from the result.
		if ($originalAttachmentList != '') {
			$result = str_replace($originalAttachmentList . '<br />', '', $result);
		}

		return $result;
	}

	/**
	 * Provides data items for the list of available categories.
	 *
	 * @param array any pre-filled data (may be empty)
	 *
	 * @return array $items with additional items from the categories
	 *               table as an array with the keys "caption" (for the
	 *               title) and "value" (for the UID)
	 */
	public function populateListCategories(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_CATEGORIES);
	}

	/**
	 * Provides data items for the list of available event types.
	 *
	 * @param array any pre-filled data (may be empty)
	 *
	 * @return array $items with additional items from the event_types
	 *               table as an array with the keys "caption" (for the
	 *               title) and "value" (for the UID)
	 */
	public function populateListEventTypes(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_EVENT_TYPES);
	}

	/**
	 * Provides data items for the list of available lodgings.
	 *
	 * @param array any pre-filled data (may be empty)
	 *
	 * @return array $items with additional items from the lodgings table
	 *               as an array with the keys "caption" (for the title)
	 *               and "value" (for the UID)
	 */
	public function populateListLodgings(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_LODGINGS);
	}

	/**
	 * Provides data items for the list of available foods.
	 *
	 * @param array any pre-filled data (may be empty)
	 *
	 * @return array $items with additional items from the foods table
	 *               as an array with the keys "caption" (for the title)
	 *               and "value" (for the UID)
	 */
	public function populateListFoods(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_FOODS);
	}

	/**
	 * Provides data items for the list of available payment methods.
	 *
	 * @param array any pre-filled data (may be empty)
	 *
	 * @return array $items with additional items from payment methods
	 *               table as an array with the keys "caption" (for the
	 *               title) and "value" (for the UID)
	 */
	public function populateListPaymentMethods(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_PAYMENT_METHODS);
	}

	/**
	 * Provides data items for the list of available organizers.
	 *
	 * @param array any pre-filled data (may be empty)
	 *
	 * @return array $items with additional items from the organizers
	 *               table as an array with the keys "caption" (for the
	 *               title) and "value" (for the UID)
	 */
	public function populateListOrganizers(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_ORGANIZERS);
	}

	/**
	 * Provides data items for the list of available places.
	 *
	 * @param array any pre-filled data (may be empty)
	 *
	 * @return array $items with additional items from the places table
	 *               as an array with the keys "caption" (for the title)
	 *               and "value" (for the UID)
	 */
	public function populateListPlaces(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_SITES);
	}

	/**
	 * Provides data items for the list of available speakers.
	 *
	 * @param array any pre-filled data (may be empty)
	 *
	 * @return array $items with additional items from the speakers table
	 *               as an array with the keys "caption" (for the title)
	 *               and "value" (for the UID)
	 */
	public function populateListSpeakers(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_SPEAKERS);
	}

	/**
	 * Provides data items for the list of available checkboxes.
	 *
	 * @param array any pre-filled data (may be empty)
	 *
	 * @return array $items with additional items from the checkboxes
	 *               table as an array with the keys "caption" (for the
	 *               title) and "value" (for the UID)
	 */
	public function populateListCheckboxes(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_CHECKBOXES);
	}

	/**
	 * Provides data items for the list of available target groups.
	 *
	 * @param array any pre-filled data (may be empty)
	 *
	 * @return array $items with additional items from the target groups
	 *               table as an array with the keys "caption" (for the
	 *               title) and "value" (for the UID)
	 */
	public function populateListTargetGroups(array $items) {
		return $this->populateList($items, SEMINARS_TABLE_TARGET_GROUPS);
	}

	/**
	 * Provides data items from the DB.
	 *
	 * By default, the field "title" is used as the name that will be returned
	 * within the array (as caption). For FE users, the field "name" is used.
	 *
	 * This method overrides the method in tx_seminars_pi1_frontEndEditor and
	 * only returns the records where the currently logged in front-end user
	 * is the owner or where no owner is specified.
	 *
	 * @param array $items array that contains any pre-filled data, may be empty
	 * @param string $tableName the table name to query, must not be empty
	 * @param string $queryParameters query parameter that will be used as the
	 *                                WHERE clause, must not be empty
	 * @param boolean $appendBreak whether to append a <br /> at the end of each
	 *                             caption
	 *
	 * @return array $items with additional items from the $params['what']
	 *               table as an array with the keys "caption" (for the title)
	 *               and "value" (for the UID), might be empty
	 */
	public function populateList(
		array $items, $tableName, $queryParameters = '1 = 1', $appendBreak = false
	) {
		$frontEndUser = tx_oelib_FrontEndLoginManager::
			getInstance()->getLoggedInUser('tx_seminars_Mapper_FrontEndUser');

		if (tx_oelib_db::tableHasColumn($tableName, 'owner')) {
			$additionalQueryParameters
				= ' AND (owner = ' . $frontEndUser->getUid() . ' OR owner = 0)';
		} else {
			$additionalQueryParameters = '';
		}

		return parent::populateList(
			$items,
			$tableName,
			$queryParameters . $additionalQueryParameters,
			$appendBreak
		);
	}

	/**
	 * Gets the URL of the page that should be displayed when an event has been
	 * successfully created.
	 * An URL of the FE editor's page is returned if "submit_and_stay" was
	 * clicked.
	 *
	 * @return string complete URL of the FE page with a message or, if
	 *                "submit_and_stay" was clicked, of the current page
	 */
	public function getEventSuccessfullySavedUrl() {
		$additionalParameters = '';

		if ($this->getFormValue('proceed_file_upload')) {
			$additionalParameters = t3lib_div::implodeArrayForUrl(
				$this->prefixId,
				array('seminar' => $this->getObjectUid())
			);
			$pageId =  $GLOBALS['TSFE']->id;
		} else {
			$pageId =  $this->getConfValueInteger(
				'eventSuccessfullySavedPID', 's_fe_editing'
			);
		}

		return t3lib_div::locationHeaderUrl(
			$this->cObj->typoLink_URL(array(
				'parameter' => $pageId,
				'additionalParams' => $additionalParameters,
			))
		);
	}

	/**
	 * Checks whether the currently logged-in FE user (if any) belongs to the
	 * FE group that is allowed to enter and edit event records in the FE.
	 * This group can be set using plugin.tx_seminars.eventEditorFeGroupID.
	 *
	 * It also is checked whether that event record exists and the logged-in
	 * FE user is the owner or is editing a new record.
	 *
	 * @return string locallang key of an error message, will be an empty
	 *                string if access was granted
	 */
	private function checkAccess() {
		if (!tx_oelib_FrontEndLoginManager::getInstance()->isLoggedIn()) {
			return 'message_notLoggedIn';
		}

		if (($this->getObjectUid() > 0)
			&& !tx_seminars_objectfromdb::recordExists(
				$this->getObjectUid(), SEMINARS_TABLE_SEMINARS, true
			)
		) {
			return 'message_wrongSeminarNumber';
		}

		if ($this->getObjectUid() > 0) {
			$seminar = tx_oelib_ObjectFactory::make(
				'tx_seminars_seminar', $this->getObjectUid(), false, true
			);
			$isUserVip = $seminar->isUserVip(
				$this->getFeUserUid(),
				$this->getConfValueInteger('defaultEventVipsFeGroupID')
			);
			$isUserOwner = $seminar->isOwnerFeUser();
			$seminar->__destruct();
			unset($seminar);
			$mayManagersEditTheirEvents = $this->getConfValueBoolean(
				'mayManagersEditTheirEvents', 's_listView'
			);

			$hasAccess = $isUserOwner
				|| ($mayManagersEditTheirEvents && $isUserVip);
		} else {
			$eventEditorGroupUid = $this->getConfValueInteger(
				'eventEditorFeGroupID', 's_fe_editing'
			);
			$hasAccess = ($eventEditorGroupUid != 0)
				&& tx_oelib_FrontEndLoginManager::getInstance()
					->getLoggedInUser()->hasGroupMembership($eventEditorGroupUid);
		}

		return ($hasAccess ? '' : 'message_noAccessToEventEditor');
	}

	/**
	 * Checks whether the currently logged-in FE user (if any) belongs to the
	 * FE group that is allowed to enter and edit event records in the FE.
	 * This group can be set using plugin.tx_seminars.eventEditorFeGroupID.
	 * If the FE user does not have the necessary permissions, a localized error
	 * message will be returned.
	 *
	 * @return string an empty string if a user is logged in and allowed
	 *                to enter and edit events, a localized error message
	 *                otherwise
	 */
	public function hasAccessMessage() {
		$result = '';
		$errorMessage = $this->checkAccess();

		if ($errorMessage != '') {
			$this->setMarker('error_text', $this->translate($errorMessage));
			$result = $this->getSubpart('ERROR_VIEW');
		}

		return $result;
	}

	/**
	 * Changes all potential decimal separators (commas and dots) in price
	 * fields to dots.
	 *
	 * @param array all entered form data with the field names as keys,
	 *              will be modified, must not be empty
	 */
	private function unifyDecimalSeparators(array &$formData) {
		$priceFields = array(
			'price_regular', 'price_regular_early', 'price_regular_board',
			'price_special', 'price_special_early', 'price_special_board',
		);

		foreach ($priceFields as $key) {
			if (isset($formData[$key])) {
				$formData[$key]
					= str_replace(',', '.', $formData[$key]);
			}
		}
	}

	/**
	 * Processes the deletion of attached files and sets the form value for
	 * "attached_files" to the locally stored value for this field.
	 *
	 * This is done because when FORMidable processes the upload renderlet,
	 * the first character of the string might get lost. In addition, with
	 * FORMidable, it is possible to store the name of an invalid file in the
	 * list of attachments.
	 *
	 * @param array form data, will be modified, must not be empty
	 */
	private function processAttachments(array &$formData) {
		$filesToDelete = t3lib_div::trimExplode(
			',', $formData['delete_attached_files'], true
		);

		foreach ($filesToDelete as $fileName) {
			// saves other files in the upload folder from being deleted
			if (in_array($fileName, $this->attachedFiles)) {
				$this->purgeUploadedFile($fileName);
			}
		}

		$formData['attached_files'] = implode(',', $this->attachedFiles);
	}

	/**
	 * Removes all form data elements that are no fields in the seminars table.
	 *
	 * @param array form data, will be modified, must not be empty
	 */
	private function purgeNonSeminarsFields(array &$formData) {
		unset(
			$formData['proceed_file_upload'],
			$formData['delete_attached_files'],
			$formData['newPlace_title'],
			$formData['newPlace_address'],
			$formData['newPlace_city'],
			$formData['newPlace_country'],
			$formData['newPlace_homepage'],
			$formData['newPlace_directions'],
			$formData['newPlace_notes'],

			$formData['newSpeaker_title'],
			$formData['newSpeaker_gender'],
			$formData['newSpeaker_organization'],
			$formData['newSpeaker_homepage'],
			$formData['newSpeaker_description'],
			$formData['newSpeaker_skills'],
			$formData['newSpeaker_notes'],
			$formData['newSpeaker_address'],
			$formData['newSpeaker_phone_work'],
			$formData['newSpeaker_phone_home'],
			$formData['newSpeaker_phone_mobile'],
			$formData['newSpeaker_fax'],
			$formData['newSpeaker_email'],
			$formData['newSpeaker_cancelation_period']
		);
	}

	/**
	 * Adds some values to the form data before insertion into the database.
	 * Added values for new objects are: 'crdate', 'tstamp', 'pid' and
	 * 'owner_feuser'.
	 * For objects to update, just the 'tstamp' will be refreshed.
	 *
	 * @param array form data, will be modified, must not be empty
	 */
	private function addAdministrativeData(array &$formData) {
		$formData['tstamp'] = $GLOBALS['SIM_EXEC_TIME'];

		// Updating the timestamp is sufficent for existing records.
		if ($this->iEdition) {
			return;
		}

		$formData['crdate'] = $GLOBALS['SIM_EXEC_TIME'];
		$formData['owner_feuser'] = $this->getFeUserUid();
		$eventPid = tx_oelib_FrontEndLoginManager::getInstance()->getLoggedInUser(
			'tx_seminars_Mapper_FrontEndUser')->getEventRecordsPid();
		$formData['pid'] = ($eventPid > 0)
			? $eventPid
			: $this->getConfValueInteger('createEventsPID', 's_fe_editing');
	}

	/**
	 * Checks the publish settings of the user and hides the event record if
	 * necessary.
	 *
	 * @param array form data, will be modified if the seminar must be hidden
	 *              corresponding to the publish settings of the user, must not
	 *              be empty
	 */
	private function checkPublishSettings(array &$formData) {
		$publishSetting	= tx_oelib_FrontEndLoginManager::getInstance()
			->getLoggedInUser('tx_seminars_Mapper_FrontEndUser')
				->getPublishSetting();
		$eventUid = $this->getObjectUid();
		$isNew = ($eventUid == 0);

		$hideEditedObject = !$isNew
			&& ($publishSetting
				== tx_seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED
			);
		$hideNewObject = $isNew
			&& ($publishSetting
				> tx_seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY
			);

		$eventIsHidden = !$isNew
			? tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
				->find($eventUid)->isHidden()
			: false;

		if (($hideEditedObject || $hideNewObject) && !$eventIsHidden) {
			$formData['hidden'] = 1;
			$formData['publication_hash'] = uniqid('', true);
			$this->publicationHash = $formData['publication_hash'];
		} else {
			$this->publicationHash = '';
		}
	}

	/**
	 * Unifies decimal separators, processes the deletion of attachments and
	 * purges non-seminars-fields.
	 *
	 * @see unifyDecimalSeparators(), processAttachments(),
	 *      purgeNonSeminarsFields(), addAdministrativeData()
	 *
	 * @param array form data, must not be empty
	 *
	 * @return array modified form data, will not be empty
	 */
	public function modifyDataToInsert(array $formData) {
		$modifiedFormData = $formData;

		$this->processAttachments($modifiedFormData);
		$this->purgeNonSeminarsFields($modifiedFormData);
		$this->unifyDecimalSeparators($modifiedFormData);
		$this->addAdministrativeData($modifiedFormData);
		$this->checkPublishSettings($modifiedFormData);

		return $modifiedFormData;
	}

	/**
	 * Checks whether the provided file is of an allowed type and size. If it
	 * is, it is appended to the list of already attached files. If not, the
	 * file deleted becomes from the upload directory and the validation error
	 * is stored in $this->validationError.
	 *
	 * This check is done here because the FORMidable validators do not allow
	 * multiple error messages.
	 *
	 * @param array form data to check, must not be empty
	 *
	 * @return boolean true if the provided file is valid, false otherwise
	 */
	public function checkFile(array $valueToCheck) {
		$this->validationError = '';

		// If these values match, no files have been uploaded and we need no
		// further check.
		if ($valueToCheck['value'] == implode(',', $this->attachedFiles)) {
			return true;
		}

		$fileToCheck = array_pop(
			t3lib_div::trimExplode(',', $valueToCheck['value'], true)
		);

		$this->checkFileSize($fileToCheck);
		$this->checkFileType($fileToCheck);

		// If there is a validation error, the upload has to be done again.
		if (($this->validationError == '')
			&& ($this->isTestMode
				|| $this->getFormCreator()->oDataHandler->_allIsValid()
			)
		) {
			array_push($this->attachedFiles, $fileToCheck);
		} else {
			$this->purgeUploadedFile($fileToCheck);
		}

		return ($this->validationError == '');
	}

	/**
	 * Checks whether an uploaded file is of a valid type.
	 *
	 * @param string file name, must match an uploaded file, must not be empty
	 */
	private function checkFileType($fileName) {
		$allowedExtensions = $this->getConfValueString(
			'allowedExtensionsForUpload', 's_fe_editing'
		);

		if (!preg_match(
			'/^.+\.(' . str_replace(',', '|', $allowedExtensions) . ')$/i',
			$fileName
		)) {
			$this->validationError
				= $this->translate('message_invalid_type') .
					' ' . str_replace(',', ', ', $allowedExtensions) . '.';
		}
	}

	/**
	 * Checks whether an uploaded file is not too large.
	 *
	 * @param string file name, must match an uploaded file, must not be empty
	 */
	private function checkFileSize($fileName) {
		$maximumFileSize = $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'];
		$fileInformation = t3lib_div::makeInstance('t3lib_basicFileFunctions')
			->getTotalFileInfo(PATH_site . 'uploads/tx_seminars/' . $fileName);

		if ($fileInformation['size'] > ($maximumFileSize * 1024)) {
			$this->validationError
				= $this->translate('message_file_too_large') .
					' ' . $maximumFileSize . 'kB.';
		}
	}

	/**
	 * Deletes a file in the seminars upload directory and removes it from the
	 * list of currently attached files.
	 *
	 * @param string file name, must match an uploaded file, must not be empty
	 *
	 * @return string comma-separated list with the still attached files,
	 *                will be empty if the last attachment was removed
	 */
	private function purgeUploadedFile($fileName) {
		@unlink(PATH_site . 'uploads/tx_seminars/' . $fileName);
		$keyToPurge = array_search($fileName, $this->attachedFiles);
		if($keyToPurge !== false) {
			unset($this->attachedFiles[$keyToPurge]);
		}
	}

	/**
	 * Returns an error message if the provided file was invalid.
	 *
	 * @return string localized validation error message, will be empty if
	 *                $this->validationError was empty
	 */
	public function getFileUploadErrorMessage() {
		return $this->validationError;
	}

	/**
	 * Retrieves the keys of the subparts which should be hidden in the event
	 * editor.
	 *
	 * @return array the keys of the subparts which should be hidden in the
	 *               event editor without the prefix FE_EDITOR_, will be empty
	 *               if all subparts should be shown.
	 */
	private function getHiddenSubparts() {
		$visibilityTree = tx_oelib_ObjectFactory::make(
			'tx_oelib_Visibility_Tree', $this->createTemplateStructure()
		);

		$visibilityTree->makeNodesVisible($this->getFieldsToShow());
		$subpartsToHide = $visibilityTree->getKeysOfHiddenSubparts();
		$visibilityTree->__destruct();

		return $subpartsToHide;
	}

	/**
	 * Creates the template subpart structure.
	 *
	 * @return array the template's subpart structure for use with
	 *               tx_oelib_Visibility_Tree
	 */
	private function createTemplateStructure() {
		return array(
			'subtitle' => false,
			'title_right' => array(
				'accreditation_number' => false,
				'credit_points' => false,
			),
			'basic_information' => array(
				'categories' => false,
				'event_type' => false,
				'cancelled' => false,
			),
			'text_blocks' => array(
				'teaser' => false,
				'description' => false,
				'additional_information' => false,
			),
			'registration_information' => array(
				'dates' => array(
					'events_dates' => array(
						'begin_date' => false,
						'end_date' => false,
					),
					'registration_dates' => array(
						'begin_date_registration' => false,
						'deadline_early_bird' => false,
						'deadline_registration' => false,
					),
				),
				'attendance_information' => array(
					'registration_and_queue' => array(
						'needs_registration' => false,
						'allows_multiple_registrations' => false,
						'queue_size' => false,
					),
					'attendees_number' => array(
						'attendees_min' => false,
						'attendees_max' => false,
						'offline_attendees' => false,
					),
				),
				'target_groups' => false,
				'prices' => array(
					'regular_prices' => array(
						'price_regular' => false,
						'price_regular_early' => false,
						'price_regular_board' => false,
						'payment_methods' => false,
					),
					'special_prices' => array(
						'price_special' => false,
						'price_special_early' => false,
						'price_special_board' => false,
					),
				),
			),
			'place_information' => array(
				'place_and_room' => array(
					'place' => false,
					'room' => false,
				),
				'lodging_and_food' => array(
					'lodgings' => false,
					'foods' => false,
				),
			),
			'speakers' => false,
			'leaders' => false,
			'partner_tutor' => array(
				'partners' => false,
				'tutors' => false,
			),
			'checkbox_options' => array(
				'checkboxes' => false,
				'uses_terms_2' => false,
			),
			'attached_file_box' => false,
			'notes' => false,
		);
	}

	/**
	 * Returns the keys of the fields which should be shown in the FE editor.
	 *
	 * @return array the keys of the fields which should be shown, will be empty
	 *               if all fields should be hidden
	 */
	private function getFieldsToShow() {
		return t3lib_div::trimExplode(
			',',
			$this->getConfValueString(
				'displayFrontEndEditorFields', 's_fe_editing'),
			true
		);
	}

	/**
	 * Returns whether front-end editing of the given related record type is
	 * allowed.
	 *
	 * @param array $parameters the contents of the "params" child of the
	 *                          userobj node as key/value pairs
	 *
	 * @return boolean true if front-end editing of the given related record
	 *                 type is allowed, false otherwise
	 */
	public function isFrontEndEditingOfRelatedRecordsAllowed(array $parameters) {
		$relatedRecordType = $parameters['relatedRecordType'];

		$frontEndUser = tx_oelib_FrontEndLoginManager::
			getInstance()->getLoggedInUser('tx_seminars_Mapper_FrontEndUser');

		$isFrontEndEditingAllowed = $this->getConfValueBoolean(
			'allowFrontEndEditingOf' . $relatedRecordType, 's_fe_editing'
		);

		$axiliaryPidFromSetup = $this->getConfValueBoolean(
			'createAuxiliaryRecordsPID'
		);
		$isAnAuxiliaryPidSet = ($frontEndUser->getAuxiliaryRecordsPid() > 0) ||
			($axiliaryPidFromSetup > 0);

		return $isFrontEndEditingAllowed && $isAnAuxiliaryPidSet;
	}

	/**
	 * Reads the list of required form fields from the configuration and stores
	 * it in $this->requiredFormFields.
	 */
	private function setRequiredFormFields() {
		$this->requiredFormFields = t3lib_div::trimExplode(
			',',
			$this->getConfValueString(
				'requiredFrontEndEditorFields', 's_fe_editing'
			)
		);
	}

	/**
	 * Adds a class 'required' to the label of a field if it is required.
	 *
	 * @param tx_oelib_template $template the template in which the required
	 *        markers should be set.
	 */
	private function setRequiredFieldLabels(tx_oelib_template $template) {
		$formFieldsToCheck = $this->getFieldsToShow();

		foreach ($formFieldsToCheck as $formField) {
			$template->setMarker(
				$formField . '_required',
				(in_array($formField, $this->requiredFormFields))
					? ' class="required"'
					: ''
			);
		}
	}

	/**
	 * Checks whether a given field is required.
	 *
	 * @param array $field
	 *        the field to check, the array must contain an element with the key
	 *        'elementName' and a nonempty value for that key
	 *
	 * @return boolean true if the field is required, false otherwise
	 */
	private function isFieldRequired(array $field) {
		if ($field['elementName'] == '') {
			throw new Exception('The given field name was empty.');
		}

		return in_array($field['elementName'], $this->requiredFormFields);
	}

	/**
	 * Checks whether a given field needs to be filled in, but hasn't been
	 * filled in yet.
	 *
	 * @param array $formData
	 *        associative array containing the current value, with the key
	 *        'value' and the name, with the key 'elementName', of the form
	 *        field to check, must not be empty
	 *
	 * @return boolean true if this field is not empty or not required, false
	 *                 otherwise
	 */
	public function validateString(array $formData) {
		if (!$this->isFieldRequired($formData)) {
			return true;
		}

		return (trim($formData['value']) != '');
	}

	/**
	 * Checks whether a given field needs to be filled in with a non-zero value,
	 * but hasn't been filled in correctly yet.
	 *
	 * @param array $formData
	 *        associative array containing the current value, with the key
	 *        'value' and the name, with the key 'elementName', of the form
	 *        field to check, must not be empty
	 *
	 * @return boolean true if this field is not zero or not required, false
	 *                 otherwise
	 */
	public function validateInteger(array $formData) {
		if (!$this->isFieldRequired($formData)) {
			return true;
		}

		return (intval($formData['value']) != 0);
	}

	/**
	 * Checks whether a given field needs to be filled in with a non-empty array,
	 * but hasn't been filled in correctly yet.
	 *
	 * @param array $formData
	 *        associative array containing the current value, with the key
	 *        'value' and the name, with the key 'elementName', of the form
	 *        field to check, must not be empty
	 *
	 * @return boolean true if this field is not zero or not required, false
	 *                 otherwise
	 */
	public function validateCheckboxes(array $formData) {
		if (!$this->isFieldRequired($formData)) {
			return true;
		}

		return is_array($formData['value']) && !empty($formData['value']);
	}

	/**
	 * Checks whether a given field needs to be filled in with a valid date,
	 * but hasn't been filled in correctly yet.
	 *
	 * @param array $formData
	 *        associative array containing the current value, with the key
	 *        'value' and the name, with the key 'elementName', of the form
	 *        field to check, must not be empty
	 *
	 * @return boolean true if this field contains a valid date or if this field
	 *                 is not required, false otherwise
	 */
	public function validateDate(array $formData) {
		if (!$this->isFieldRequired($formData)) {
			return true;
		}

		return (preg_match('/^[\d:\-\/ ]+$/', $formData['value']) == 1);
	}

	/**
	 * Checks whether a given field needs to be filled in with a valid price,
	 * but hasn't been filled in correctly yet.
	 *
	 * @param array $formData
	 *        associative array containing the current value, with the key
	 *        'value' and the name, with the key 'elementName', of the form
	 *        field to check, must not be empty
	 *
	 * @return boolean true if this field contains a valid price or if this
	 *                 field is not required, false otherwise
	 */
	public function validatePrice(array $formData) {
		if (!$this->isFieldRequired($formData)) {
			return true;
		}

		return (preg_match('/^\d+((,|.)\d{1,2})?$/', $formData['value']) == 1);
	}

	/**
	 * Sends the publishing e-mail to the reviewer if necessary.
	 */
	public function sendEMailToReviewer() {
		if ($this->publicationHash == '') {
			return;
		}
		tx_oelib_MapperRegistry::purgeInstance();
		$frontEndUser = tx_oelib_FrontEndLoginManager::getInstance()
			->getLoggedInUser('tx_seminars_Mapper_FrontEndUser');
		$reviewer = $frontEndUser->getReviewerFromGroup();

		if (!$reviewer) {
			return;
		}

		$event = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Event')
			->findByPublicationHash($this->publicationHash);

		if ($event && $event->isHidden()) {
			$eMail = tx_oelib_ObjectFactory::make('tx_oelib_Mail');
			$eMail->addRecipient($reviewer);
			$eMail->setSender($frontEndUser);
			$eMail->setSubject($this->translate('publish_event_subject'));
			$eMail->setMessage($this->createEMailContent($event));

			tx_oelib_mailerFactory::getInstance()->getMailer()->send($eMail);

			$eMail->__destruct();
		}
	}

	/**
	 * Builds the content for the publishing e-mail to the reviewer.
	 *
	 * @param tx_seminars_Model_Event $event
	 *        the event to send the publication e-mail for
	 *
	 * @return string the e-mail body for the publishing e-mail, will not be
	 *                empty
	 */
	private function createEMailContent(tx_seminars_Model_Event $event) {
		$this->getTemplateCode(true);
		$this->setLabels();

		$markerPrefix = 'publish_event';

		if ($event->hasBeginDate()) {
			$beginDate = strftime(
				$this->getConfValueString('dateFormatYMD'),
				$event->getBeginDateAsUnixTimeStamp()
			);
		} else {
			$beginDate = '';
		}

		$this->setMarker('title', $event->getTitle(), $markerPrefix);
		$this->setOrDeleteMarkerIfNotEmpty(
			'date', $beginDate, $markerPrefix, 'wrapper_publish_event'
		);
		$this->setMarker(
			'description', $event->getDescription(), $markerPrefix
		);

		$this->setMarker('link', $this->createReviewUrl(), $markerPrefix);

		return $this->getSubpart('MAIL_PUBLISH_EVENT');
	}

	/**
	 * Builds the URL for the reviewer e-mail.
	 *
	 * @return string the URL for the plain text e-mail, will not be empty
	 */
	private function createReviewUrl() {
		$url = $this->cObj->typoLink_URL(array(
			'parameter' => $GLOBALS['TSFE']->id . ',' .
				$this->getConfValueInteger('typeNumForPublish'),
			'additionalParams' => t3lib_div::implodeArrayForUrl(
				'tx_seminars_publication',
				array(
					'hash' => $this->publicationHash,
				),
				'',
				false,
				true
			),
			'type' => $this->getConfValueInteger('typeNumForPublish'),
		));

		return t3lib_div::locationHeaderUrl(preg_replace(
			array('/\[/', '/\]/'),
			array('%5B', '%5D'),
			$url
		));
	}

	/**
	 * Creates a new place record.
	 *
	 * This function is intended to be called via an AJAX FORMidable event.
	 *
	 * @param tx_ameosformidable $formidable
	 *        the FORMidable object for the AJAX call
	 *
	 * @return array calls to be executed on the client
	 */
	public static function createNewPlace(tx_ameosformidable $formidable) {
		$formData = $formidable->oMajixEvent->getParams();
		$validationErrors = self::validatePlace(
			$formidable, array(
				'title' => $formData['newPlace_title'],
				'city' => $formData['newPlace_city'],
			)
		);
		if (!empty($validationErrors)) {
			return array(
				$formidable->majixExecJs(
					'alert("' . implode('\n', $validationErrors) . '");'
				),
			);
		};

		$countryUid = intval($formData['newPlace_country']);
		if ($countryUid > 0) {
			try {
				$country = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country')
					->find($countryUid);
				$countryCode = $country->getIsoAlpha2Code();
			} catch (Exception $exception) {
				$countryCode = '';
			}
		} else {
			$countryCode = '';
		}

		$place = tx_oelib_ObjectFactory::make('tx_seminars_Model_Place');
		$place->setData(array_merge(
			self::createBasicAuxiliaryData(),
			array(
				'title' => trim(strip_tags($formData['newPlace_title'])),
				'address' => trim(strip_tags($formData['newPlace_address'])),
				'city' => trim(strip_tags($formData['newPlace_city'])),
				'country' => $countryCode,
				'homepage' => trim(strip_tags($formData['newPlace_homepage'])),
				'directions' => trim($formData['newPlace_directions']),
				'notes' => trim(strip_tags($formData['newPlace_notes']))
			)
		));
		$place->markAsDirty();
		tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Place')->save($place);

		return array(
			$formidable->aORenderlets['newPlaceModalBox']->majixCloseBox(),
			$formidable->majixExecJs(
				'appendPlaceInEditor(' . $place->getUid() . ', "' .
					addcslashes($place->getTitle(), '"\\') . '")'
			),
		);
	}

	/**
	 * Validates the entered data for a place.
	 *
	 * @param tx_ameosformidable $formidable
	 *        the FORMidable object for the AJAX call
	 * @param array $formData
	 *        the entered form data, the key must be stripped of the
	 *        "newPlace_"/"editPlace_" prefix
	 *
	 * @return array the error messages, will be empty if there are no
	 *         validation errors
	 */
	private static function validatePlace(
		tx_ameosformidable $formidable, array $formData
	) {
		$validationErrors = array();
		if (trim($formData['title']) == '') {
			$validationErrors[] = $formidable->getLLLabel(
				'LLL:EXT:seminars/pi1/locallang.xml:message_emptyTitle'
			);
		}
		if (trim($formData['city']) == '') {
			$validationErrors[] = $formidable->getLLLabel(
				'LLL:EXT:seminars/pi1/locallang.xml:message_emptyCity'
			);
		}

		return $validationErrors;
	}

	/**
	 * Creates the basic data for a FE-entered auxiliary record (owner, PID).
	 *
	 * @return array the basic data as an associative array, will not be empty
	 */
	private static function createBasicAuxiliaryData() {
		$GLOBALS['TSFE']->tmpl->start(
			t3lib_div::makeInstance('t3lib_pageSelect')->getRootLine(
				$GLOBALS['TSFE']->id
			)
		);

		$owner = tx_oelib_FrontEndLoginManager::getInstance()
			->getLoggedInUser('tx_seminars_Mapper_FrontEndUser');
		$ownerPageUid = $owner->getAuxiliaryRecordsPid();

		$pageUid = ($ownerPageUid > 0)
			? $ownerPageUid
			: tx_oelib_ConfigurationRegistry::get('plugin.tx_seminars_pi1')
				->getAsInteger('createAuxiliaryRecordsPID');

		return array(
			'owner' => $owner,
			'pid' => $pageUid,
		);
	}

	/**
	 * Creates a new speaker record.
	 *
	 * This function is intended to be called via an AJAX FORMidable event.
	 *
	 * @param tx_ameosformidable $formidable
	 *        the FORMidable object for the AJAX call
	 *
	 * @return array calls to be executed on the client
	 */
	public static function createNewSpeaker(tx_ameosformidable $formidable) {
		$formData = $formidable->oMajixEvent->getParams();
		$validationErrors = self::validateSpeaker(
			$formidable, array('title' => $formData['newSpeaker_title'])
		);
		if (!empty($validationErrors)) {
			return array(
				$formidable->majixExecJs(
					'alert("' . implode('\n', $validationErrors) . '");'
				),
			);
		};

		$skillMapper = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Skill');
		$skills = tx_oelib_ObjectFactory::make('tx_oelib_List');
		if (is_array($formData['newSpeaker_skills'])) {
			foreach ($formData['newSpeaker_skills'] as $rawUid) {
				$safeUid = intval($rawUid);
				if ($safeUid > 0) {
					$skills->add($skillMapper->find($safeUid));
				}
			}

		}

		$speaker = tx_oelib_ObjectFactory::make('tx_seminars_Model_Speaker');
		$speaker->setData(array_merge(
			self::createBasicAuxiliaryData(),
			array(
				'title' => trim(strip_tags($formData['newSpeaker_title'])),
				'gender' => intval($formData['newSpeaker_gender']),
				'organization'
					=> trim(strip_tags($formData['newSpeaker_organization'])),
				'homepage' => trim(strip_tags($formData['newSpeaker_homepage'])),
				'description' => trim($formData['newSpeaker_description']),
				'skills' => $skills,
				'notes' => trim(strip_tags($formData['newSpeaker_notes'])),
				'address' => trim(strip_tags($formData['newSpeaker_address'])),
				'phone_work'
					=> trim(strip_tags($formData['newSpeaker_phone_work'])),
				'phone_home'
					=> trim(strip_tags($formData['newSpeaker_phone_home'])),
				'phone_mobile'
					=> trim(strip_tags($formData['newSpeaker_phone_mobile'])),
				'fax' => trim(strip_tags($formData['newSpeaker_fax'])),
				'email' => trim(strip_tags($formData['newSpeaker_email'])),
				'cancelation_period'
					=> intval($formData['newSpeaker_cancelation_period']),
			)
		));
		$speaker->markAsDirty();
		tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Speaker')->save($speaker);

		return array(
			$formidable->aORenderlets['newSpeakerModalBox']->majixCloseBox(),
			$formidable->majixExecJs(
				'appendSpeakerInEditor(' . $speaker->getUid() . ', "' .
					addcslashes($speaker->getName(), '"\\') . '")'
			),
		);
	}

	/**
	 * Validates the entered data for a speaker.
	 *
	 * @param tx_ameosformidable $formidable
	 *        the FORMidable object for the AJAX call
	 * @param array $formData
	 *        the entered form data, the key must be stripped of the
	 *        "newSpeaker_"/"editSpeaker_" prefix
	 *
	 * @return array the error messages, will be empty if there are no
	 *         validation errors
	 */
	private static function validateSpeaker(
		tx_ameosformidable $formidable, array $formData
	) {
		$validationErrors = array();
		if (trim($formData['title']) == '') {
			$validationErrors[] = $formidable->getLLLabel(
				'LLL:EXT:seminars/pi1/locallang.xml:message_emptyName'
			);
		}

		return $validationErrors;
	}

	/**
	 * Provides data items for the list of countries.
	 *
	 * @return array items as an array with the keys "caption" (for the title)
	 *         and "value" (for the UID)
	 */
	public static function populateListCountries() {
		$result = array();

		$countries = tx_oelib_MapperRegistry::get('tx_oelib_Mapper_Country')
			->findAll();
		foreach ($countries as $country) {
			$result[] = array(
				'caption' => $country->getLocalShortName(),
				'value' => $country->getUid(),
			);
		}

		return $result;
	}

	/**
	 * Provides data items for the list of skills.
	 *
	 * @return array items as an array with the keys "caption" (for the title)
	 *         and "value" (for the UID)
	 */
	public static function populateListSkills() {
		$result = array();

		$skills = tx_oelib_MapperRegistry::get('tx_seminars_Mapper_Skill')
			->findAll();
		foreach ($skills as $skill) {
			$result[] = array(
				'caption' => $skill->getTitle(),
				'value' => $skill->getUid(),
			);
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_eventEditor.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1_eventEditor.php']);
}
?>