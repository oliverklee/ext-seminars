<?php

declare(strict_types=1);

use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Email\SystemEmailFromBuilder;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Oelib\Model\BackEndUser;
use OliverKlee\Oelib\Model\Country;
use OliverKlee\Oelib\Templating\Template;
use OliverKlee\Seminars\Model\Interfaces\Titled;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This class is a controller which allows to create and edit events on the FE.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_FrontEnd_EventEditor extends \Tx_Seminars_FrontEnd_Editor
{
    /**
     * @var string stores a validation error message if there was one
     */
    private $validationError = '';

    /**
     * @var string[] currently attached files
     */
    private $attachedFiles = [];

    /**
     * @var string the prefix used for every subpart in the FE editor
     */
    const SUBPART_PREFIX = 'fe_editor';

    /**
     * @var string[] the fields required to file a new event.
     */
    private $requiredFormFields = [];

    /**
     * @var string the publication hash for the event to edit/create
     */
    private $publicationHash = '';

    /**
     * @var mixed[]
     */
    protected $savedFormData = [];

    /**
     * The constructor.
     *
     * After the constructor has been called, hasAccessMessage() must be called
     * to ensure that the logged-in user is allowed to edit a given seminar.
     *
     * @param array $configuration TypoScript configuration for the plugin
     * @param ContentObjectRenderer $contentObjectRenderer the parent cObj content, needed for the flexforms
     */
    public function __construct(array $configuration, ContentObjectRenderer $contentObjectRenderer)
    {
        parent::__construct($configuration, $contentObjectRenderer);
        \tx_rnbase::load(\Tx_Rnbase_Database_Connection::class);
        $this->setRequiredFormFields();
    }

    /**
     * There are more than one init calls.
     * Mkforms calls the init for code behinds and oelib initializes the class, too.
     *
     * @param array|\tx_mkforms_forms_IForm|null $configuration
     */
    public function init($configuration = null)
    {
        if (is_array($configuration)) {
            parent::init($configuration);
        }
    }

    /**
     * Stores the currently attached files.
     *
     * Attached files are stored in a member variable and added to the form data
     * afterwards, as the FORMidable renderlet is not usable for this.
     *
     * @return void
     */
    private function storeAttachedFiles()
    {
        if ($this->isTestMode()) {
            $this->attachedFiles = [];
        } else {
            $dataHandler = $this->getFormCreator()->getDataHandler();
            $this->attachedFiles = GeneralUtility::trimExplode(
                ',',
                $dataHandler->__aStoredData['attached_files'],
                true
            );
        }
    }

    /**
     * Declares the additional data handler for m:n relations.
     *
     * @return void
     */
    private function declareDataHandler()
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ameos_formidable']
        ['declaredobjects']['datahandlers']['DBMM'] = [
            'key' => 'dh_dbmm',
            'base' => true,
        ];
    }

    /**
     * Creates the HTML output.
     *
     * @return string HTML of the create/edit form
     */
    public function render(): string
    {
        $this->setFormConfiguration((array)$this->conf['form.']['eventEditor.']);
        $this->declareDataHandler();

        $this->storeAttachedFiles();

        /** @var Template $template */
        $template = GeneralUtility::makeInstance(Template::class);
        $template->processTemplate(parent::render());

        $template->hideSubpartsArray(
            $this->getHiddenSubparts(),
            self::SUBPART_PREFIX
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
     * @param Template $template holds the raw HTML output, must be already processed by FORMidable
     *
     * @return string HTML for the FE editor with the formatted attachment
     *                list if there are attached files, will not be empty
     */
    private function getHtmlWithAttachedFilesList(Template $template): string
    {
        foreach (
            [
                'label_delete',
                'label_really_delete',
                'label_save',
                'label_save_and_back',
            ] as $label
        ) {
            $template->setMarker($label, $this->translate($label));
        }

        /** @var \tx_ameosformidable $form */
        $form = $this->getFormCreator()->getDataHandler()->oForm;
        /** @var \formidable_mainrenderlet $renderlet */
        $renderlet = $form->aORenderlets['attached_files'];
        $originalAttachmentList = $renderlet->mForcedValue;

        if (empty($this->attachedFiles)) {
            $template->hideSubparts('attached_files');
        } else {
            $attachmentList = '';
            $fileNumber = 1;
            foreach ($this->attachedFiles as $fileName) {
                $template->setMarker('file_name', $fileName);
                $template->setMarker(
                    'single_attached_file_id',
                    'attached_file_' . $fileNumber
                );
                $fileNumber++;
                $attachmentList .= $template->getSubpart('SINGLE_ATTACHED_FILE');
            }
            $template->setSubpart('single_attached_file', $attachmentList);
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
     * @return array[] $items with additional items from the categories
     *               table as an array with the keys "caption" (for the
     *               title) and "value" (for the UID)
     */
    public function populateListCategories(): array
    {
        /** @var \Tx_Seminars_Mapper_Category $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Category::class);
        $categories = $mapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        return self::makeListToFormidableList($categories);
    }

    /**
     * Provides data items for the list of available event types.
     *
     * @return array[] $items with additional items from the event_types
     *               table as an array with the keys "caption" (for the
     *               title) and "value" (for the UID)
     */
    public function populateListEventTypes(): array
    {
        /** @var \Tx_Seminars_Mapper_EventType $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_EventType::class);
        $eventTypes = $mapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        return self::makeListToFormidableList($eventTypes);
    }

    /**
     * Provides data items for the list of available lodgings.
     *
     * @return array[] $items with additional items from the lodgings table
     *               as an array with the keys "caption" (for the title)
     *               and "value" (for the UID)
     */
    public function populateListLodgings(): array
    {
        /** @var \Tx_Seminars_Mapper_Lodging $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Lodging::class);
        $lodgings = $mapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        return self::makeListToFormidableList($lodgings);
    }

    /**
     * Provides data items for the list of available foods.
     *
     * @return array[] $items with additional items from the foods table
     *               as an array with the keys "caption" (for the title)
     *               and "value" (for the UID)
     */
    public function populateListFoods(): array
    {
        /** @var \Tx_Seminars_Mapper_Food $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Food::class);
        $foods = $mapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        return self::makeListToFormidableList($foods);
    }

    /**
     * Provides data items for the list of available payment methods.
     *
     * @return array[] $items with additional items from payment methods
     *               table as an array with the keys "caption" (for the
     *               title) and "value" (for the UID)
     */
    public function populateListPaymentMethods(): array
    {
        /** @var \Tx_Seminars_Mapper_PaymentMethod $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_PaymentMethod::class);
        $paymentMethods = $mapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        return self::makeListToFormidableList($paymentMethods);
    }

    /**
     * Provides data items for the list of available organizers.
     *
     * @return array[] $items with additional items from the organizers
     *               table as an array with the keys "caption" (for the
     *               title) and "value" (for the UID)
     */
    public function populateListOrganizers(): array
    {
        $frontEndUser = self::getLoggedInUser();
        if (!$frontEndUser instanceof \Tx_Seminars_Model_FrontEndUser) {
            return [];
        }

        if ($frontEndUser->hasDefaultOrganizers()) {
            $organizers = $frontEndUser->getDefaultOrganizers();
        } else {
            /** @var \Tx_Seminars_Mapper_Organizer $mapper */
            $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Organizer::class);
            $organizers = $mapper->findByPageUid((string)$this->getPidForAuxiliaryRecords(), 'title ASC');
        }

        return self::makeListToFormidableList($organizers);
    }

    /**
     * Returns the logged-in user.
     *
     * @return \Tx_Seminars_Model_FrontEndUser|null
     */
    protected static function getLoggedInUser()
    {
        return FrontEndLoginManager::getInstance()->getLoggedInUser(\Tx_Seminars_Mapper_FrontEndUser::class);
    }

    /**
     * Provides data items for the list of available places.
     *
     * @param array[] $items any pre-filled data (may be empty)
     * @param array|null $unused unused
     * @param \tx_mkforms_forms_Base $form
     *
     * @return array[] $items with additional items from the places table
     *               as an array with the keys "caption" (for the title)
     *               and "value" (for the UID)
     */
    public function populateListPlaces(array $items, $unused = null, \tx_mkforms_forms_Base $form = null): array
    {
        $result = $items;

        /** @var \Tx_Seminars_Mapper_Place $placeMapper */
        $placeMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Place::class);
        $places = $placeMapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        if ($form !== null) {
            /** @var \formidable_mainrenderlet $renderlet */
            $renderlet = $form->aORenderlets['editPlaceButton'];
            /** @var array $editButtonConfiguration */
            $editButtonConfiguration = $form->_navConf($renderlet->sXPath);
        } else {
            $renderlet = null;
            $editButtonConfiguration = [];
        }

        $frontEndUser = self::getLoggedInUser();

        $showEditButton = $this->isFrontEndEditingOfRelatedRecordsAllowed(
            ['relatedRecordType' => 'Places']
        ) && $form !== null;

        /** @var \Tx_Seminars_Model_Place $place */
        foreach ($places as $place) {
            $frontEndUserIsOwner = $place->getOwner() === $frontEndUser;

            // Only shows places which have no owner or where the owner is the
            // currently logged in front-end user.
            if (!$frontEndUserIsOwner && $place->getOwner()) {
                continue;
            }

            if ($showEditButton && $frontEndUserIsOwner) {
                $editButtonConfiguration['name'] = 'editPlaceButton_' . $place->getUid();
                $editButtonConfiguration['onclick']['userobj']['php'] = '
                    return ' . self::class . '::showEditPlaceModalBox($this, ' . $place->getUid() . ');
                ';
                /** @var \tx_mkforms_widgets_button_Main $editButton */
                $editButton = $form->_makeRenderlet($editButtonConfiguration, $renderlet->sXPath);
                $editButton->includeScripts();
                $editButtonHTML = $editButton->_render();
                $result[] = [
                    'caption' => $place->getTitle(),
                    'value' => $place->getUid(),
                    'labelcustom' => 'id="tx_seminars_pi1_seminars_place_label_' . $place->getUid() . '"',
                    'wrapitem' => '|</td><td>' . $editButtonHTML['__compiled'],
                ];
            } else {
                $result[] = [
                    'caption' => $place->getTitle(),
                    'value' => $place->getUid(),
                    'wrapitem' => '|</td><td>&nbsp;',
                ];
            }
        }

        return $result;
    }

    /**
     * Provides data items for the list of available speakers.
     *
     * @param array $parameters the parameters sent to this function by FORMidable
     * @param \tx_mkforms_forms_Base $form
     *
     * @return array[] $items with additional items from the speakers table
     *               as an array with the keys "caption" (for the title)
     *               and "value" (for the UID)
     */
    public function populateListSpeakers(
        array $parameters = [],
        \tx_mkforms_forms_Base $form = null
    ): array {
        $result = [];

        /** @var \Tx_Seminars_Mapper_Speaker $speakerMapper */
        $speakerMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Speaker::class);
        $speakers = $speakerMapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        if ($form !== null) {
            /** @var \formidable_mainrenderlet $renderlet */
            $renderlet = $form->aORenderlets['editSpeakerButton'];
            /** @var array $editButtonConfiguration */
            $editButtonConfiguration = $form->_navConf($renderlet->sXPath);
        } else {
            $renderlet = null;
            $editButtonConfiguration = [];
        }

        $frontEndUser = self::getLoggedInUser();

        $showEditButton = $this->isFrontEndEditingOfRelatedRecordsAllowed(
            ['relatedRecordType' => 'Speakers']
        ) && $form !== null;

        $type = $parameters['type'];
        if (empty($parameters['lister'])) {
            $isLister = false;
            $activeSpeakers = '';
        } else {
            $isLister = true;
            $activeSpeakers = $form->getDataHandler()->getStoredData(strtolower($type) . 's');
        }

        /** @var \Tx_Seminars_Model_Speaker $speaker */
        foreach ($speakers as $speaker) {
            $frontEndUserIsOwner = ($speaker->getOwner() === $frontEndUser);

            // Only shows speakers which have no owner or where the owner is
            // the currently logged in front-end user.
            if (!$frontEndUserIsOwner && $speaker->getOwner()) {
                continue;
            }

            // the new method to list the speakers
            if ($isLister) {
                $result[] = [
                    'uid' => $speaker->getUid(),
                    'selected' => GeneralUtility::inList($activeSpeakers, $speaker->getUid()) ? 1 : 0,
                    'name' => $speaker->getName(),
                    'edit' => ($showEditButton && $frontEndUserIsOwner) ? 1 : 0,
                ];
                continue;
            }
            if ($showEditButton && $frontEndUserIsOwner) {
                $editButtonConfiguration['name'] = 'edit' . $type . 'Button_' . $speaker->getUid();
                $editButtonConfiguration['onclick']['userobj']['php'] = '
                    return ' . self::class . '::showEditSpeakerModalBox($this, ' . $speaker->getUid() . ');';
                /** @var \tx_mkforms_widgets_button_Main $editButton */
                $editButton = $form->_makeRenderlet($editButtonConfiguration, $renderlet->sXPath);
                $editButton->includeScripts();
                $editButtonHTML = $editButton->_render();
                $result[] = [
                    'caption' => $speaker->getName(),
                    'value' => $speaker->getUid(),
                    'labelcustom' => 'id="tx_seminars_pi1_seminars_' .
                        strtolower($type) . '_label_' . $speaker->getUid() . '"',
                    'wrapitem' => '|</td><td>' . $editButtonHTML['__compiled'],
                ];
            } else {
                $result[] = [
                    'caption' => $speaker->getName(),
                    'value' => $speaker->getUid(),
                    'wrapitem' => '|</td><td>&nbsp;',
                ];
            }
        }

        return $result;
    }

    /**
     * Provides data items for the list of available checkboxes.
     *
     * @param array[] $items any pre-filled data (may be empty)
     * @param array|null $unused unused
     * @param \tx_mkforms_forms_Base $form
     *
     * @return array[] $items with additional items from the checkboxes
     *               table as an array with the keys "caption" (for the
     *               title) and "value" (for the UID)
     */
    public function populateListCheckboxes(
        array $items,
        $unused = null,
        \tx_mkforms_forms_Base $form = null
    ): array {
        $result = $items;

        /** @var \Tx_Seminars_Mapper_Checkbox $checkboxMapper */
        $checkboxMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Checkbox::class);
        $checkboxes = $checkboxMapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        if ($form !== null) {
            /** @var \formidable_mainrenderlet $renderlet */
            $renderlet = $form->aORenderlets['editCheckboxButton'];
            /** @var array $editButtonConfiguration */
            $editButtonConfiguration = $form->_navConf($renderlet->sXPath);
        } else {
            $renderlet = null;
            $editButtonConfiguration = [];
        }

        $frontEndUser = self::getLoggedInUser();

        $showEditButton = $this->isFrontEndEditingOfRelatedRecordsAllowed(
            ['relatedRecordType' => 'Checkboxes']
        ) && $form !== null;

        /** @var \Tx_Seminars_Model_Checkbox $checkbox */
        foreach ($checkboxes as $checkbox) {
            $frontEndUserIsOwner = ($checkbox->getOwner() === $frontEndUser);

            // Only shows checkboxes which have no owner or where the owner is
            // the currently logged in front-end user.
            if (!$frontEndUserIsOwner && $checkbox->getOwner()) {
                continue;
            }

            if ($showEditButton && $frontEndUserIsOwner) {
                $editButtonConfiguration['name'] = 'editCheckboxButton_' . $checkbox->getUid();
                $editButtonConfiguration['onclick']['userobj']['php'] = '
                    return ' . self::class . '::showEditCheckboxModalBox($this, ' . $checkbox->getUid() . ');
                ';
                /** @var \tx_mkforms_widgets_button_Main $editButton */
                $editButton = $form->_makeRenderlet($editButtonConfiguration, $renderlet->sXPath);
                $editButton->includeScripts();
                $editButtonHTML = $editButton->_render();
                $result[] = [
                    'caption' => $checkbox->getTitle(),
                    'value' => $checkbox->getUid(),
                    'labelcustom' => 'id="tx_seminars_pi1_seminars_checkbox_label_' . $checkbox->getUid() . '"',
                    'wrapitem' => '|</td><td>' . $editButtonHTML['__compiled'],
                ];
            } else {
                $result[] = [
                    'caption' => $checkbox->getTitle(),
                    'value' => $checkbox->getUid(),
                    'wrapitem' => '|</td><td>&nbsp;',
                ];
            }
        }

        return $result;
    }

    /**
     * Provides data items for the list of available target groups.
     *
     * @param array[] $items array any pre-filled data (may be empty)
     * @param array|null $unused unused
     * @param \tx_mkforms_forms_Base $form
     *
     * @return array[] $items with additional items from the target groups
     *               table as an array with the keys "caption" (for the
     *               title) and "value" (for the UID)
     */
    public function populateListTargetGroups(
        array $items,
        $unused = null,
        \tx_mkforms_forms_Base $form = null
    ): array {
        $result = $items;

        /** @var \Tx_Seminars_Mapper_TargetGroup $targetGroupMapper */
        $targetGroupMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_TargetGroup::class);
        $targetGroups = $targetGroupMapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        if ($form !== null) {
            /** @var \formidable_mainrenderlet $renderlet */
            $renderlet = $form->aORenderlets['editTargetGroupButton'];
            /** @var array $editButtonConfiguration */
            $editButtonConfiguration = $form->_navConf($renderlet->sXPath);
        } else {
            $renderlet = null;
            $editButtonConfiguration = [];
        }

        $frontEndUser = self::getLoggedInUser();

        $showEditButton = $this->isFrontEndEditingOfRelatedRecordsAllowed(
            ['relatedRecordType' => 'TargetGroups']
        ) && $form !== null;

        /** @var \Tx_Seminars_Model_TargetGroup $targetGroup */
        foreach ($targetGroups as $targetGroup) {
            $frontEndUserIsOwner = ($targetGroup->getOwner() === $frontEndUser);

            // Only shows target groups which have no owner or where the owner
            // is the currently logged in front-end user.
            if (!$frontEndUserIsOwner && $targetGroup->getOwner()) {
                continue;
            }

            if ($showEditButton && $frontEndUserIsOwner) {
                $editButtonConfiguration['name'] = 'editTargetGroupButton_' .
                    $targetGroup->getUid();
                $editButtonConfiguration['onclick']['userobj']['php'] = '
                    return ' . self::class . '::showEditTargetGroupModalBox($this, ' . $targetGroup->getUid() . ');
                ';
                /** @var \tx_mkforms_widgets_button_Main $editButton */
                $editButton = $form->_makeRenderlet($editButtonConfiguration, $renderlet->sXPath);
                $editButton->includeScripts();
                $editButtonHTML = $editButton->_render();
                $result[] = [
                    'caption' => $targetGroup->getTitle(),
                    'value' => $targetGroup->getUid(),
                    'labelcustom' => 'id="tx_seminars_pi1_seminars_target_group_label_' . $targetGroup->getUid() . '"',
                    'wrapitem' => '|</td><td>' . $editButtonHTML['__compiled'],
                ];
            } else {
                $result[] = [
                    'caption' => $targetGroup->getTitle(),
                    'value' => $targetGroup->getUid(),
                    'wrapitem' => '|</td><td>&nbsp;',
                ];
            }
        }

        return $result;
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
    public function getEventSuccessfullySavedUrl(): string
    {
        $additionalParameters = '';

        if ($this->getFormValue('proceed_file_upload')) {
            $additionalParameters = GeneralUtility::implodeArrayForUrl(
                $this->prefixId,
                ['seminar' => $this->getObjectUid()]
            );
            $pageId = (int)$this->getFrontEndController()->id;
        } else {
            $pageId = $this->getConfValueInteger('eventSuccessfullySavedPID', 's_fe_editing');
        }

        return GeneralUtility::locationHeaderUrl(
            $this->cObj->typoLink_URL(
                [
                    'parameter' => $pageId,
                    'additionalParams' => $additionalParameters,
                ]
            )
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
     * @return string locallang key of an error message, will be an empty string if access was granted
     */
    private function checkAccess(): string
    {
        if (!FrontEndLoginManager::getInstance()->isLoggedIn()) {
            return 'message_notLoggedIn';
        }

        $uid = $this->getObjectUid();
        /** @var \Tx_Seminars_OldModel_Event|null $event */
        $event = \Tx_Seminars_OldModel_Event::fromUid($uid, true);

        if ($uid > 0 && !($event instanceof \Tx_Seminars_OldModel_Event)) {
            return 'message_wrongSeminarNumber';
        }

        $user = self::getLoggedInUser();
        if ($uid > 0 && $event instanceof \Tx_Seminars_OldModel_Event) {
            $isUserVip = $event->isUserVip($user->getUid(), $this->getConfValueInteger('defaultEventVipsFeGroupID'));
            $isUserOwner = $event->isOwnerFeUser();
            $mayManagersEditTheirEvents = $this->getConfValueBoolean('mayManagersEditTheirEvents', 's_listView');

            $hasAccess = $isUserOwner || ($mayManagersEditTheirEvents && $isUserVip);
        } else {
            $eventEditorGroupUid = $this->getConfValueInteger('eventEditorFeGroupID', 's_fe_editing');
            $hasAccess = $eventEditorGroupUid !== 0 && $user->hasGroupMembership((string)$eventEditorGroupUid);
        }

        return $hasAccess ? '' : 'message_noAccessToEventEditor';
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
    public function hasAccessMessage(): string
    {
        $result = '';
        $errorMessage = $this->checkAccess();

        if ($errorMessage !== '') {
            $this->setMarker('error_text', $this->translate($errorMessage));
            $result = $this->getSubpart('ERROR_VIEW');
        }

        return $result;
    }

    /**
     * Changes all potential decimal separators (commas and dots) in price
     * fields to dots.
     *
     * @param array[] $formData all entered form data with the field names as keys, will be modified, must not be empty
     *
     * @return void
     */
    private function unifyDecimalSeparators(array &$formData)
    {
        $priceFields = [
            'price_regular',
            'price_regular_early',
            'price_regular_board',
            'price_special',
            'price_special_early',
            'price_special_board',
        ];

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
     * @param array[] $formData form data, will be modified, must not be empty
     *
     * @return void
     */
    private function processAttachments(array &$formData)
    {
        $filesToDelete = GeneralUtility::trimExplode(
            ',',
            $formData['delete_attached_files'],
            true
        );

        foreach ($filesToDelete as $fileName) {
            // saves other files in the upload folder from being deleted
            if (in_array($fileName, $this->attachedFiles, true)) {
                $this->purgeUploadedFile($fileName);
            }
        }

        $formData['attached_files'] = implode(',', $this->attachedFiles);
    }

    /**
     * Removes all form data elements that are no fields in the seminars table.
     *
     * @param array[] $formData form data, will be modified, must not be empty
     *
     * @return void
     */
    private function purgeNonSeminarsFields(array &$formData)
    {
        /** @var string[][] $fieldsToUnset */
        $fieldsToUnset = [
            '' => ['proceed_file_upload', 'delete_attached_files'],
            'newPlace_' => [
                'title',
                'address',
                'zip',
                'city',
                'country',
                'homepage',
                'directions',
                'notes',
            ],
            'editPlace_' => [
                'title',
                'address',
                'zip',
                'city',
                'country',
                'homepage',
                'directions',
                'notes',
                'uid',
            ],
            'newSpeaker_' => [
                'title',
                'gender',
                'organization',
                'homepage',
                'description',
                'skills',
                'notes',
                'address',
                'phone_work',
                'phone_home',
                'phone_mobile',
                'fax',
                'email',
                'cancelation_period',
            ],
            'editSpeaker_' => [
                'title',
                'gender',
                'organization',
                'homepage',
                'description',
                'skills',
                'notes',
                'address',
                'phone_work',
                'phone_home',
                'phone_mobile',
                'fax',
                'email',
                'cancelation_period',
                'uid',
            ],
            'newCheckbox_' => ['title'],
            'editCheckbox_' => ['title', 'uid'],
            'newTargetGroup_' => [
                'title',
                'uid',
                'minimum_age',
                'maximum_age',
            ],
            'editTargetGroup_' => [
                'title',
                'uid',
                'minimum_age',
                'maximum_age',
            ],
        ];

        foreach ($fieldsToUnset as $prefix => $keys) {
            foreach ($keys as $key) {
                unset($formData[$prefix . $key]);
            }
        }
    }

    /**
     * Adds some values to the form data before insertion into the database.
     * Added values for new objects are: 'crdate', 'tstamp', 'pid' and
     * 'owner_feuser'.
     * For objects to update, just the 'tstamp' will be refreshed.
     *
     * @param array[] $formData form data, will be modified, must not be empty
     *
     * @return void
     */
    private function addAdministrativeData(array &$formData)
    {
        $formData['tstamp'] = $GLOBALS['SIM_EXEC_TIME'];
        // For existing records, updating the timestamp is sufficient.
        if ($this->getObjectUid() > 0) {
            return;
        }

        $user = self::getLoggedInUser();

        $formData['crdate'] = $GLOBALS['SIM_EXEC_TIME'];
        if ($user instanceof \Tx_Seminars_Model_FrontEndUser) {
            $formData['owner_feuser'] = $user->getUid();
            $eventPid = $user->getEventRecordsPid();
        } else {
            $formData['owner_feuser'] = 0;
            $eventPid = 0;
        }

        $formData['pid'] = $eventPid > 0 ? $eventPid : $this->getConfValueInteger('createEventsPID', 's_fe_editing');
    }

    /**
     * Checks the publish settings of the user and hides the event record if necessary.
     *
     * @param array[] $formData
     *        form data, will be modified if the seminar must be hidden corresponding to the publish settings of the user,
     *        must not be empty
     *
     * @return void
     */
    private function checkPublishSettings(array &$formData)
    {
        $user = self::getLoggedInUser();
        $publishSetting = $user instanceof \Tx_Seminars_Model_FrontEndUser
            ? $user->getPublishSetting() : \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY;
        $eventUid = $this->getObjectUid();
        $isNew = $eventUid === 0;

        $hideEditedObject = !$isNew && $publishSetting === \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_HIDE_EDITED;
        $hideNewObject = $isNew && $publishSetting > \Tx_Seminars_Model_FrontEndUserGroup::PUBLISH_IMMEDIATELY;

        if ($isNew) {
            $eventIsHidden = false;
        } else {
            /** @var \Tx_Seminars_Mapper_Event $mapper */
            $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);
            /** @var \Tx_Seminars_Model_Event $event */
            $event = $mapper->find($eventUid);
            $eventIsHidden = $event->isHidden();
        }

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
     * @param array[] $formData form data, must not be empty
     *
     * @return array modified form data, will not be empty
     *
     * @see unifyDecimalSeparators(), processAttachments(),
     *      purgeNonSeminarsFields(), addAdministrativeData()
     */
    public function modifyDataToInsert(array $formData): array
    {
        $modifiedFormData = $formData;

        $this->processAttachments($modifiedFormData);
        $this->purgeNonSeminarsFields($modifiedFormData);
        $this->unifyDecimalSeparators($modifiedFormData);
        $this->addAdministrativeData($modifiedFormData);
        $this->checkPublishSettings($modifiedFormData);
        $this->addCategoriesOfUser($modifiedFormData);

        $this->savedFormData = $modifiedFormData;

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
     * @param string[] $valueToCheck form data to check, must not be empty
     *
     * @return bool TRUE if the provided file is valid, FALSE otherwise
     */
    public function checkFile(array $valueToCheck): bool
    {
        $this->validationError = '';

        // If these values match, no files have been uploaded and we need no
        // further check.
        if ($valueToCheck['value'] == implode(',', $this->attachedFiles)) {
            return true;
        }

        $fileToCheck = array_pop(
            GeneralUtility::trimExplode(',', $valueToCheck['value'], true)
        );

        $this->checkFileSize($fileToCheck);
        $this->checkFileType($fileToCheck);

        // If there is a validation error, the upload has to be done again.
        if (
            ($this->validationError == '')
            && ($this->isTestMode() || $this->getFormCreator()->getValidationTool()->isAllValid())
        ) {
            $this->attachedFiles[] = $fileToCheck;
        } else {
            $this->purgeUploadedFile($fileToCheck);
        }

        return $this->validationError == '';
    }

    /**
     * Checks whether an uploaded file is of a valid type.
     *
     * @param string $fileName file name, must match an uploaded file, must not be empty
     *
     * @return void
     */
    private function checkFileType(string $fileName)
    {
        $allowedExtensions = $this->getConfValueString(
            'allowedExtensionsForUpload',
            's_fe_editing'
        );

        if (!preg_match('/^.+\\.(' . str_replace(',', '|', $allowedExtensions) . ')$/i', $fileName)) {
            $this->validationError = $this->translate('message_invalid_type') .
                ' ' . str_replace(',', ', ', $allowedExtensions) . '.';
        }
    }

    /**
     * Checks whether an uploaded file is not too large.
     *
     * @param string $fileName file name, must match an uploaded file, must not be empty
     *
     * @return void
     */
    private function checkFileSize(string $fileName)
    {
        $maximumFileSize = $GLOBALS['TYPO3_CONF_VARS']['BE']['maxFileSize'];

        $fileSize = filesize($fileName);

        if ($fileSize > ($maximumFileSize * 1024)) {
            $this->validationError = $this->translate('message_file_too_large') . ' ' . $maximumFileSize . 'kB.';
        }
    }

    /**
     * Deletes a file in the seminars upload directory and removes it from the
     * list of currently attached files.
     *
     * @param string $fileName file name, must match an uploaded file, must not be empty
     *
     * @return void
     */
    private function purgeUploadedFile(string $fileName)
    {
        @unlink(PATH_site . 'uploads/tx_seminars/' . $fileName);
        $keyToPurge = array_search($fileName, $this->attachedFiles, true);
        if ($keyToPurge !== false) {
            /** @var string|int $keyToPurge */
            unset($this->attachedFiles[$keyToPurge]);
        }
    }

    /**
     * Returns an error message if the provided file was invalid.
     *
     * @return string localized validation error message, will be empty if
     *                $this->validationError was empty
     */
    public function getFileUploadErrorMessage(): string
    {
        return $this->validationError;
    }

    /**
     * Retrieves the keys of the subparts which should be hidden in the event
     * editor.
     *
     * @return string[] the keys of the subparts which should be hidden in the
     *               event editor without the prefix FE_EDITOR_, will be empty
     *               if all subparts should be shown.
     */
    private function getHiddenSubparts(): array
    {
        /** @var \Tx_Oelib_Visibility_Tree $visibilityTree */
        $visibilityTree = GeneralUtility::makeInstance(
            \Tx_Oelib_Visibility_Tree::class,
            $this->createTemplateStructure()
        );

        $visibilityTree->makeNodesVisible($this->getFieldsToShow());
        return $visibilityTree->getKeysOfHiddenSubparts();
    }

    /**
     * Creates the template subpart structure.
     *
     * @return array the template's subpart structure for use with
     *               \Tx_Oelib_Visibility_Tree
     */
    private function createTemplateStructure(): array
    {
        return [
            'subtitle' => false,
            'title_right' => [
                'accreditation_number' => false,
                'credit_points' => false,
            ],
            'basic_information' => [
                'categories' => false,
                'event_type' => false,
                'cancelled' => false,
            ],
            'text_blocks' => [
                'teaser' => false,
                'description' => false,
                'additional_information' => false,
            ],
            'registration_information' => [
                'dates' => [
                    'events_dates' => [
                        'begin_date' => false,
                        'end_date' => false,
                    ],
                    'registration_dates' => [
                        'begin_date_registration' => false,
                        'deadline_early_bird' => false,
                        'deadline_registration' => false,
                    ],
                ],
                'attendance_information' => [
                    'registration_and_queue' => [
                        'needs_registration' => false,
                        'allows_multiple_registrations' => false,
                        'queue_size' => false,
                    ],
                    'attendees_number' => [
                        'attendees_min' => false,
                        'attendees_max' => false,
                        'offline_attendees' => false,
                    ],
                ],
                'target_groups' => false,
                'prices' => [
                    'regular_prices' => [
                        'price_regular' => false,
                        'price_regular_early' => false,
                        'price_regular_board' => false,
                        'payment_methods' => false,
                    ],
                    'special_prices' => [
                        'price_special' => false,
                        'price_special_early' => false,
                        'price_special_board' => false,
                    ],
                ],
            ],
            'place_information' => [
                'place_and_room' => [
                    'place' => false,
                    'room' => false,
                ],
                'lodging_and_food' => [
                    'lodgings' => false,
                    'foods' => false,
                ],
            ],
            'speakers' => false,
            'leaders' => false,
            'partner_tutor' => [
                'partners' => false,
                'tutors' => false,
            ],
            'checkbox_options' => [
                'checkboxes' => false,
                'uses_terms_2' => false,
            ],
            'attached_file_box' => false,
            'notes' => false,
        ];
    }

    /**
     * Returns the keys of the fields which should be shown in the FE editor.
     *
     * @return string[] the keys of the fields which should be shown, will be empty if all fields should be hidden
     */
    private function getFieldsToShow(): array
    {
        $fieldsToShow = GeneralUtility::trimExplode(
            ',',
            $this->getConfValueString(
                'displayFrontEndEditorFields',
                's_fe_editing'
            ),
            true
        );
        $this->removeCategoryIfNecessary($fieldsToShow);

        return $fieldsToShow;
    }

    /**
     * Returns whether front-end editing of the given related record type is
     * allowed.
     *
     * @param string[] $parameters the contents of the "params" child of the userobj node as key/value pairs
     *
     * @return bool TRUE if front-end editing of the given related record
     *                 type is allowed, FALSE otherwise
     */
    public function isFrontEndEditingOfRelatedRecordsAllowed(array $parameters): bool
    {
        $relatedRecordType = $parameters['relatedRecordType'];

        $frontEndUser = self::getLoggedInUser();
        $isFrontEndEditingAllowed = $this->getConfValueBoolean(
            'allowFrontEndEditingOf' . $relatedRecordType,
            's_fe_editing'
        );

        $auxiliaryPidFromSetup = $this->getConfValueBoolean('createAuxiliaryRecordsPID');
        $isAnAuxiliaryPidSet = ($frontEndUser->getAuxiliaryRecordsPid() > 0) || ($auxiliaryPidFromSetup > 0);

        return $isFrontEndEditingAllowed && $isAnAuxiliaryPidSet;
    }

    /**
     * Reads the list of required form fields from the configuration and stores
     * it in $this->requiredFormFields.
     *
     * @return void
     */
    private function setRequiredFormFields()
    {
        $this->requiredFormFields = GeneralUtility::trimExplode(
            ',',
            $this->getConfValueString(
                'requiredFrontEndEditorFields',
                's_fe_editing'
            )
        );

        $this->removeCategoryIfNecessary($this->requiredFormFields);
    }

    /**
     * Adds a class 'required' to the label of a field if it is required.
     *
     * @param Template $template the template in which the required markers should be set
     *
     * @return void
     */
    private function setRequiredFieldLabels(Template $template)
    {
        foreach ($this->getFieldsToShow() as $formField) {
            $template->setMarker(
                $formField . '_required',
                in_array($formField, $this->requiredFormFields, true)
                    ? ' class="required"'
                    : ''
            );
        }
    }

    /**
     * Checks whether a given field is required.
     *
     * @param string[] $field
     *        the field to check, the array must contain an element with the key
     *        'elementName' and a nonempty value for that key
     *
     * @return bool TRUE if the field is required, FALSE otherwise
     */
    private function isFieldRequired(array $field): bool
    {
        if ($field['elementName'] == '') {
            throw new \InvalidArgumentException('The given field name was empty.', 1333293167);
        }

        return in_array($field['elementName'], $this->requiredFormFields, true);
    }

    /**
     * Checks whether a given field needs to be filled in, but hasn't been
     * filled in yet.
     *
     * @param array[] $formData
     *        associative array containing the current value, with the key
     *        'value' and the name, with the key 'elementName', of the form
     *        field to check, must not be empty
     *
     * @return bool TRUE if this field is not empty or not required, FALSE
     *                 otherwise
     */
    public function validateString(array $formData): bool
    {
        if (!$this->isFieldRequired($formData)) {
            return true;
        }

        return trim($formData['value']) != '';
    }

    /**
     * Checks whether a given field needs to be filled in with a non-zero value,
     * but hasn't been filled in correctly yet.
     *
     * @param array[] $formData
     *        associative array containing the current value, with the key
     *        'value' and the name, with the key 'elementName', of the form
     *        field to check, must not be empty
     *
     * @return bool TRUE if this field is not zero or not required, FALSE
     *                 otherwise
     */
    public function validateInteger(array $formData): bool
    {
        if (!$this->isFieldRequired($formData)) {
            return true;
        }

        return ((int)$formData['value']) !== 0;
    }

    /**
     * Checks whether a given field needs to be filled in with a non-empty array,
     * but hasn't been filled in correctly yet.
     *
     * @param array[] $formData
     *        associative array containing the current value, with the key
     *        'value' and the name, with the key 'elementName', of the form
     *        field to check, must not be empty
     *
     * @return bool TRUE if this field is not zero or not required, FALSE
     *                 otherwise
     */
    public function validateCheckboxes(array $formData): bool
    {
        if (!$this->isFieldRequired($formData)) {
            return true;
        }

        return is_array($formData['value']) && !empty($formData['value']);
    }

    /**
     * Checks whether a given field needs to be filled in with a valid date,
     * but hasn't been filled in correctly yet.
     *
     * @param array[] $formData
     *        associative array containing the current value, with the key
     *        'value' and the name, with the key 'elementName', of the form
     *        field to check, must not be empty
     *
     * @return bool TRUE if this field contains a valid date or if this field
     *                 is not required, FALSE otherwise
     */
    public function validateDate(array $formData): bool
    {
        if (!$this->isFieldRequired($formData)) {
            return true;
        }

        return preg_match('/^[\\d:\\-\\/ ]+$/', $formData['value']) == 1;
    }

    /**
     * Checks whether a given field needs to be filled in with a valid price,
     * but hasn't been filled in correctly yet.
     *
     * @param array[] $formData
     *        associative array containing the current value, with the key
     *        'value' and the name, with the key 'elementName', of the form
     *        field to check, must not be empty
     *
     * @return bool TRUE if this field contains a valid price or if this
     *                 field is not required, FALSE otherwise
     */
    public function validatePrice(array $formData): bool
    {
        if (!$this->isFieldRequired($formData)) {
            return true;
        }

        return preg_match('/^\\d+([,.]\\d{1,2})?$/', $formData['value']) == 1;
    }

    /**
     * Sends the publishing e-mail to the reviewer if necessary.
     *
     * @return void
     */
    public function sendEMailToReviewer()
    {
        if ($this->publicationHash === '') {
            return;
        }
        $reviewer = $this->getReviewer();
        if ($reviewer === null) {
            return;
        }

        /** @var \Tx_Seminars_Mapper_Event $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);
        /** @var \Tx_Seminars_Model_Event $event */
        $event = $mapper->findByPublicationHash($this->publicationHash);

        if ($event !== null && $event->isHidden()) {
            /** @var \Tx_Oelib_Mail $eMail */
            $eMail = GeneralUtility::makeInstance(\Tx_Oelib_Mail::class);
            $eMail->addRecipient($reviewer);
            $eMail->setSender($this->getEmailSender());
            $eMail->setReplyTo(self::getLoggedInUser());
            $eMail->setSubject($this->translate('publish_event_subject'));
            $eMail->setMessage($this->createEMailContent($event));

            /** @var \Tx_Oelib_MailerFactory $mailerFactory */
            $mailerFactory = GeneralUtility::makeInstance(\Tx_Oelib_MailerFactory::class);
            $mailerFactory->getMailer()->send($eMail);
        }
    }

    /**
     * Gets the reviewer for new/edited records.
     *
     * @return BackEndUser|null
     */
    protected function getReviewer()
    {
        \Tx_Oelib_MapperRegistry::purgeInstance();
        return self::getLoggedInUser()->getReviewerFromGroup();
    }

    /**
     * Builds the content for the publishing e-mail to the reviewer.
     *
     * @param \Tx_Seminars_Model_Event $event
     *        the event to send the publication e-mail for
     *
     * @return string the e-mail body for the publishing e-mail, will not be
     *                empty
     */
    private function createEMailContent(\Tx_Seminars_Model_Event $event): string
    {
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
            'date',
            $beginDate,
            $markerPrefix,
            'wrapper_publish_event'
        );
        $this->setMarker(
            'description',
            $event->getDescription(),
            $markerPrefix
        );

        $this->setMarker('link', $this->createReviewUrl(), $markerPrefix);

        return $this->getSubpart('MAIL_PUBLISH_EVENT');
    }

    /**
     * Builds the URL for the reviewer e-mail.
     *
     * @return string the URL for the plain text e-mail, will not be empty
     */
    private function createReviewUrl(): string
    {
        $url = $this->cObj->typoLink_URL(
            [
                'parameter' => $this->getFrontEndController()->id . ','
                    . \Tx_Seminars_FrontEnd_PublishEvent::PUBLICATION_TYPE_NUMBER,
                'additionalParams' => GeneralUtility::implodeArrayForUrl(
                    'tx_seminars_publication',
                    ['hash' => $this->publicationHash],
                    '',
                    false,
                    true
                ),
                'type' => \Tx_Seminars_FrontEnd_PublishEvent::PUBLICATION_TYPE_NUMBER,
            ]
        );

        return GeneralUtility::locationHeaderUrl(preg_replace(['/\\[/', '/\\]/'], ['%5B', '%5D'], $url));
    }

    /**
     * Sends an additional notification email to the review if this is enabled in the configuration and if the event has been
     * newly created.
     *
     * @return void
     */
    public function sendAdditionalNotificationEmailToReviewer()
    {
        if (!self::getSeminarsConfiguration()->getAsBoolean('sendAdditionalNotificationEmailInFrontEndEditor')) {
            return;
        }
        $reviewer = $this->getReviewer();
        if ($reviewer === null) {
            return;
        }

        /** @var \Tx_Oelib_Mail $eMail */
        $eMail = GeneralUtility::makeInstance(\Tx_Oelib_Mail::class);
        $eMail->addRecipient($reviewer);
        $eMail->setSender($this->getEmailSender());
        $eMail->setReplyTo(self::getLoggedInUser());
        $eMail->setSubject($this->translate('save_event_subject'));
        $eMail->setMessage($this->createAdditionalEmailContent());

        /** @var \Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(\Tx_Oelib_MailerFactory::class);
        $mailerFactory->getMailer()->send($eMail);
    }

    /**
     * Builds the content for the additional notification e-mail to the reviewer.
     *
     * @return string the e-mail body for the notification e-mail, will not be empty
     */
    protected function createAdditionalEmailContent(): string
    {
        $this->getTemplateCode(true);
        $this->setLabels();

        $markerPrefix = 'save_event';

        $title = $this->savedFormData['title'] ?? '';
        $this->setMarker('title', $title, $markerPrefix);
        $description = $this->savedFormData['description'] ?? '';
        $this->setMarker('description', $description, $markerPrefix);

        $beginDateAsTimeStamp = isset($this->savedFormData['begin_date']) ? (int)$this->savedFormData['begin_date'] : 0;
        $beginDate = ($beginDateAsTimeStamp !== 0)
            ? strftime($this->getConfValueString('dateFormatYMD'), $beginDateAsTimeStamp) : '';
        $this->setOrDeleteMarkerIfNotEmpty(
            'date',
            $beginDate,
            $markerPrefix,
            'wrapper_save_event'
        );

        return $this->getSubpart('MAIL_SAVE_EVENT');
    }

    /**
     * Creates a new place record.
     *
     * This function is intended to be called via an AJAX FORMidable event.
     *
     * @param \tx_mkforms_forms_Base $form
     *
     * @return array[] calls to be executed on the client
     */
    public static function createNewPlace(\tx_mkforms_forms_Base $form): array
    {
        /** @var \formidableajax $ajax */
        $ajax = $form->getMajix();
        $formData = $ajax->getParams();
        $validationErrors = self::validatePlace(
            $form,
            [
                'title' => $formData['newPlace_title'],
                'address' => $formData['newPlace_address'],
                'zip' => $formData['newPlace_zip'],
                'city' => $formData['newPlace_city'],
                'country' => $formData['newPlace_country'],
                'homepage' => $formData['newPlace_homepage'],
                'directions' => $formData['newPlace_directions'],
            ]
        );
        if (!empty($validationErrors)) {
            return [
                $form->majixExecJs(
                    'alert("' . implode('\\n', $validationErrors) . '");'
                ),
            ];
        }

        /** @var \Tx_Seminars_Model_Place $place */
        $place = GeneralUtility::makeInstance(\Tx_Seminars_Model_Place::class);
        $place->setData(self::createBasicAuxiliaryData());
        self::setPlaceData($place, 'newPlace_', $formData);
        $place->markAsDirty();
        /** @var \Tx_Seminars_Mapper_Place $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Place::class);
        $mapper->save($place);

        /** @var \formidable_mainrenderlet $renderlet */
        $renderlet = $form->aORenderlets['editPlaceButton'];
        /** @var array $editButtonConfiguration */
        $editButtonConfiguration = $form->_navConf($renderlet->sXPath);
        $editButtonConfiguration['name'] = 'editPlaceButton_' . $place->getUid();
        $editButtonConfiguration['onclick']['userobj']['php'] = '
            return ' . self::class . '::showEditPlaceModalBox($this, ' . $place->getUid() . ');
        ';
        /** @var \tx_mkforms_widgets_button_Main $editButton */
        $editButton = $form->_makeRenderlet($editButtonConfiguration, $renderlet->sXPath);
        $editButton->includeScripts();
        $editButtonHTML = $editButton->_render();

        /** @var \tx_mkforms_widgets_modalbox_Main $modalBox */
        $modalBox = $form->aORenderlets['newPlaceModalBox'];
        return [
            $modalBox->majixCloseBox(),
            $form->majixExecJs(
                'TYPO3.seminars.appendPlaceInEditor(' . $place->getUid() . ', "' .
                addcslashes($place->getTitle(), '"\\') . '", {
                        "name": "' . addcslashes($editButtonHTML['name'], '"\\') . '",
                        "id": "' . addcslashes($editButtonHTML['id'], '"\\') . '",
                        "value": "' . addcslashes($editButtonHTML['value'], '"\\') . '"
                    });'
            ),
        ];
    }

    /**
     * Updates an existing place record.
     *
     * This function is intended to be called via an AJAX FORMidable event.
     *
     * @param \tx_mkforms_forms_Base $form
     *
     * @return array[] calls to be executed on the client
     */
    public static function updatePlace(\tx_mkforms_forms_Base $form): array
    {
        /** @var \formidableajax $ajax */
        $ajax = $form->getMajix();
        $formData = $ajax->getParams();
        $frontEndUser = self::getLoggedInUser();
        /** @var \Tx_Seminars_Mapper_Place $placeMapper */
        $placeMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Place::class);

        try {
            /** @var \Tx_Seminars_Model_Place $place */
            $place = $placeMapper->find((int)$formData['editPlace_uid']);
        } catch (\Exception $exception) {
            return $form->majixExecJs(
                'alert("The place with the given UID does not exist.");'
            );
        }

        if ($place->getOwner() !== $frontEndUser) {
            return $form->majixExecJs(
                'alert("You are not allowed to edit this place.");'
            );
        }

        $validationErrors = self::validatePlace(
            $form,
            [
                'title' => $formData['editPlace_title'],
                'address' => $formData['editPlace_address'],
                'zip' => $formData['editPlace_zip'],
                'city' => $formData['editPlace_city'],
                'country' => $formData['editPlace_country'],
                'homepage' => $formData['editPlace_homepage'],
                'directions' => $formData['editPlace_directions'],
            ]
        );
        if (!empty($validationErrors)) {
            return $form->majixExecJs(
                'alert("' . implode('\\n', $validationErrors) . '");'
            );
        }

        self::setPlaceData($place, 'editPlace_', $formData);
        $placeMapper->save($place);

        $htmlId = 'tx_seminars_pi1_seminars_place_label_' . $place->getUid();

        /** @var \tx_mkforms_widgets_modalbox_Main $modalBox */
        $modalBox = $form->aORenderlets['editPlaceModalBox'];
        return [
            $modalBox->majixCloseBox(),
            $form->majixExecJs(
                'TYPO3.seminars.updateAuxiliaryRecordInEditor("' . $htmlId . '", "' .
                addcslashes($place->getTitle(), '"\\') . '")'
            ),
        ];
    }

    /**
     * Validates the entered data for a place.
     *
     * @param \tx_mkforms_forms_Base $form
     * @param array[] $formData
     *        the entered form data, the key must be stripped of the
     *        "newPlace_"/"editPlace_" prefix
     *
     * @return string[] the error messages, will be empty if there are no validation errors
     */
    private static function validatePlace(\tx_mkforms_forms_Base $form, array $formData): array
    {
        $validationErrors = [];

        $keys = [
            'title',
            'address',
            'zip',
            'city',
            'homepage',
            'directions',
        ];
        foreach ($keys as $key) {
            if (\trim($formData[$key]) === '' && self::isPlaceFieldRequired($key)) {
                $validationErrors[] = $form->getConfigXML()->getLLLabel(
                    'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:message_empty' . ucfirst($key)
                );
            }
        }
        $key = 'country';
        if (((int)$formData[$key] === 0) && self::isPlaceFieldRequired($key)) {
            $validationErrors[] = $form->getConfigXML()->getLLLabel(
                'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:message_empty' . ucfirst($key)
            );
        }

        return $validationErrors;
    }

    /**
     * Checks whether the place field with the key $key is required.
     *
     * @param string $key the key of the field to check, must not be empty
     *
     * @return bool TRUE if the field with the key $key is required,
     *                 FALSE otherwise
     */
    private static function isPlaceFieldRequired(string $key): bool
    {
        if ($key == '') {
            throw new \InvalidArgumentException('$key must not be empty.');
        }

        $requiredFields = self::getSeminarsConfiguration()->getAsTrimmedArray('requiredFrontEndEditorPlaceFields');
        // The field "title" always is required.
        $requiredFields[] = 'title';

        return in_array($key, $requiredFields, true);
    }

    /**
     * Sets the data of a place model based on the data given in $formData.
     *
     * @param \Tx_Seminars_Model_Place $place the place model to set the data
     * @param string $prefix the prefix of the form fields in $formData
     * @param array[] $formData the form data to use for setting the place data
     *
     * @return void
     */
    private static function setPlaceData(
        \Tx_Seminars_Model_Place $place,
        string $prefix,
        array $formData
    ) {
        $countryUid = (int)$formData[$prefix . 'country'];
        if ($countryUid > 0) {
            try {
                /** @var \Tx_Oelib_Mapper_Country $mapper */
                $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Country::class);
                /** @var Country $country */
                $country = $mapper->find($countryUid);
            } catch (\Exception $exception) {
                $country = null;
            }
        } else {
            $country = null;
        }

        $place->setTitle(trim(strip_tags($formData[$prefix . 'title'])));
        $place->setAddress(trim(strip_tags($formData[$prefix . 'address'])));
        $place->setZip(trim(strip_tags($formData[$prefix . 'zip'])));
        $place->setCity(trim(strip_tags($formData[$prefix . 'city'])));
        $place->setCountry($country);
        $place->setHomepage(trim(strip_tags($formData[$prefix . 'homepage'])));
        $place->setDirections(trim($formData[$prefix . 'directions']));
        $place->setNotes(trim(strip_tags($formData[$prefix . 'notes'])));
    }

    /**
     * Shows a modalbox containing a form for editing an existing place record.
     *
     * @param \tx_mkforms_forms_Base $form
     * @param int $placeUid the UID of the place to edit, must be > 0
     *
     * @return array[] calls to be executed on the client
     */
    public static function showEditPlaceModalBox(\tx_mkforms_forms_Base $form, int $placeUid): array
    {
        if ($placeUid <= 0) {
            return $form->majixExecJs('alert("$placeUid must be >= 0.");');
        }

        /** @var \Tx_Seminars_Mapper_Place $placeMapper */
        $placeMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Place::class);

        try {
            /** @var \Tx_Seminars_Model_Place $place */
            $place = $placeMapper->find((int)$placeUid);
        } catch (\Tx_Oelib_Exception_NotFound $exception) {
            return $form->majixExecJs(
                'alert("A place with the given UID does not exist.");'
            );
        }

        $frontEndUser = self::getLoggedInUser();
        if ($place->getOwner() !== $frontEndUser) {
            return $form->majixExecJs(
                'alert("You are not allowed to edit this place.");'
            );
        }

        try {
            $country = $place->getCountry();
            if ($country) {
                $countryUid = $country->getUid();
            } else {
                $countryUid = 0;
            }
        } catch (\Tx_Oelib_Exception_NotFound $exception) {
            $countryUid = 0;
        }

        $fields = [
            'uid' => $place->getUid(),
            'title' => $place->getTitle(),
            'address' => $place->getAddress(),
            'zip' => $place->getZip(),
            'city' => $place->getCity(),
            'country' => $countryUid,
            'homepage' => $place->getHomepage(),
            'directions' => $place->getDirections(),
            'notes' => $place->getNotes(),
        ];

        foreach ($fields as $key => $value) {
            /** @var \formidable_mainrenderlet $renderlet */
            $renderlet = $form->aORenderlets['editPlace_' . $key];
            $renderlet->setValue($value);
        }

        $form->getRenderer()->_setDisplayLabels(true);
        /** @var \tx_mkforms_widgets_modalbox_Main $modalBox */
        $modalBox = $form->aORenderlets['editPlaceModalBox'];
        $result = $modalBox->majixShowBox();
        $form->getRenderer()->_setDisplayLabels(false);

        return $result;
    }

    /**
     * Creates the basic data for a FE-entered auxiliary record (owner, PID).
     *
     * @return array the basic data as an associative array, will not be empty
     */
    private static function createBasicAuxiliaryData(): array
    {
        $owner = self::getLoggedInUser();
        $ownerPageUid = $owner->getAuxiliaryRecordsPid();

        $pageUid = ($ownerPageUid > 0) ? $ownerPageUid : self::getSeminarsConfiguration(
        )->getAsInteger('createAuxiliaryRecordsPID');

        return [
            'owner' => $owner,
            'pid' => $pageUid,
        ];
    }

    /**
     * Creates a new speaker record.
     *
     * This function is intended to be called via an AJAX FORMidable event.
     *
     * @param mixed[] $formData
     * @param \tx_mkforms_forms_Base $form
     *
     * @return array[] calls to be executed on the client
     */
    public function createNewSpeaker(array $formData, \tx_mkforms_forms_Base $form): array
    {
        $formData = self::removePathFromWidgetData($formData, $form);
        $validationErrors = self::validateSpeaker(
            $form,
            ['title' => $formData['newSpeaker_title']]
        );
        if (!empty($validationErrors)) {
            return [
                $form->majixExecJs(
                    'alert("' . implode('\\n', $validationErrors) . '");'
                ),
            ];
        }

        /** @var \Tx_Seminars_Model_Speaker $speaker */
        $speaker = GeneralUtility::makeInstance(\Tx_Seminars_Model_Speaker::class);

        self::createBasicAuxiliaryData();

        $speaker->setData(
            array_merge(
                self::createBasicAuxiliaryData(),
                ['skills' => new \Tx_Oelib_List()]
            )
        );
        self::setSpeakerData($speaker, 'newSpeaker_', $formData);
        $speaker->markAsDirty();
        /** @var \Tx_Seminars_Mapper_Speaker $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Speaker::class);
        $mapper->save($speaker);

        // refresh all speaker listers
        /** @var array $results */
        $results = $this->repaintSpeakers($form);
        // close box
        /** @var \tx_mkforms_widgets_modalbox_Main $modalBox */
        $modalBox = $form->aORenderlets['newSpeakerModalBox'];
        $results[] = $modalBox->majixCloseBox();

        return $results;
    }

    /**
     * Updates an existing speaker record.
     *
     * This function is intended to be called via an AJAX FORMidable event.
     *
     * @param mixed[] $formData
     * @param \tx_mkforms_forms_Base $form
     *
     * @return array[] calls to be executed on the client
     */
    public function updateSpeaker(array $formData, \tx_mkforms_forms_Base $form): array
    {
        $formData = self::removePathFromWidgetData($formData, $form);
        $frontEndUser = self::getLoggedInUser();
        /** @var \Tx_Seminars_Mapper_Speaker $speakerMapper */
        $speakerMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Speaker::class);

        try {
            /** @var \Tx_Seminars_Model_Speaker $speaker */
            $speaker = $speakerMapper->find((int)$formData['editSpeaker_uid']);
        } catch (\Exception $exception) {
            return $form->majixExecJs(
                'alert("The speaker with the given UID does not exist.");'
            );
        }

        if ($speaker->getOwner() !== $frontEndUser) {
            return $form->majixExecJs(
                'alert("You are not allowed to edit this speaker.");'
            );
        }

        $validationErrors = self::validateSpeaker(
            $form,
            ['title' => $formData['editSpeaker_title']]
        );
        if (!empty($validationErrors)) {
            return [
                $form->majixExecJs(
                    'alert("' . implode('\\n', $validationErrors) . '");'
                ),
            ];
        }

        self::setSpeakerData($speaker, 'editSpeaker_', $formData);
        $speakerMapper->save($speaker);

        $results = $this->repaintSpeakers($form);
        /** @var \tx_mkforms_widgets_modalbox_Main $modalBox */
        $modalBox = $form->aORenderlets['editSpeakerModalBox'];
        $results[] = $modalBox->majixCloseBox();

        return $results;
    }

    /**
     * @param \tx_mkforms_forms_Base $form
     *
     * @return array
     */
    protected function repaintSpeakers(\tx_mkforms_forms_Base $form): array
    {
        $speakerTypes = [
            'speaker',
            'leader',
            'partner',
            'tutor',
        ];

        $results = [];
        // refresh all speaker listers
        foreach ($speakerTypes as $speakerType) {
            $widget = $form->getWidget($speakerType . 's');
            if ($widget instanceof \tx_mkforms_widgets_lister_Main) {
                $results[] = $widget->repaintFirst();
            }
        }
        return $results;
    }

    /**
     * Validates the entered data for a speaker.
     *
     * @param \tx_mkforms_forms_Base $form
     * @param array[] $formData
     *        the entered form data, the key must be stripped of the
     *        "newSpeaker_"/"editSpeaker_" prefix
     *
     * @return string[] the error messages, will be empty if there are no validation errors
     */
    private static function validateSpeaker(
        \tx_mkforms_forms_Base $form,
        array $formData
    ): array {
        $validationErrors = [];
        if (trim($formData['title']) == '') {
            $validationErrors[] = $form->getConfigXML()->getLLLabel(
                'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:message_emptyName'
            );
        }

        return $validationErrors;
    }

    /**
     * Sets the data of a speaker model based on the data given in $formData.
     *
     * @param \Tx_Seminars_Model_Speaker $speaker
     *        the speaker model to set the data for
     * @param string $prefix the prefix of the form fields in $formData
     * @param array[] $formData the form data to use for setting the speaker data
     *
     * @return void
     */
    private static function setSpeakerData(\Tx_Seminars_Model_Speaker $speaker, string $prefix, array $formData)
    {
        /** @var \Tx_Seminars_Mapper_Skill $skillMapper */
        $skillMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Skill::class);
        $skills = new \Tx_Oelib_List();

        if (is_array($formData[$prefix . 'skills'])) {
            foreach ($formData[$prefix . 'skills'] as $rawUid) {
                $safeUid = (int)$rawUid;
                if ($safeUid > 0) {
                    /** @var \Tx_Seminars_Model_Skill $skill */
                    $skill = $skillMapper->find($safeUid);
                    $skills->add($skill);
                }
            }
        }

        $speaker->setSkills($skills);

        $speaker->setName(trim(strip_tags($formData[$prefix . 'title'])));
        $speaker->setGender((int)$formData[$prefix . 'gender']);
        $speaker->setOrganization($formData[$prefix . 'organization']);
        $speaker->setHomepage(trim(strip_tags($formData[$prefix . 'homepage'])));
        $speaker->setDescription(trim($formData[$prefix . 'description']));
        $speaker->setNotes(trim(strip_tags($formData[$prefix . 'notes'])));
        $speaker->setAddress(trim(strip_tags($formData[$prefix . 'address'])));
        $speaker->setPhoneWork(trim(strip_tags($formData[$prefix . 'phone_work'])));
        $speaker->setPhoneHome(trim(strip_tags($formData[$prefix . 'phone_home'])));
        $speaker->setPhoneMobile(trim(strip_tags($formData[$prefix . 'phone_mobile'])));
        $speaker->setFax(trim(strip_tags($formData[$prefix . 'fax'])));
        $speaker->setEMailAddress(trim(strip_tags($formData[$prefix . 'email'])));
        $speaker->setCancelationPeriod((int)$formData[$prefix . 'cancelation_period']);
    }

    /**
     * Shows a modalbox containing a form for editing an existing speaker record.
     *
     * @param mixed $params
     * @param \tx_mkforms_forms_Base $form
     *
     * @return array[] calls to be executed on the client
     */
    public static function openEditSpeakerModalBox(
        array $params,
        \tx_mkforms_forms_Base $form
    ): array {
        $speakerId = empty($params['uid']) ? 0 : (int)$params['uid'];
        return self::showEditSpeakerModalBox($form, $speakerId);
    }

    /**
     * Shows a modalbox containing a form for editing an existing speaker record.
     *
     * @param \tx_mkforms_forms_Base $form
     * @param int $speakerUid the UID of the speaker to edit, must be > 0
     *
     * @return array[] calls to be executed on the client
     */
    public static function showEditSpeakerModalBox(
        \tx_mkforms_forms_Base $form,
        int $speakerUid
    ): array {
        if ($speakerUid <= 0) {
            return $form->majixExecJs('alert("$speakerUid must be >= 0.");');
        }

        /** @var \Tx_Seminars_Mapper_Speaker $speakerMapper */
        $speakerMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Speaker::class);

        try {
            /** @var \Tx_Seminars_Model_Speaker $speaker */
            $speaker = $speakerMapper->find((int)$speakerUid);
        } catch (\Tx_Oelib_Exception_NotFound $exception) {
            return $form->majixExecJs(
                'alert("A speaker with the given UID does not exist.");'
            );
        }

        $frontEndUser = self::getLoggedInUser();
        if ($speaker->getOwner() !== $frontEndUser) {
            return $form->majixExecJs(
                'alert("You are not allowed to edit this speaker.");'
            );
        }

        $fields = [
            'uid' => $speaker->getUid(),
            'title' => $speaker->getName(),
            'gender' => $speaker->getGender(),
            'organization' => $speaker->getOrganization(),
            'homepage' => $speaker->getHomepage(),
            'description' => $speaker->getDescription(),
            'notes' => $speaker->getNotes(),
            'address' => $speaker->getAddress(),
            'phone_work' => $speaker->getPhoneWork(),
            'phone_home' => $speaker->getPhoneHome(),
            'phone_mobile' => $speaker->getPhoneMobile(),
            'fax' => $speaker->getFax(),
            'email' => $speaker->getEMailAddress(),
            'cancelation_period' => $speaker->getCancelationPeriod(),
        ];

        foreach ($fields as $key => $value) {
            /** @var \formidable_mainrenderlet $renderlet */
            $renderlet = $form->aORenderlets['editSpeakerModalBox__editSpeaker_' . $key];
            $renderlet->setValue($value);
        }

        $result = [];

        $form->getRenderer()->_setDisplayLabels(true);
        /** @var \tx_mkforms_widgets_modalbox_Main $modalBox */
        $modalBox = $form->aORenderlets['editSpeakerModalBox'];
        $result[] = $modalBox->majixShowBox();
        $form->getRenderer()->_setDisplayLabels(false);

        /** @var \tx_mkforms_widgets_checkbox_Main $checkboxRenderlet */
        $checkboxRenderlet = $form->aORenderlets['editSpeakerModalBox__editSpeaker_skills'];
        $result[] = $checkboxRenderlet->majixCheckNone();

        /** @var \Tx_Seminars_Model_Skill $skill */
        foreach ($speaker->getSkills() as $skill) {
            $result[] = $checkboxRenderlet->majixCheckItem($skill->getUid());
        }

        return $result;
    }

    /**
     * Creates a new checkbox record.
     *
     * This function is intended to be called via an AJAX FORMidable event.
     *
     * @param \tx_mkforms_forms_Base $form
     *
     * @return array[] calls to be executed on the client
     */
    public static function createNewCheckbox(\tx_mkforms_forms_Base $form): array
    {
        /** @var \formidableajax $ajax */
        $ajax = $form->getMajix();
        $formData = $ajax->getParams();
        $validationErrors = self::validateCheckbox(
            $form,
            ['title' => $formData['newCheckbox_title']]
        );
        if (!empty($validationErrors)) {
            return [
                $form->majixExecJs(
                    'alert("' . implode('\\n', $validationErrors) . '");'
                ),
            ];
        }

        /** @var \Tx_Seminars_Model_Checkbox $checkbox */
        $checkbox = GeneralUtility::makeInstance(\Tx_Seminars_Model_Checkbox::class);
        $checkbox->setData(self::createBasicAuxiliaryData());
        self::setCheckboxData($checkbox, 'newCheckbox_', $formData);
        $checkbox->markAsDirty();
        /** @var \Tx_Seminars_Mapper_Checkbox $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Checkbox::class);
        $mapper->save($checkbox);

        /** @var \formidable_mainrenderlet $renderlet */
        $renderlet = $form->aORenderlets['editCheckboxButton'];
        /** @var array $editButtonConfiguration */
        $editButtonConfiguration = $form->_navConf($renderlet->sXPath);
        $editButtonConfiguration['name'] = 'editCheckboxButton_' . $checkbox->getUid();
        $editButtonConfiguration['onclick']['userobj']['php'] = '
            return ' . self::class . '::showEditCheckboxModalBox($this, ' . $checkbox->getUid() . ');
        ';
        /** @var \tx_mkforms_widgets_button_Main $editButton */
        $editButton = $form->_makeRenderlet($editButtonConfiguration, $renderlet->sXPath);
        $editButton->includeScripts();
        $editButtonHTML = $editButton->_render();

        /** @var \tx_mkforms_widgets_modalbox_Main $modalBox */
        $modalBox = $form->aORenderlets['newCheckboxModalBox'];

        return [
            $modalBox->majixCloseBox(),
            $form->majixExecJs(
                'TYPO3.seminars.appendCheckboxInEditor(' . $checkbox->getUid() . ', "' .
                addcslashes($checkbox->getTitle(), '"\\') . '", {
                        "name": "' . addcslashes($editButtonHTML['name'], '"\\') . '",
                        "id": "' . addcslashes($editButtonHTML['id'], '"\\') . '",
                        "value": "' . addcslashes($editButtonHTML['value'], '"\\') . '"
                    });'
            ),
        ];
    }

    /**
     * Updates an existing checkbox record.
     *
     * This function is intended to be called via an AJAX FORMidable event.
     *
     * @param \tx_mkforms_forms_Base $form
     *
     * @return array[] calls to be executed on the client
     */
    public static function updateCheckbox(\tx_mkforms_forms_Base $form): array
    {
        /** @var \formidableajax $ajax */
        $ajax = $form->getMajix();
        $formData = $ajax->getParams();
        $frontEndUser = self::getLoggedInUser();
        /** @var \Tx_Seminars_Mapper_Checkbox $checkboxMapper */
        $checkboxMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Checkbox::class);

        try {
            /** @var \Tx_Seminars_Model_Checkbox $checkbox */
            $checkbox = $checkboxMapper->find((int)$formData['editCheckbox_uid']);
        } catch (\Exception $exception) {
            return $form->majixExecJs(
                'alert("The checkbox with the given UID does not exist.");'
            );
        }

        if ($checkbox->getOwner() !== $frontEndUser) {
            return $form->majixExecJs(
                'alert("You are not allowed to edit this checkbox.");'
            );
        }

        $validationErrors = self::validateCheckbox(
            $form,
            ['title' => $formData['editCheckbox_title']]
        );
        if (!empty($validationErrors)) {
            return $form->majixExecJs(
                'alert("' . implode('\\n', $validationErrors) . '");'
            );
        }

        self::setCheckboxData($checkbox, 'editCheckbox_', $formData);
        $checkboxMapper->save($checkbox);

        $htmlId = 'tx_seminars_pi1_seminars_checkbox_label_' . $checkbox->getUid();

        /** @var \tx_mkforms_widgets_modalbox_Main $modalBox */
        $modalBox = $form->aORenderlets['editCheckboxModalBox'];

        return [
            $modalBox->majixCloseBox(),
            $form->majixExecJs(
                'TYPO3.seminars.updateAuxiliaryRecordInEditor("' . $htmlId . '", "' .
                addcslashes($checkbox->getTitle(), '"\\') . '")'
            ),
        ];
    }

    /**
     * Validates the entered data for a checkbox.
     *
     * @param \tx_mkforms_forms_Base $form
     * @param array[] $formData
     *        the entered form data, the key must be stripped of the
     *        "newCheckbox_"/"editCheckbox_" prefix
     *
     * @return string[] the error messages, will be empty if there are no validation errors
     */
    private static function validateCheckbox(
        \tx_mkforms_forms_Base $form,
        array $formData
    ): array {
        $validationErrors = [];
        if (trim($formData['title']) == '') {
            $validationErrors[] = $form->getConfigXML()->getLLLabel(
                'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:message_emptyTitle'
            );
        }

        return $validationErrors;
    }

    /**
     * Sets the data of a checkbox model based on the data given in $formData.
     *
     * @param \Tx_Seminars_Model_Checkbox $checkbox the checkbox model to set the data
     * @param string $prefix the prefix of the form fields in $formData
     * @param array[] $formData the form data to use for setting the checkbox data
     *
     * @return void
     */
    private static function setCheckboxData(
        \Tx_Seminars_Model_Checkbox $checkbox,
        string $prefix,
        array $formData
    ) {
        $checkbox->setTitle($formData[$prefix . 'title']);
    }

    /**
     * Shows a modalbox containing a form for editing an existing checkbox record.
     *
     * @param \tx_mkforms_forms_Base $form
     * @param int $checkboxUid the UID of the checkbox to edit, must be > 0
     *
     * @return array[] calls to be executed on the client
     */
    public static function showEditCheckboxModalBox(
        \tx_mkforms_forms_Base $form,
        int $checkboxUid
    ): array {
        if ($checkboxUid <= 0) {
            return $form->majixExecJs('alert("$checkboxUid must be >= 0.");');
        }

        /** @var \Tx_Seminars_Mapper_Checkbox $checkboxMapper */
        $checkboxMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Checkbox::class);

        try {
            /** @var \Tx_Seminars_Model_Checkbox $checkbox */
            $checkbox = $checkboxMapper->find((int)$checkboxUid);
        } catch (\Tx_Oelib_Exception_NotFound $exception) {
            return $form->majixExecJs(
                'alert("A checkbox with the given UID does not exist.");'
            );
        }

        $frontEndUser = self::getLoggedInUser();
        if ($checkbox->getOwner() !== $frontEndUser) {
            return $form->majixExecJs(
                'alert("You are not allowed to edit this checkbox.");'
            );
        }

        $fields = [
            'uid' => $checkbox->getUid(),
            'title' => $checkbox->getTitle(),
        ];

        foreach ($fields as $key => $value) {
            /** @var \formidable_mainrenderlet $renderlet */
            $renderlet = $form->aORenderlets['editCheckbox_' . $key];
            $renderlet->setValue($value);
        }

        $form->getRenderer()->_setDisplayLabels(true);
        /** @var \tx_mkforms_widgets_modalbox_Main $modalBox */
        $modalBox = $form->aORenderlets['editCheckboxModalBox'];
        $result = $modalBox->majixShowBox();
        $form->getRenderer()->_setDisplayLabels(false);

        return $result;
    }

    /**
     * Creates a new target group record.
     *
     * This function is intended to be called via an AJAX FORMidable event.
     *
     * @param \tx_mkforms_forms_Base $form
     *
     * @return array[] calls to be executed on the client
     */
    public static function createNewTargetGroup(\tx_mkforms_forms_Base $form): array
    {
        /** @var \formidableajax $ajax */
        $ajax = $form->getMajix();
        $formData = $ajax->getParams();
        $validationErrors = self::validateTargetGroup(
            $form,
            [
                'title' => $formData['newTargetGroup_title'],
                'minimum_age' => $formData['newTargetGroup_minimum_age'],
                'maximum_age' => $formData['newTargetGroup_maximum_age'],
            ]
        );
        if (!empty($validationErrors)) {
            return [
                $form->majixExecJs(
                    'alert("' . implode('\\n', $validationErrors) . '");'
                ),
            ];
        }

        /** @var \Tx_Seminars_Model_TargetGroup $targetGroup */
        $targetGroup = GeneralUtility::makeInstance(\Tx_Seminars_Model_TargetGroup::class);
        $targetGroup->setData(self::createBasicAuxiliaryData());
        self::setTargetGroupData($targetGroup, 'newTargetGroup_', $formData);
        $targetGroup->markAsDirty();
        /** @var \Tx_Seminars_Mapper_TargetGroup $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_TargetGroup::class);
        $mapper->save($targetGroup);

        /** @var \formidable_mainrenderlet $renderlet */
        $renderlet = $form->aORenderlets['editTargetGroupButton'];
        /** @var array $editButtonConfiguration */
        $editButtonConfiguration = $form->_navConf($renderlet->sXPath);
        $editButtonConfiguration['name'] = 'editTargetGroupButton_' . $targetGroup->getUid();
        $editButtonConfiguration['onclick']['userobj']['php'] = '
            return ' . self::class . '::showEditTargetGroupModalBox($this, ' . $targetGroup->getUid() . ');
        ';
        /** @var \tx_mkforms_widgets_button_Main $editButton */
        $editButton = $form->_makeRenderlet($editButtonConfiguration, $renderlet->sXPath);
        $editButton->includeScripts();
        $editButtonHTML = $editButton->_render();

        /** @var \tx_mkforms_widgets_modalbox_Main $modalBox */
        $modalBox = $form->aORenderlets['newTargetGroupModalBox'];

        return [
            $modalBox->majixCloseBox(),
            $form->majixExecJs(
                'TYPO3.seminars.appendTargetGroupInEditor(' . $targetGroup->getUid() . ', "' .
                addcslashes($targetGroup->getTitle(), '"\\') . '", {
                        "name": "' . addcslashes($editButtonHTML['name'], '"\\') . '",
                        "id": "' . addcslashes($editButtonHTML['id'], '"\\') . '",
                        "value": "' . addcslashes($editButtonHTML['value'], '"\\') . '"
                    });'
            ),
        ];
    }

    /**
     * Updates an existing target group record.
     *
     * This function is intended to be called via an AJAX FORMidable event.
     *
     * @param \tx_mkforms_forms_Base $form
     *
     * @return array[] calls to be executed on the client
     */
    public static function updateTargetGroup(\tx_mkforms_forms_Base $form): array
    {
        /** @var \formidableajax $ajax */
        $ajax = $form->getMajix();
        $formData = $ajax->getParams();
        $frontEndUser = self::getLoggedInUser();
        /** @var \Tx_Seminars_Mapper_TargetGroup $targetGroupMapper */
        $targetGroupMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_TargetGroup::class);

        try {
            /** @var \Tx_Seminars_Model_TargetGroup $targetGroup */
            $targetGroup = $targetGroupMapper->find((int)$formData['editTargetGroup_uid']);
        } catch (\Exception $exception) {
            return $form->majixExecJs(
                'alert("The target group with the given UID does not exist.");'
            );
        }

        if ($targetGroup->getOwner() !== $frontEndUser) {
            return $form->majixExecJs(
                'alert("You are not allowed to edit this target group.");'
            );
        }

        $validationErrors = self::validateTargetGroup(
            $form,
            [
                'title' => $formData['editTargetGroup_title'],
                'minimum_age' => $formData['editTargetGroup_minimum_age'],
                'maximum_age' => $formData['editTargetGroup_maximum_age'],
            ]
        );
        if (!empty($validationErrors)) {
            return $form->majixExecJs(
                'alert("' . implode('\\n', $validationErrors) . '");'
            );
        }

        self::setTargetGroupData($targetGroup, 'editTargetGroup_', $formData);
        $targetGroupMapper->save($targetGroup);

        $htmlId = 'tx_seminars_pi1_seminars_target_group_label_' . $targetGroup->getUid();

        /** @var \tx_mkforms_widgets_modalbox_Main $modalBox */
        $modalBox = $form->aORenderlets['editTargetGroupModalBox'];

        return [
            $modalBox->majixCloseBox(),
            $form->majixExecJs(
                'TYPO3.seminars.updateAuxiliaryRecordInEditor("' . $htmlId . '", "' .
                addcslashes($targetGroup->getTitle(), '"\\') . '")'
            ),
        ];
    }

    /**
     * Validates the entered data for a target group.
     *
     * @param \tx_mkforms_forms_Base $form
     * @param array[] $formData
     *        the entered form data, the key must be stripped of the
     *        "newTargetGroup_"/"editTargetGroup_" prefix
     *
     * @return string[] the error messages, will be empty if there are no validation errors
     */
    private static function validateTargetGroup(
        \tx_mkforms_forms_Base $form,
        array $formData
    ): array {
        $validationErrors = [];
        if (trim($formData['title']) == '') {
            $validationErrors[] = $form->getConfigXML()->getLLLabel(
                'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:message_emptyTitle'
            );
        }
        if (
            \preg_match('/^(\\d*)$/', \trim($formData['minimum_age']))
            && \preg_match('/^(\\d*)$/', \trim($formData['maximum_age']))
        ) {
            $minimumAge = $formData['minimum_age'];
            $maximumAge = $formData['maximum_age'];

            if (($minimumAge > 0) && ($maximumAge > 0) && ($minimumAge > $maximumAge)) {
                $validationErrors[] = $form->getConfigXML()->getLLLabel(
                    'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:' .
                    'message_targetGroupMaximumAgeSmallerThanMinimumAge'
                );
            }
        } else {
            $validationErrors[] = $form->getConfigXML()->getLLLabel(
                'LLL:EXT:seminars/Resources/Private/Language/locallang.xlf:message_noTargetGroupAgeNumber'
            );
        }

        return $validationErrors;
    }

    /**
     * Sets the data of a target group model based on the data given in
     * $formData.
     *
     * @param \Tx_Seminars_Model_TargetGroup $targetGroup
     *        the target group model to set the data
     * @param string $prefix the prefix of the form fields in $formData
     * @param array[] $formData
     *        the form data to use for setting the target group data
     *
     * @return void
     */
    private static function setTargetGroupData(
        \Tx_Seminars_Model_TargetGroup $targetGroup,
        string $prefix,
        array $formData
    ) {
        $targetGroup->setTitle($formData[$prefix . 'title']);
        $targetGroup->setMinimumAge((int)$formData[$prefix . 'minimum_age']);
        $targetGroup->setMaximumAge((int)$formData[$prefix . 'maximum_age']);
    }

    /**
     * Shows a modalbox containing a form for editing an existing target group
     * record.
     *
     * @param \tx_mkforms_forms_Base $form
     * @param int $targetGroupUid
     *        the UID of the target group to edit, must be > 0
     *
     * @return array[] calls to be executed on the client
     */
    public static function showEditTargetGroupModalBox(
        \tx_mkforms_forms_Base $form,
        int $targetGroupUid
    ): array {
        if ($targetGroupUid <= 0) {
            return $form->majixExecJs('alert("$targetGroupUid must be >= 0.");');
        }

        /** @var \Tx_Seminars_Mapper_TargetGroup $targetGroupMapper */
        $targetGroupMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_TargetGroup::class);

        try {
            /** @var \Tx_Seminars_Model_TargetGroup $targetGroup */
            $targetGroup = $targetGroupMapper->find((int)$targetGroupUid);
        } catch (\Tx_Oelib_Exception_NotFound $exception) {
            return $form->majixExecJs(
                'alert("A target group with the given UID does not exist.");'
            );
        }

        $frontEndUser = self::getLoggedInUser();
        if ($targetGroup->getOwner() !== $frontEndUser) {
            return $form->majixExecJs(
                'alert("You are not allowed to edit this target group.");'
            );
        }

        $minimumAge = ($targetGroup->getMinimumAge() > 0)
            ? $targetGroup->getMinimumAge() : '';
        $maximumAge = ($targetGroup->getMaximumAge() > 0)
            ? $targetGroup->getMaximumAge() : '';

        $fields = [
            'uid' => $targetGroup->getUid(),
            'title' => $targetGroup->getTitle(),
            'minimum_age' => $minimumAge,
            'maximum_age' => $maximumAge,
        ];

        foreach ($fields as $key => $value) {
            /** @var \formidable_mainrenderlet $renderlet */
            $renderlet = $form->aORenderlets['editTargetGroup_' . $key];
            $renderlet->setValue($value);
        }

        $form->getRenderer()->_setDisplayLabels(true);

        /** @var \tx_mkforms_widgets_modalbox_Main $modalBox */
        $modalBox = $form->aORenderlets['editTargetGroupModalBoxBox'];
        $result = $modalBox->majixShowBox();
        $form->getRenderer()->_setDisplayLabels(false);

        return $result;
    }

    /**
     * Provides data items for the list of countries.
     *
     * @return array[] items as an array with the keys "caption" (for the title) and "value" (for the UID)
     */
    public static function populateListCountries(): array
    {
        $result = [];

        /** @var \Tx_Oelib_Mapper_Country $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Country::class);
        /** @var Country $country */
        foreach ($mapper->findAll('cn_short_local') as $country) {
            $result[] = [
                'caption' => $country->getLocalShortName(),
                'value' => $country->getUid(),
            ];
        }

        return $result;
    }

    /**
     * Provides data items for the list of skills.
     *
     * @return array[] items as an array with the keys "caption" (for the title) and "value" (for the UID)
     */
    public static function populateListSkills(): array
    {
        /** @var \Tx_Seminars_Mapper_Skill $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Seminars_Mapper_Skill::class);
        $skills = $mapper->findAll('title ASC');

        return self::makeListToFormidableList($skills);
    }

    /**
     * Returns an array of caption value pairs for formidable checkboxes.
     *
     * @param \Tx_Oelib_List $models
     *        List of models to show in the checkboxes, may be empty
     *
     * @return array[] items as an array with the keys "caption" (for the title)
     *         and "value" (for the UID), will be empty if an empty model list
     *         was provided
     */
    public static function makeListToFormidableList(\Tx_Oelib_List $models): array
    {
        if ($models->isEmpty()) {
            return [];
        }

        $result = [];

        /** @var AbstractModel|Titled $model */
        foreach ($models as $model) {
            $result[] = [
                'caption' => $model->getTitle(),
                'value' => $model->getUid(),
            ];
        }

        return $result;
    }

    /**
     * Returns the UID of the preselected organizer.
     *
     * @return int the UID of the preselected organizer; if more than one
     *                 organizer is available, zero will be returned
     */
    public function getPreselectedOrganizer(): int
    {
        $availableOrganizers = $this->populateListOrganizers();
        if (count($availableOrganizers) !== 1) {
            return 0;
        }

        $organizerData = array_pop($availableOrganizers);

        return $organizerData['value'];
    }

    /**
     * Returns the allowed PIDs for the auxiliary records.
     *
     * @return int PID for the auxiliary records, may be empty
     */
    private function getPidForAuxiliaryRecords(): int
    {
        $auxiliaryRecordsPid = self::getLoggedInUser()->getAuxiliaryRecordsPid();
        if ($auxiliaryRecordsPid === 0) {
            $auxiliaryRecordsPid = self::getSeminarsConfiguration()->getAsInteger('createAuxiliaryRecordsPID');
        }

        return $auxiliaryRecordsPid;
    }

    /**
     * Adds the default categories of the currently logged-in user to the
     * event.
     *
     * Note: This affects only new records. Existing records (with a UID) will
     * not be changed.
     *
     * @param array[] $formData
     *        all entered form data with the field names as keys, will be
     *        modified, must not be empty
     *
     * @return void
     */
    private function addCategoriesOfUser(array &$formData)
    {
        $eventUid = $this->getObjectUid();
        if ($eventUid > 0) {
            return;
        }
        $frontEndUser = self::getLoggedInUser();
        if (!$frontEndUser instanceof \Tx_Seminars_Model_FrontEndUser || !$frontEndUser->hasDefaultCategories()) {
            return;
        }

        $formData['categories'] = $frontEndUser->getDefaultCategoriesFromGroup()->getUids();
    }

    /**
     * Removes the category field if the user has default categories set.
     *
     * @param string[] $formFields
     *        the fields which should be checked for category, will be modified, may be empty
     *
     * @return void
     */
    private function removeCategoryIfNecessary(array &$formFields)
    {
        if (!in_array('categories', $formFields, true)) {
            return;
        }

        $frontEndUser = self::getLoggedInUser();
        if ($frontEndUser instanceof \Tx_Seminars_Model_FrontEndUser && $frontEndUser->hasDefaultCategories()) {
            $categoryKey = (string)\array_search('categories', $formFields, true);
            unset($formFields[$categoryKey]);
        }
    }

    /**
     * Gets the Configuration for plugin.tx_seminars_pi1.
     *
     * @return \Tx_Oelib_Configuration
     */
    protected static function getSeminarsConfiguration(): \Tx_Oelib_Configuration
    {
        return \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars_pi1');
    }

    /**
     * Fakes a form data value that is usually provided by the FORMidable object.
     *
     * This function is for testing purposes.
     *
     * @param string $key column name of the 'tx_seminars_seminars' table as key, must not be empty
     * @param mixed $value faked value
     *
     * @return void
     */
    public function setSavedFormValue(string $key, $value)
    {
        $this->savedFormData[$key] = $value;
    }

    protected function getEmailSender(): \Tx_Oelib_Interface_MailRole
    {
        $systemEmailFromBuilder = GeneralUtility::makeInstance(SystemEmailFromBuilder::class);
        if ($systemEmailFromBuilder->canBuild()) {
            $sender = $systemEmailFromBuilder->build();
        } else {
            $sender = self::getLoggedInUser();
        }
        return $sender;
    }
}
