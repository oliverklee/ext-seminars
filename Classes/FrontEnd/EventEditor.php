<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\FrontEnd;

use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Email\SystemEmailFromBuilder;
use OliverKlee\Oelib\Interfaces\Configuration;
use OliverKlee\Oelib\Interfaces\MailRole;
use OliverKlee\Oelib\Mapper\CountryMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Oelib\Model\BackEndUser as OelibBackEndUser;
use OliverKlee\Oelib\Templating\Template;
use OliverKlee\Oelib\Visibility\Tree;
use OliverKlee\Seminars\Configuration\Traits\SharedPluginConfiguration;
use OliverKlee\Seminars\Email\EmailBuilder;
use OliverKlee\Seminars\Mapper\CategoryMapper;
use OliverKlee\Seminars\Mapper\CheckboxMapper;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Mapper\EventTypeMapper;
use OliverKlee\Seminars\Mapper\FoodMapper;
use OliverKlee\Seminars\Mapper\FrontEndUserMapper;
use OliverKlee\Seminars\Mapper\LodgingMapper;
use OliverKlee\Seminars\Mapper\OrganizerMapper;
use OliverKlee\Seminars\Mapper\PaymentMethodMapper;
use OliverKlee\Seminars\Mapper\PlaceMapper;
use OliverKlee\Seminars\Mapper\SkillMapper;
use OliverKlee\Seminars\Mapper\SpeakerMapper;
use OliverKlee\Seminars\Mapper\TargetGroupMapper;
use OliverKlee\Seminars\Model\Checkbox;
use OliverKlee\Seminars\Model\Event;
use OliverKlee\Seminars\Model\FrontEndUser;
use OliverKlee\Seminars\Model\FrontEndUserGroup;
use OliverKlee\Seminars\Model\Interfaces\Titled;
use OliverKlee\Seminars\Model\Place;
use OliverKlee\Seminars\Model\Speaker;
use OliverKlee\Seminars\Model\TargetGroup;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * This class is a controller which allows creating and editing events on the FE.
 *
 * @deprecated #1544 will be removed in seminars 5.0
 */
class EventEditor extends AbstractEditor
{
    use SharedPluginConfiguration;

    /**
     * @var string stores a validation error message if there was one
     */
    private $validationError = '';

    /**
     * @var string the prefix used for every subpart in the FE editor
     */
    private const SUBPART_PREFIX = 'fe_editor';

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
        $this->setRequiredFormFields();
    }

    /**
     * There are more than one init calls.
     * Mkforms calls the init for code behinds and oelib initializes the class, too.
     *
     * @param array|\tx_mkforms_forms_IForm|null $configuration
     */
    public function init($configuration = null): void
    {
        if (is_array($configuration)) {
            parent::init($configuration);
        }
    }

    /**
     * Declares the additional data handler for m:n relations.
     */
    private function declareDataHandler(): void
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

        return $this->renderHtmlWithLabels($template);
    }

    /**
     * Returns the complete HTML for the FE editor.
     *
     * @param Template $template holds the raw HTML output, must be already processed by FORMidable
     *
     * @return string HTML for the FE editor
     */
    private function renderHtmlWithLabels(Template $template): string
    {
        foreach (['label_save', 'label_save_and_back'] as $label) {
            $template->setMarker($label, $this->translate($label));
        }

        return $template->getSubpart();
    }

    /**
     * Provides data items for the list of available categories.
     *
     * @return array<int, array{caption: string, value: int}>
     */
    public function populateListCategories(): array
    {
        $mapper = MapperRegistry::get(CategoryMapper::class);
        $categories = $mapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        return self::makeListToFormidableList($categories);
    }

    /**
     * Provides data items for the list of available event types.
     *
     * @return array<int, array{caption: string, value: int}>
     */
    public function populateListEventTypes(): array
    {
        $mapper = MapperRegistry::get(EventTypeMapper::class);
        $eventTypes = $mapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        return self::makeListToFormidableList($eventTypes);
    }

    /**
     * Provides data items for the list of available lodgings.
     *
     * @return array<int, array{caption: string, value: int}>
     */
    public function populateListLodgings(): array
    {
        $mapper = MapperRegistry::get(LodgingMapper::class);
        $lodgings = $mapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        return self::makeListToFormidableList($lodgings);
    }

    /**
     * Provides data items for the list of available foods.
     *
     * @return array<int, array{caption: string, value: int}>
     */
    public function populateListFoods(): array
    {
        $mapper = MapperRegistry::get(FoodMapper::class);
        $foods = $mapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        return self::makeListToFormidableList($foods);
    }

    /**
     * Provides data items for the list of available payment methods.
     *
     * @return array<int, array{caption: string, value: int}>
     */
    public function populateListPaymentMethods(): array
    {
        $mapper = MapperRegistry::get(PaymentMethodMapper::class);
        $paymentMethods = $mapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        return self::makeListToFormidableList($paymentMethods);
    }

    /**
     * Provides data items for the list of available organizers.
     *
     * @return array<int, array{caption: string, value: int}>
     */
    public function populateListOrganizers(): array
    {
        $frontEndUser = self::getLoggedInUser();
        if (!$frontEndUser instanceof FrontEndUser) {
            return [];
        }

        if ($frontEndUser->hasDefaultOrganizers()) {
            $organizers = $frontEndUser->getDefaultOrganizers();
        } else {
            $mapper = MapperRegistry::get(OrganizerMapper::class);
            $organizers = $mapper->findByPageUid((string)$this->getPidForAuxiliaryRecords(), 'title ASC');
        }

        return self::makeListToFormidableList($organizers);
    }

    protected static function getLoggedInUser(): ?FrontEndUser
    {
        $userUid = FrontEndLoginManager::getInstance()->getLoggedInUserUid();
        return $userUid > 0 ? MapperRegistry::get(FrontEndUserMapper::class)->find($userUid) : null;
    }

    /**
     * Provides data items for the list of available places.
     *
     * @param array<string, string> $parameters
     *
     * @return array<int, array{caption: string, value: int}>
     */
    public function populateListPlaces(array $parameters = [], ?\tx_mkforms_forms_Base $form = null): array
    {
        $result = [];

        $placeMapper = MapperRegistry::get(PlaceMapper::class);
        $places = $placeMapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        $frontEndUser = self::getLoggedInUser();

        /** @var Place $place */
        foreach ($places as $place) {
            $result[] = [
                'caption' => $place->getTitle(),
                'value' => $place->getUid(),
                'wrapitem' => '|</td><td>&nbsp;',
            ];
        }

        return $result;
    }

    /**
     * Provides data items for the list of available speakers.
     *
     * @param array<string, string> $parameters
     *
     * @return array<int, array{caption: string, value: int}>
     */
    public function populateListSpeakers(array $parameters = [], ?\tx_mkforms_forms_Base $form = null): array
    {
        $result = [];

        $speakerMapper = MapperRegistry::get(SpeakerMapper::class);
        $speakers = $speakerMapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        $frontEndUser = self::getLoggedInUser();

        $type = (string)($parameters['type'] ?? '');
        if (empty($parameters['lister'])) {
            $isLister = false;
            $activeSpeakers = '';
        } else {
            $isLister = true;
            $activeSpeakers = $form->getDataHandler()->getStoredData(strtolower($type) . 's');
        }

        /** @var Speaker $speaker */
        foreach ($speakers as $speaker) {
            // the new method to list the speakers
            if ($isLister) {
                $result[] = [
                    'uid' => $speaker->getUid(),
                    'selected' => GeneralUtility::inList($activeSpeakers, (string)$speaker->getUid()) ? 1 : 0,
                    'name' => $speaker->getName(),
                ];
                continue;
            }
            $result[] = [
                'caption' => $speaker->getName(),
                'value' => $speaker->getUid(),
                'wrapitem' => '|</td><td>&nbsp;',
            ];
        }

        return $result;
    }

    /**
     * Provides data items for the list of available checkboxes.
     *
     * @param array<string, string> $parameters
     *
     * @return array<int, array{caption: string, value: int}>
     */
    public function populateListCheckboxes(array $parameters = [], ?\tx_mkforms_forms_Base $form = null): array
    {
        $result = [];

        $checkboxMapper = MapperRegistry::get(CheckboxMapper::class);
        $checkboxes = $checkboxMapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        $frontEndUser = self::getLoggedInUser();

        /** @var Checkbox $checkbox */
        foreach ($checkboxes as $checkbox) {
            $result[] = [
                'caption' => $checkbox->getTitle(),
                'value' => $checkbox->getUid(),
                'wrapitem' => '|</td><td>&nbsp;',
            ];
        }

        return $result;
    }

    /**
     * Provides data items for the list of available target groups.
     *
     * @param array<string, string> $parameters
     *
     * @return array<int, array{caption: string, value: int}>
     */
    public function populateListTargetGroups(array $parameters = [], ?\tx_mkforms_forms_Base $form = null): array
    {
        $result = [];

        $targetGroupMapper = MapperRegistry::get(TargetGroupMapper::class);
        $targetGroups = $targetGroupMapper->findByPageUid($this->getPidForAuxiliaryRecords(), 'title ASC');

        $frontEndUser = self::getLoggedInUser();

        /** @var TargetGroup $targetGroup */
        foreach ($targetGroups as $targetGroup) {
            $result[] = [
                'caption' => $targetGroup->getTitle(),
                'value' => $targetGroup->getUid(),
                'wrapitem' => '|</td><td>&nbsp;',
            ];
        }

        return $result;
    }

    /**
     * Gets the URL of the page that should be displayed when an event has been successfully created.
     *
     * An URL of the FE editor's page is returned if "submit_and_stay" was clicked.
     *
     * @return string complete URL of the FE page with a message or, if "submit_and_stay" was clicked, of the current page
     */
    public function getEventSuccessfullySavedUrl(): string
    {
        $pageId = $this->getConfValueInteger('eventSuccessfullySavedPID', 's_fe_editing');

        return GeneralUtility::locationHeaderUrl($this->cObj->typoLink_URL(['parameter' => $pageId]));
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
        /** @var LegacyEvent|null $event */
        $event = LegacyEvent::fromUid($uid, true);

        if ($uid > 0 && !($event instanceof LegacyEvent)) {
            return 'message_wrongSeminarNumber';
        }

        $user = self::getLoggedInUser();
        if ($uid > 0 && $event instanceof LegacyEvent) {
            $hasAccess = $event->isOwnerFeUser();
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
     * Changes all potential decimal separators (commas and dots) in price fields to dots.
     *
     * @param array<string, string|int|array<int, string|int>> $formData all entered form data, will be modified
     */
    private function unifyDecimalSeparators(array &$formData): void
    {
        $priceFields = [
            'price_regular',
            'price_regular_early',
            'price_special',
            'price_special_early',
        ];

        foreach ($priceFields as $key) {
            if (isset($formData[$key])) {
                $formData[$key] = str_replace(',', '.', $formData[$key]);
            }
        }
    }

    /**
     * Removes all form data elements that are no fields in the seminars table.
     *
     * @param array<string, string|int|array<int, string|int>> $formData form data, will be modified
     */
    private function purgeNonSeminarsFields(array &$formData): void
    {
        /** @var array<string, array<int, string>> $fieldsToUnset */
        $fieldsToUnset = [
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
                'email',
                'cancelation_period',
                'uid',
            ],
            'newCheckbox_' => ['title'],
            'editCheckbox_' => ['title', 'uid'],
            'newTargetGroup_' => ['title', 'uid', 'minimum_age', 'maximum_age'],
            'editTargetGroup_' => ['title', 'uid', 'minimum_age', 'maximum_age'],
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
     * @param array<string, string|int|array<int, string|int>> $formData form data, will be modified, must not be empty
     */
    private function addAdministrativeData(array &$formData): void
    {
        $formData['tstamp'] = $GLOBALS['SIM_EXEC_TIME'];
        // For existing records, updating the timestamp is sufficient.
        if ($this->getObjectUid() > 0) {
            return;
        }

        $user = self::getLoggedInUser();

        $formData['crdate'] = $GLOBALS['SIM_EXEC_TIME'];
        if ($user instanceof FrontEndUser) {
            $formData['owner_feuser'] = $user->getUid();
            $eventPid = $user->getEventRecordsPid();
        } else {
            $formData['owner_feuser'] = 0;
            $eventPid = 0;
        }

        $formData['pid'] = $eventPid > 0 ? $eventPid : $this->getConfValueInteger('createEventsPID', 's_fe_editing');
    }

    /**
     * Checks the publication settings of the user and hides the event record if necessary.
     *
     * @param array<string, string|int|array<int, string|int>> $formData form data, will be modified
     *        if the seminar must be hidden corresponding to the publish settings of the user, must not be empty
     *
     * @deprecated #1543 will be removed in seminars 5.0
     */
    private function checkPublishSettings(array &$formData): void
    {
        $user = self::getLoggedInUser();
        $publishSetting = $user instanceof FrontEndUser
            ? $user->getPublishSetting() : FrontEndUserGroup::PUBLISH_IMMEDIATELY;
        $eventUid = $this->getObjectUid();
        $isNew = $eventUid === 0;

        $hideEditedObject = !$isNew && $publishSetting === FrontEndUserGroup::PUBLISH_HIDE_EDITED;
        $hideNewObject = $isNew && $publishSetting > FrontEndUserGroup::PUBLISH_IMMEDIATELY;

        if ($isNew) {
            $eventIsHidden = false;
        } else {
            $mapper = MapperRegistry::get(EventMapper::class);
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
     * @param array<string, string|int|array<int, string|int>> $formData form data, must not be empty
     *
     * @return array modified form data, will not be empty
     *
     * @see unifyDecimalSeparators(), purgeNonSeminarsFields(), addAdministrativeData()
     */
    public function modifyDataToInsert(array $formData): array
    {
        $modifiedFormData = $formData;

        $this->purgeNonSeminarsFields($modifiedFormData);
        $this->unifyDecimalSeparators($modifiedFormData);
        $this->addAdministrativeData($modifiedFormData);
        $this->checkPublishSettings($modifiedFormData);
        $this->addCategoriesOfUser($modifiedFormData);

        $this->savedFormData = $modifiedFormData;

        return $modifiedFormData;
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
        $visibilityTree = GeneralUtility::makeInstance(Tree::class, $this->createTemplateStructure());
        $visibilityTree->makeNodesVisible($this->getFieldsToShow());

        return $visibilityTree->getKeysOfHiddenSubparts();
    }

    /**
     * Creates the template subpart structure.
     *
     * @return array the template's subpart structure for use with Tree
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
                        'payment_methods' => false,
                    ],
                    'special_prices' => [
                        'price_special' => false,
                        'price_special_early' => false,
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
        /** @var array<int, non-empty-string> $fieldsToShow */
        $fieldsToShow = GeneralUtility::trimExplode(
            ',',
            $this->getConfValueString('displayFrontEndEditorFields', 's_fe_editing'),
            true
        );
        $this->removeCategoryIfNecessary($fieldsToShow);

        return $fieldsToShow;
    }

    /**
     * Reads the list of required form fields from the configuration and stores
     * it in $this->requiredFormFields.
     */
    private function setRequiredFormFields(): void
    {
        $this->requiredFormFields = GeneralUtility::trimExplode(
            ',',
            $this->getConfValueString('requiredFrontEndEditorFields', 's_fe_editing')
        );

        $this->removeCategoryIfNecessary($this->requiredFormFields);
    }

    /**
     * Adds a class 'required' to the label of a field if it is required.
     *
     * @param Template $template the template in which the required markers should be set
     */
    private function setRequiredFieldLabels(Template $template): void
    {
        foreach ($this->getFieldsToShow() as $formField) {
            $template->setMarker(
                $formField . '_required',
                in_array($formField, $this->requiredFormFields, true) ? ' class="required"' : ''
            );
        }
    }

    /**
     * Checks whether a given field is required.
     *
     * @param string[] $field the field to check, the array must contain an element with the key
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
     * @param array<string, string|int|array<int, string|int>> $formData associative array containing the current
     *        value, with the key 'value' and the name, with the key 'elementName', of the form
     *        field to check, must not be empty
     *
     * @return bool TRUE if this field is not empty or not required, FALSE otherwise
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
     * @param array<string, string|int|array<int, string|int>> $formData associative array containing the current
     *        value, with the key 'value' and the name, with the key 'elementName', of the form
     *        field to check, must not be empty
     *
     * @return bool TRUE if this field is not zero or not required, FALSE otherwise
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
     * @param array<string, string|int|array<int, string|int>> $formData associative array containing the current
     *        value, with the key 'value' and the name, with the key 'elementName', of the form
     *        field to check, must not be empty
     *
     * @return bool TRUE if this field is not zero or not required, FALSE otherwise
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
     * @param array<string, string|int|array<int, string|int>> $formData associative array containing the current
     *        value, with the key 'value' and the name, with the key 'elementName', of the form
     *        field to check, must not be empty
     *
     * @return bool TRUE if this field contains a valid date or if this field is not required, FALSE otherwise
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
     * @param array<string, string|int|array<int, string|int>> $formData associative array containing the current
     *        value, with the key 'value' and the name, with the key 'elementName', of the form
     *        field to check, must not be empty
     *
     * @return bool TRUE if this field contains a valid price or if this field is not required, FALSE otherwise
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
     * @deprecated #1543 will be removed in seminars 5.0
     */
    public function sendEmailToReviewer(): void
    {
        if ($this->publicationHash === '') {
            return;
        }
        $reviewer = $this->getReviewer();
        if ($reviewer === null) {
            return;
        }

        $event = MapperRegistry::get(EventMapper::class)->findByPublicationHash($this->publicationHash);
        if ($event instanceof Event && $event->isHidden()) {
            $sender = $this->getEmailSender();
            $loggedInUser = self::getLoggedInUser();

            GeneralUtility::makeInstance(EmailBuilder::class)
                ->to($reviewer)
                ->from($sender)
                ->replyTo($loggedInUser)
                ->subject($this->translate('publish_event_subject'))
                ->text($this->createEmailContent($event))
                ->build()->send();
        }
    }

    /**
     * Gets the reviewer for new/edited records.
     */
    protected function getReviewer(): ?OelibBackEndUser
    {
        MapperRegistry::purgeInstance();
        return self::getLoggedInUser()->getReviewerFromGroup();
    }

    /**
     * Builds the content for the publishing e-mail to the reviewer.
     *
     * @param Event $event the event to send the publication e-mail for
     *
     * @return string the e-mail body for the publishing e-mail, will not be empty
     *
     * @deprecated #1543 will be removed in seminars 5.0
     */
    private function createEmailContent(Event $event): string
    {
        $this->getTemplateCode(true);
        $this->setLabels();

        $markerPrefix = 'publish_event';

        if ($event->hasBeginDate()) {
            $beginDate = \strftime($this->getDateFormat(), $event->getBeginDateAsUnixTimeStamp());
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
     * @deprecated #1543 will be removed in seminars 5.0
     *
     * @return string the URL for the plain text e-mail, will not be empty
     */
    private function createReviewUrl(): string
    {
        $url = $this->cObj->typoLink_URL(
            [
                'parameter' => $this->getFrontEndController()->id . ','
                    . EventPublication::PUBLICATION_TYPE_NUMBER,
                'additionalParams' => GeneralUtility::implodeArrayForUrl(
                    'tx_seminars_publication',
                    ['hash' => $this->publicationHash],
                    '',
                    false,
                    true
                ),
                'type' => EventPublication::PUBLICATION_TYPE_NUMBER,
            ]
        );

        return GeneralUtility::locationHeaderUrl(preg_replace(['/\\[/', '/\\]/'], ['%5B', '%5D'], $url));
    }

    /**
     * Sends an additional notification email to the review if this is enabled in the configuration and if the event has
     * been newly created.
     *
     * @deprecated #1543 will be removed in seminars 5.0
     */
    public function sendAdditionalNotificationEmailToReviewer(): void
    {
        if (!self::getSeminarsConfiguration()->getAsBoolean('sendAdditionalNotificationEmailInFrontEndEditor')) {
            return;
        }
        $reviewer = $this->getReviewer();
        if ($reviewer === null) {
            return;
        }

        $sender = $this->getEmailSender();
        $loggedInUser = self::getLoggedInUser();

        GeneralUtility::makeInstance(EmailBuilder::class)
            ->to($reviewer)
            ->from($sender)
            ->replyTo($loggedInUser)
            ->subject($this->translate('save_event_subject'))
            ->text($this->createAdditionalEmailContent())
            ->build()->send();
    }

    /**
     * Builds the content for the additional notification e-mail to the reviewer.
     *
     * @deprecated #1543 will be removed in seminars 5.0
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
        $beginDate = $beginDateAsTimeStamp !== 0 ? \strftime($this->getDateFormat(), $beginDateAsTimeStamp) : '';
        $this->setOrDeleteMarkerIfNotEmpty(
            'date',
            $beginDate,
            $markerPrefix,
            'wrapper_save_event'
        );

        return $this->getSubpart('MAIL_SAVE_EVENT');
    }

    /**
     * Provides data items for the list of countries.
     *
     * @return array[] items as an array with the keys "caption" (for the title) and "value" (for the UID)
     */
    public static function populateListCountries(): array
    {
        $result = [];

        foreach (MapperRegistry::get(CountryMapper::class)->findAll('cn_short_local') as $country) {
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
     * @return array<int, array{caption: string, value: int}>
     */
    public static function populateListSkills(): array
    {
        $skills = MapperRegistry::get(SkillMapper::class)->findAll('title ASC');

        return self::makeListToFormidableList($skills);
    }

    /**
     * Returns an array of caption value pairs for formidable checkboxes.
     *
     * @param Collection<AbstractModel&Titled> $models models to show in the checkboxes, may be empty
     *
     * @return array<int, array{caption: string, value: int}>
     */
    public static function makeListToFormidableList(Collection $models): array
    {
        if ($models->isEmpty()) {
            return [];
        }

        $result = [];

        foreach ($models as $model) {
            // @phpstan-ignore-next-line We are checking for contract violations here.
            if (!$model instanceof Titled) {
                throw new \InvalidArgumentException(
                    'All elements in $models must implement the interface Titled.',
                    1656254936
                );
            }

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
     * @return int the UID of the preselected organizer; if more than one organizer is available, zero will be returned
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
     * Adds the default categories of the currently logged-in user to the event.
     *
     * Note: This affects only new records. Existing records (with a UID) will not be changed.
     *
     * @param array<string, string|int|array<int, string|int>> $formData all entered form data
     *        with the field names as keys, will be modified, must not be empty
     */
    private function addCategoriesOfUser(array &$formData): void
    {
        $eventUid = $this->getObjectUid();
        if ($eventUid > 0) {
            return;
        }
        $frontEndUser = self::getLoggedInUser();
        if (!$frontEndUser instanceof FrontEndUser || !$frontEndUser->hasDefaultCategories()) {
            return;
        }

        $formData['categories'] = $frontEndUser->getDefaultCategoriesFromGroup()->getUids();
    }

    /**
     * Removes the category field if the user has default categories set.
     *
     * @param string[] $formFields the fields which should be checked for category, will be modified, may be empty
     */
    private function removeCategoryIfNecessary(array &$formFields): void
    {
        if (!in_array('categories', $formFields, true)) {
            return;
        }

        $frontEndUser = self::getLoggedInUser();
        if ($frontEndUser instanceof FrontEndUser && $frontEndUser->hasDefaultCategories()) {
            $categoryKey = (string)\array_search('categories', $formFields, true);
            unset($formFields[$categoryKey]);
        }
    }

    /**
     * Gets the Configuration for plugin.tx_seminars_pi1.
     */
    protected static function getSeminarsConfiguration(): Configuration
    {
        return ConfigurationRegistry::get('plugin.tx_seminars_pi1');
    }

    /**
     * Fakes a form data value that is usually provided by the FORMidable object.
     *
     * This function is for testing purposes.
     *
     * @param string $key column name of the 'tx_seminars_seminars' table as key, must not be empty
     * @param mixed $value faked value
     */
    public function setSavedFormValue(string $key, $value): void
    {
        $this->savedFormData[$key] = $value;
    }

    protected function getEmailSender(): MailRole
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
