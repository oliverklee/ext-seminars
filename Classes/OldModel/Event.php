<?php

declare(strict_types=1);

use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Model\FrontEndUser;
use OliverKlee\Oelib\Templating\TemplateHelper;
use OliverKlee\Seminars\Model\Traits\EventEmailSenderTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

/**
 * This class represents a seminar (or similar event).
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_OldModel_Event extends \Tx_Seminars_OldModel_AbstractTimeSpan
{
    use EventEmailSenderTrait;

    /**
     * the same as the class name
     *
     * @var string
     */
    public $prefixId = \Tx_Seminars_OldModel_Event::class;

    /**
     * @var string the name of the SQL table this class corresponds to
     */
    protected static $tableName = 'tx_seminars_seminars';

    /**
     * @var bool
     */
    private $registrationsHaveBeenRetrieved = false;

    /**
     * @var array[]
     */
    private $registrations = [];

    /**
     * the number of all attendances
     *
     * @var int
     */
    protected $numberOfAttendances = 0;

    /**
     * the number of paid attendances
     *
     * @var int
     */
    protected $numberOfAttendancesPaid = 0;

    /**
     * the number of attendances on the registration queue
     *
     * @var int
     */
    protected $numberOfAttendancesOnQueue = 0;

    /**
     * whether the statistics have been already calculate
     *
     * @var bool
     */
    protected $statisticsHaveBeenCalculated = false;

    /**
     * will be null if this is not a date record
     *
     * @var \Tx_Seminars_OldModel_Event|null
     */
    private $topic = null;

    /**
     * @return \Tx_Seminars_OldModel_Event|null
     */
    public function getTopic()
    {
        if ($this->topic instanceof \Tx_Seminars_OldModel_Event) {
            return $this->topic;
        }
        if (!$this->isEventDate()) {
            return null;
        }

        $topic = $this->loadTopic();
        // Avoid infinite loops due to date records that have been converted to a topic or single event.
        if ($topic instanceof \Tx_Seminars_OldModel_Event && !$topic->isEventDate()) {
            $this->setTopic($topic);
        }

        return $this->topic;
    }

    /**
     * @param Tx_Seminars_OldModel_Event $topic
     *
     * @return void
     */
    public function setTopic(\Tx_Seminars_OldModel_Event $topic)
    {
        $this->topic = $topic;
    }

    /**
     * Gets a list of the titles of (topic) records referenced by the this record.
     *
     * @param string $foreignTable the name of the foreign table (must not be empty), having the uid and title fields
     * @param string $mmTable the name of the m:m table, having the uid_local, uid_foreign and sorting fields
     *
     * @return string[] the titles of the referenced records
     */
    protected function getTopicMmRecordTitles(string $foreignTable, string $mmTable): array
    {
        $uid = $this->isEventDate() ? $this->getRecordPropertyInteger('topic') : $this->getUid();

        return $this->getMmRecordTitlesByUid($foreignTable, $mmTable, $uid);
    }

    /**
     * Gets our topic's title. For date records, this will return the
     * corresponding topic record's title.
     *
     * @return string our topic title (or '' if there is an error)
     */
    public function getTitle(): string
    {
        return $this->getTopicString('title');
    }

    /**
     * Gets our direct title. Even for date records, this will return our
     * direct title (which is visible in the back end) instead of the
     * corresponding topic record's title.
     *
     * @return string our direct title (or '' if there is an error)
     */
    public function getRealTitle(): string
    {
        return parent::getTitle();
    }

    /**
     * Gets our subtitle.
     *
     * @return string our seminar subtitle (or '' if there is an error)
     */
    public function getSubtitle(): string
    {
        return $this->getTopicString('subtitle');
    }

    /**
     * Checks whether we have a subtitle.
     *
     * @return bool TRUE if we have a non-empty subtitle, FALSE otherwise.
     */
    public function hasSubtitle(): bool
    {
        return $this->hasTopicString('subtitle');
    }

    /**
     * Gets our description.
     *
     * @return string our seminar description (or '' if there is an error)
     */
    public function getDescription(): string
    {
        return $this->getTopicString('description');
    }

    /**
     * Sets the description of the event.
     *
     * @param string $description the description for this event, may be empty
     *
     * @return void
     */
    public function setDescription(string $description)
    {
        $this->setRecordPropertyString('description', $description);
    }

    /**
     * Checks whether we have a description.
     *
     * @return bool TRUE if we have a non-empty description, FALSE otherwise.
     */
    public function hasDescription(): bool
    {
        return $this->hasTopicString('description');
    }

    /**
     * Gets the additional information.
     *
     * @return string HTML code of the additional information (or '' if there is
     *                an error)
     */
    public function getAdditionalInformation(): string
    {
        return $this->getTopicString('additional_information');
    }

    /**
     * Sets our additional information.
     *
     * @param string $additionalInformation our additional information, may be empty
     *
     * @return void
     */
    public function setAdditionalInformation(string $additionalInformation)
    {
        $this->setRecordPropertyString(
            'additional_information',
            $additionalInformation
        );
    }

    /**
     * Checks whether we have additional information for this event.
     *
     * @return bool TRUE if we have additional information (field not empty),
     *                 FALSE otherwise.
     */
    public function hasAdditionalInformation(): bool
    {
        return $this->hasTopicString('additional_information');
    }

    /**
     * Gets the unique seminar title, consisting of the seminar title and the
     * date (comma-separated).
     *
     * If the seminar has no date, just the title is returned.
     *
     * Note: This function does not htmlspecialchar its return value.
     *
     * @param string $dash the character used to separate start date and end date
     *
     * @return string the unique seminar title (or '' if there is an error)
     */
    public function getTitleAndDate($dash = '–'): string
    {
        $date = $this->hasDate() ? ', ' . $this->getDate($dash) : '';

        return $this->getTitle() . $date;
    }

    /**
     * Gets the accreditation number (which actually is a string, not an
     * integer).
     *
     * @return string the accreditation number (may be empty)
     */
    public function getAccreditationNumber(): string
    {
        return $this->getRecordPropertyString('accreditation_number');
    }

    /**
     * Checks whether we have an accreditation number set.
     *
     * @return bool TRUE if we have a non-empty accreditation number, FALSE otherwise.
     */
    public function hasAccreditationNumber(): bool
    {
        return $this->hasRecordPropertyString('accreditation_number');
    }

    /**
     * Gets the number of credit points for this seminar
     * (or an empty string if it is not set yet).
     *
     * @return string the number of credit points or a an empty string if it is 0
     */
    public function getCreditPoints(): string
    {
        return $this->hasCreditPoints() ? (string)$this->getTopicInteger('credit_points') : '';
    }

    /**
     * Checks whether this seminar has a non-zero number of credit
     * points assigned.
     *
     * @return bool TRUE if the seminar has credit points assigned,
     *                 FALSE otherwise.
     */
    public function hasCreditPoints(): bool
    {
        return $this->hasTopicInteger('credit_points');
    }

    /**
     * Gets our place (or places), complete as RTE'ed HTML with address and
     * links. Returns a localized string "will be announced" if the seminar has
     * no places set.
     *
     * @param TemplateHelper $plugin the current FE plugin
     *
     * @return string our places description (or '' if there is an error)
     */
    public function getPlaceWithDetails(TemplateHelper $plugin): string
    {
        if (!$this->hasPlace()) {
            $plugin->setMarker('message_will_be_announced', $this->translate('message_willBeAnnounced'));
            return $plugin->getSubpart('PLACE_LIST_EMPTY');
        }

        $result = '';
        foreach ($this->getPlacesAsArray() as $place) {
            $name = \htmlspecialchars((string)$place['title'], ENT_QUOTES | ENT_HTML5);
            if ((string)$place['homepage'] !== '') {
                $name = $plugin->cObj->getTypoLink(
                    $name,
                    (string)$place['homepage'],
                    [],
                    $plugin->getConfValueString('externalLinkTarget')
                );
            }
            $plugin->setMarker('place_item_title', $name);

            $descriptionParts = [];
            if ((string)$place['address'] !== '') {
                $addressParts = GeneralUtility::trimExplode("\r", (string)$place['address'], true);
                $address = \implode(', ', $addressParts);
                $descriptionParts[] = \htmlspecialchars($address, ENT_QUOTES | ENT_HTML5);
            }
            if ((string)$place['city'] !== '') {
                $descriptionParts[] = \htmlspecialchars(
                    \trim($place['zip'] . ' ' . $place['city']),
                    ENT_QUOTES | ENT_HTML5
                );
            }
            if ((string)$place['country'] !== '') {
                $countryName = $this->getCountryNameFromIsoCode((string)$place['country']);
                if ($countryName !== '') {
                    $descriptionParts[] = \htmlspecialchars($countryName, ENT_QUOTES | ENT_HTML5);
                }
            }

            $description = \implode(', ', $descriptionParts);
            if ((string)$place['directions'] !== '') {
                $description .= $plugin->pi_RTEcssText(\trim((string)$place['directions']));
            }
            $plugin->setMarker('place_item_description', $description);

            $result .= $plugin->getSubpart('PLACE_LIST_ITEM');
        }

        $plugin->setMarker('place_list_content', $result);

        return $plugin->getSubpart('PLACE_LIST_COMPLETE');
    }

    /**
     * Checks whether the current event has at least one place set, and if
     * this/these pace(s) have a country set.
     * Returns a boolean TRUE if at least one of the set places has a
     * country set, returns FALSE otherwise.
     *
     * IMPORTANT: This function does not check whether the saved ISO code is
     * valid at all. As this field is filled through the BE from a prefilled
     * list, this should never be an issue at all.
     *
     * @return bool whether at least one place with country are set
     *                 for the current event
     */
    public function hasCountry(): bool
    {
        $placesWithCountry = $this->getPlacesWithCountry();
        return $this->hasPlace() && !empty($placesWithCountry);
    }

    /**
     * Returns an array of two-char ISO codes of countries for this event.
     * These are fetched from the referenced place records of this event. If no
     * place is set, or the set place(s) don't have any country set, an empty
     * array will be returned.
     *
     * @return string[] the list of ISO codes for the countries of this event, may be empty
     */
    public function getPlacesWithCountry(): array
    {
        if (!$this->hasPlace()) {
            return [];
        }

        $countries = array_column($this->getPlacesAsArray(), 'country');
        return array_filter(
            $countries,
            static function (string $country): bool {
                return $country !== '';
            }
        );
    }

    /**
     * Returns a comma-separated list of country names that were set in the
     * place record(s).
     * If no places are set, or no countries are selected in the set places,
     * an empty string will be returned.
     *
     * @return string comma-separated list of countries for this event,
     *                may be empty
     */
    public function getCountry(): string
    {
        if (!$this->hasCountry()) {
            return '';
        }

        $countryList = [];

        // Fetches the countries from the corresponding place records, may be
        // an empty array.
        // Get the real country names from the ISO codes.
        foreach ($this->getPlacesWithCountry() as $currentCountry) {
            $countryList[] = $this->getCountryNameFromIsoCode($currentCountry);
        }

        // Makes sure that each country is exactly once in the array and then
        // returns this list.
        $countryListUnique = array_unique($countryList);
        return implode(', ', $countryListUnique);
    }

    /**
     * Returns a comma-separated list of city names that were set in the place
     * record(s).
     * If no places are set, or no cities are selected in the set places, an
     * empty string will be returned.
     *
     * @return string comma-separated list of cities for this event, may be
     *                empty
     */
    public function getCities(): string
    {
        if (!$this->hasCities()) {
            return '';
        }

        $cityList = $this->getCitiesFromPlaces();

        // Makes sure that each city is exactly once in the array and then
        // returns this list.
        $cityListUnique = array_unique($cityList);
        return implode(', ', $cityListUnique);
    }

    /**
     * Checks whether the current event has at least one place set, and if
     * this/these pace(s) have a city set.
     * Returns a boolean TRUE if at least one of the set places has a
     * city set, returns FALSE otherwise.
     *
     * @return bool whether at least one place with city are set for the
     *                 current event
     */
    public function hasCities(): bool
    {
        return $this->hasPlace() && count($this->getCitiesFromPlaces()) > 0;
    }

    /**
     * Returns the city names for this event.
     *
     * These are fetched from the referenced place records of this event. If no
     * place is set, or the set place(s) don't have any city set, an empty
     * array will be returned.
     *
     * @return string[]
     */
    public function getCitiesFromPlaces(): array
    {
        return array_column($this->getPlacesAsArray(), 'city');
    }

    /**
     * Returns the name of the requested country from the static info tables.
     * If the country with this ISO code could not be found in the database,
     * an empty string is returned instead.
     *
     * @param string $isoCode the ISO 3166-1 alpha-2 code of the country, must not be empty
     *
     * @return string the short local name of the country or an empty
     *                string if the country could not be found
     */
    public function getCountryNameFromIsoCode(string $isoCode): string
    {
        $table = 'static_countries';
        $title = self::getConnectionForTable($table)
            ->select(['cn_short_local'], $table, ['cn_iso_2' => $isoCode])->fetchColumn();

        return \is_string($title) ? $title : '';
    }

    /**
     * Gets our place (or places) with address and links as HTML, not RTE'ed yet,
     * separated by LF.
     *
     * Returns a localized string "will be announced" if the seminar has no
     * places set.
     *
     * @return string our places description (or '' if there is an error)
     */
    protected function getPlaceWithDetailsRaw(): string
    {
        if (!$this->hasPlace()) {
            return $this->translate('message_willBeAnnounced');
        }

        $placeTexts = [];

        foreach ($this->getPlacesAsArray() as $place) {
            $placeText = $place['title'];
            if ($place['homepage'] != '') {
                $placeText .= LF . $place['homepage'];
            }

            $descriptionParts = [];
            if ($place['address'] != '') {
                $descriptionParts[] = str_replace(CR, ',', $place['address']);
            }
            if ($place['city'] != '') {
                $descriptionParts[] = trim(
                    $place['zip'] . ' ' . $place['city']
                );
            }
            if ($place['country'] != '') {
                $countryName = $this->getCountryNameFromIsoCode(
                    $place['country']
                );
                if ($countryName != '') {
                    $descriptionParts[] = $countryName;
                }
            }

            if (!empty($descriptionParts)) {
                $placeText .= ', ' . implode(', ', $descriptionParts);
            }
            if ($place['directions'] != '') {
                $placeText .= LF . str_replace(CR, ', ', $place['directions']);
            }

            $placeTexts[] = $placeText;
        }

        return implode(LF, $placeTexts);
    }

    /**
     * Gets all places that are related to this event as an array.
     *
     * The array will be two-dimensional: The first dimensional is just numeric.
     * The second dimension is associative with the following keys:
     * title, address, city, country, homepage, directions
     *
     * @return array[] all places as a two-dimensional array, will be empty if there are no places assigned
     */
    protected function getPlacesAsArray(): array
    {
        $queryBuilder = self::getQueryBuilderForTable('tx_seminars_sites');

        return $queryBuilder
            ->select('uid', 'title', 'address', 'zip', 'city', 'country', 'homepage', 'directions')
            ->from('tx_seminars_sites')
            ->leftJoin(
                'tx_seminars_sites',
                'tx_seminars_seminars_place_mm',
                'mm',
                $queryBuilder->expr()->eq(
                    'tx_seminars_sites.uid',
                    $queryBuilder->quoteIdentifier('mm.uid_foreign')
                )
            )
            ->where(
                $queryBuilder->expr()->eq(
                    'mm.uid_local',
                    $queryBuilder->createNamedParameter($this->getUid(), \PDO::PARAM_INT)
                )
            )
            ->orderBy('mm.sorting')
            ->execute()
            ->fetchAll();
    }

    /**
     * Gets our place (or places) as a plain text list (just the names).
     * Returns a localized string "will be announced" if the seminar has no
     * places set.
     *
     * Note: This function does not htmlspecialchar the place titles.
     *
     * @return string our places list (or '' if there is an error)
     */
    public function getPlaceShort(): string
    {
        if (!$this->hasPlace()) {
            return $this->translate('message_willBeAnnounced');
        }

        $places = $this->getPlacesAsArray();

        return implode(', ', array_column($places, 'title'));
    }

    /**
     * Creates and returns a speakerbag object.
     *
     * @param string $speakerRelation
     *        the relation in which the speakers stand to this event: "speakers" (default), "partners", "tutors" or "leaders"
     *
     * @return \Tx_Seminars_Bag_Speaker a speakerbag object
     */
    private function getSpeakerBag($speakerRelation = 'speakers'): \Tx_Seminars_Bag_Speaker
    {
        switch ($speakerRelation) {
            case 'partners':
                $mmTable = 'tx_seminars_seminars_speakers_mm_partners';
                break;
            case 'tutors':
                $mmTable = 'tx_seminars_seminars_speakers_mm_tutors';
                break;
            case 'leaders':
                $mmTable = 'tx_seminars_seminars_speakers_mm_leaders';
                break;
            case 'speakers':
                // The fallthrough is intended.
            default:
                $mmTable = 'tx_seminars_seminars_speakers_mm';
        }

        return GeneralUtility::makeInstance(
            \Tx_Seminars_Bag_Speaker::class,
            $mmTable . '.uid_local = ' . $this->getUid(
            ) . ' AND ' . 'tx_seminars_speakers.uid = ' . $mmTable . '.uid_foreign',
            $mmTable,
            '',
            'sorting'
        );
    }

    /**
     * Gets our speaker (or speakers), complete as RTE'ed HTML with details and
     * links.
     * Returns an empty paragraph if this seminar doesn't have any speakers.
     *
     * As speakers can be related to this event as speakers, partners, tutors or
     * leaders, the type relation can be specified. The default is "speakers".
     *
     * @param TemplateHelper $plugin the live pibase object
     * @param string $speakerRelation
     *        the relation in which the speakers stand to this event:
     *        "speakers" (default), "partners", "tutors" or "leaders"
     *
     * @return string our speakers (or '' if there is an error)
     */
    public function getSpeakersWithDetails(TemplateHelper $plugin, $speakerRelation = 'speakers'): string
    {
        if (!$this->hasSpeakersOfType($speakerRelation)) {
            return '';
        }

        $result = [];

        /** @var \Tx_Seminars_OldModel_Speaker $speaker */
        foreach ($this->getSpeakerBag($speakerRelation) as $speaker) {
            $name = $speaker->getLinkedTitle($plugin);
            if ($speaker->hasOrganization()) {
                $name .= ', ' . \htmlspecialchars($speaker->getOrganization(), ENT_QUOTES | ENT_HTML5);
            }
            $plugin->setMarker('speaker_item_title', $name);
            $plugin->setMarker(
                'speaker_item_description',
                $speaker->hasDescription() ? $speaker->getDescription($plugin) : ''
            );
            $plugin->setMarker(
                'speaker_image',
                $speaker->hasImage() ? $this->renderSpeakerImage($speaker, $plugin) : ''
            );
            $result[] = $plugin->getSubpart('SPEAKER_LIST_ITEM');
        }

        return \implode(LF, $result);
    }

    private function renderSpeakerImage(\Tx_Seminars_OldModel_Speaker $speaker, TemplateHelper $plugin): string
    {
        $imageConfiguration = [
            'altText' => $plugin->translate('speakerImage.alt'),
            'titleText' => $plugin->translate('speakerImage.alt'),
            'params' => 'class="speaker-image"',
            'file' => $speaker->getImage()->getPublicUrl(),
            'file.' => [
                'width' => $plugin->getConfValueInteger('speakerImageWidth') . 'c',
                'height' => $plugin->getConfValueInteger('speakerImageHeight') . 'c',
            ],
        ];

        return $plugin->cObj->cObjGetSingle('IMAGE', $imageConfiguration);
    }

    /**
     * Gets our speaker (or speakers), as HTML with details and URLs, but not
     * RTE'ed yet.
     * Returns an empty string if this event doesn't have any speakers.
     *
     * As speakers can be related to this event as speakers, partners, tutors or
     * leaders, the type relation can be specified. The default is "speakers".
     *
     * @param string $speakerRelation
     *        the relation in which the speakers stand to this event:
     *        "speakers" (default), "partners", "tutors" or "leaders"
     *
     * @return string our speakers (or '' if there is an error)
     */
    protected function getSpeakersWithDescriptionRaw(string $speakerRelation = 'speakers'): string
    {
        if (!$this->hasSpeakersOfType($speakerRelation)) {
            return '';
        }

        $result = '';

        /** @var \Tx_Seminars_OldModel_Speaker $speaker */
        foreach ($this->getSpeakerBag($speakerRelation) as $speaker) {
            $result .= $speaker->getTitle();
            if ($speaker->hasOrganization()) {
                $result .= ', ' . $speaker->getOrganization();
            }
            if ($speaker->hasHomepage()) {
                $result .= ', ' . $speaker->getHomepage();
            }
            $result .= LF;

            if ($speaker->hasDescription()) {
                $result .= $speaker->getDescriptionRaw() . LF;
            }
        }

        return $result;
    }

    /**
     * Gets our speaker (or speakers) as a list (just their names),
     * linked to their homepage, if the speaker (or speakers) has one.
     * Returns an empty string if this seminar doesn't have any speakers.
     *
     * As speakers can be related to this event as speakers, partners, tutors or
     * leaders, the type relation can be specified. The default is "speakers".
     *
     * @param TemplateHelper $plugin
     * @param string $speakerRelation
     *        the relation in which the speakers stand to this event:
     *        "speakers" (default), "partners", "tutors" or "leaders"
     *
     * @return string our speakers list, will be empty if an error occurred
     *                during processing
     */
    public function getSpeakersShort(TemplateHelper $plugin, $speakerRelation = 'speakers'): string
    {
        if (!$this->hasSpeakersOfType($speakerRelation)) {
            return '';
        }

        $result = [];

        /** @var \Tx_Seminars_OldModel_Speaker $speaker */
        foreach ($this->getSpeakerBag($speakerRelation) as $speaker) {
            $result[] = $speaker->getLinkedTitle($plugin);
        }

        return implode(', ', $result);
    }

    /**
     * Gets the number of speakers associated with this event.
     *
     * @return int the number of speakers associated with this event,
     *                 will be >= 0
     */
    public function getNumberOfSpeakers(): int
    {
        return $this->getRecordPropertyInteger('speakers');
    }

    /**
     * Gets the number of partners associated with this event.
     *
     * @return int the number of partners associated with this event,
     *                 will be >= 0
     */
    public function getNumberOfPartners(): int
    {
        return $this->getRecordPropertyInteger('partners');
    }

    /**
     * Gets the number of tutors associated with this event.
     *
     * @return int the number of tutors associated with this event,
     *                 will be >= 0
     */
    public function getNumberOfTutors(): int
    {
        return $this->getRecordPropertyInteger('tutors');
    }

    /**
     * Gets the number of leaders associated with this event.
     *
     * @return int the number of leaders associated with this event,
     *                 will be >= 0
     */
    public function getNumberOfLeaders(): int
    {
        return $this->getRecordPropertyInteger('leaders');
    }

    /**
     * Checks whether we have speaker relations of the specified type set.
     *
     * @param string $speakerRelation
     *        the relation in which the speakers stand to this event:
     *        "speakers" (default), "partners", "tutors" or "leaders"
     *
     * @return bool TRUE if we have any speakers of the specified type
     *                 related to this event, FALSE otherwise.
     */
    public function hasSpeakersOfType($speakerRelation = 'speakers'): bool
    {
        switch ($speakerRelation) {
            case 'partners':
                $hasSpeakers = $this->hasPartners();
                break;
            case 'tutors':
                $hasSpeakers = $this->hasTutors();
                break;
            case 'leaders':
                $hasSpeakers = $this->hasLeaders();
                break;
            case 'speakers':
                // The fallthrough is intended.
            default:
                $hasSpeakers = $this->hasSpeakers();
        }

        return $hasSpeakers;
    }

    /**
     * Checks whether we have any speakers set.
     *
     * @return bool TRUE if we have any speakers related to this event,
     *                 FALSE otherwise
     */
    public function hasSpeakers(): bool
    {
        return $this->hasRecordPropertyInteger('speakers');
    }

    /**
     * Checks whether we have any partners set.
     *
     * @return bool TRUE if we have any partners related to this event,
     *                 FALSE otherwise
     */
    public function hasPartners(): bool
    {
        return $this->hasRecordPropertyInteger('partners');
    }

    /**
     * Checks whether we have any tutors set.
     *
     * @return bool TRUE if we have any tutors related to this event,
     *                 FALSE otherwise
     */
    public function hasTutors(): bool
    {
        return $this->hasRecordPropertyInteger('tutors');
    }

    /**
     * Checks whether we have any leaders set.
     *
     * @return bool TRUE if we have any leaders related to this event,
     *                 FALSE otherwise
     */
    public function hasLeaders(): bool
    {
        return $this->hasRecordPropertyInteger('leaders');
    }

    /**
     * Returns the language key suffix for the speaker headings.
     *
     * @param string $speakerType
     *        the type to determine the gender and number of, must be
     *        'speakers', 'tutors', 'leaders' or 'partners'
     *
     * @return string header marker for speaker heading will be
     *                'type_number_gender'. Number will be 'single' or
     *                'multiple' and gender will be 'male', 'female' or 'mixed'.
     *                The only exception is multiple speakers and mixed genders,
     *                then the result will be the input value.
     *                Will be empty if no speaker of the given type exists for
     *                this seminar.
     */
    public function getLanguageKeySuffixForType(string $speakerType): string
    {
        if (!$this->hasSpeakersOfType($speakerType)) {
            return '';
        }

        $result = $speakerType;
        $hasMaleSpeakers = false;
        $hasFemaleSpeakers = false;
        $hasMultipleSpeakers = false;

        $speakers = $this->getSpeakerBag($speakerType);
        if ($speakers->count() > 1) {
            $hasMultipleSpeakers = true;
            $result .= '_multiple';
        } else {
            $result .= '_single';
        }

        /** @var \Tx_Seminars_OldModel_Speaker $speaker */
        foreach ($speakers as $speaker) {
            switch ($speaker->getGender()) {
                case \Tx_Seminars_OldModel_Speaker::GENDER_MALE:
                    $hasMaleSpeakers = true;
                    break;
                case \Tx_Seminars_OldModel_Speaker::GENDER_FEMALE:
                    $hasFemaleSpeakers = true;
                    break;
                default:
                    $hasMaleSpeakers = true;
                    $hasFemaleSpeakers = true;
            }
        }

        if ($hasMaleSpeakers && !$hasFemaleSpeakers) {
            $result .= '_male';
        } elseif (!$hasMaleSpeakers && $hasFemaleSpeakers) {
            $result .= '_female';
        } elseif ($hasMultipleSpeakers) {
            $result = $speakerType;
        } else {
            $result .= '_unknown';
        }

        return $result;
    }

    /**
     * Checks whether we have a language set.
     *
     * @return bool TRUE if we have a language set for this event,
     *                 FALSE otherwise
     */
    public function hasLanguage(): bool
    {
        return $this->hasRecordPropertyString('language');
    }

    /**
     * Returns the localized name of the language for this event. In the case
     * that no language is selected, an empty string will be returned.
     *
     * @return string the localized name of the language of this event or
     *                an empty string if no language is set
     */
    public function getLanguageName(): string
    {
        $language = '';
        if ($this->hasLanguage()) {
            $language = $this->getLanguageNameFromIsoCode(
                $this->getRecordPropertyString('language')
            );
        }
        return $language;
    }

    /**
     * Returns the language ISO code for this event. In the case that no
     * language is selected, an empty string will be returned.
     *
     * @return string the ISO code of the language of this event or an
     *                empty string if no language is set
     */
    public function getLanguage(): string
    {
        return $this->getRecordPropertyString('language');
    }

    /**
     * Sets the language ISO code for this event.
     *
     * @param string $language
     *        the ISO code of the language for this event to set, may be empty
     *
     * @return void
     */
    public function setLanguage(string $language)
    {
        $this->setRecordPropertyString('language', $language);
    }

    /**
     * Gets our regular price as a string containing amount and currency. If
     * no regular price has been set, either "free" or "to be announced" will
     * be returned, depending on the TS variable showToBeAnnouncedForEmptyPrice.
     *
     * @return string the regular seminar price
     */
    public function getPriceRegular(): string
    {
        if ($this->hasPriceRegular()) {
            $result = $this->formatPrice($this->getPriceRegularAmount());
        } else {
            $result =
                $this->getConfValueBoolean('showToBeAnnouncedForEmptyPrice')
                    ? $this->translate('message_willBeAnnounced')
                    : $this->translate('message_forFree');
        }

        return $result;
    }

    /**
     * Gets our regular price as a decimal.
     *
     * @return string
     */
    private function getPriceRegularAmount(): string
    {
        return $this->getTopicDecimal('price_regular');
    }

    /**
     * Returns the price, formatted as configured in TS.
     *
     * @param string $value the price
     *
     * @return string the price, formatted as in configured in TS
     */
    public function formatPrice(string $value): string
    {
        /** @var \Tx_Oelib_ViewHelper_Price $priceViewHelper */
        $priceViewHelper = GeneralUtility::makeInstance(\Tx_Oelib_ViewHelper_Price::class);
        $priceViewHelper->setCurrencyFromIsoAlpha3Code(
            \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')->getAsString('currency')
        );
        $priceViewHelper->setValue((float)$value);

        return $priceViewHelper->render();
    }

    /**
     * Returns the current regular price for this event.
     * If there is a valid early bird offer, this price will be returned,
     * otherwise the default price.
     *
     * @return string the price and the currency
     */
    public function getCurrentPriceRegular(): string
    {
        if ($this->getPriceOnRequest()) {
            return $this->translate('message_onRequest');
        }

        return $this->earlyBirdApplies() ? $this->getEarlyBirdPriceRegular() : $this->getPriceRegular();
    }

    /**
     * Returns the current price for this event.
     * If there is a valid early bird offer, this price will be returned, the
     * default special price otherwise.
     *
     * @return string the price and the currency
     */
    public function getCurrentPriceSpecial(): string
    {
        if ($this->getPriceOnRequest()) {
            return $this->translate('message_onRequest');
        }

        return $this->earlyBirdApplies() ? $this->getEarlyBirdPriceSpecial() : $this->getPriceSpecial();
    }

    /**
     * Gets our regular price during the early bird phase as a string containing
     * amount and currency.
     *
     * @return string the regular early bird event price
     */
    public function getEarlyBirdPriceRegular(): string
    {
        return $this->hasEarlyBirdPriceRegular()
            ? $this->formatPrice($this->getEarlyBirdPriceRegularAmount()) : '';
    }

    /**
     * Gets our regular price during the early bird phase as a decimal.
     *
     * If there is no regular early bird price, this function returns "0.00".
     *
     * @return string the regular early bird event price
     */
    private function getEarlyBirdPriceRegularAmount(): string
    {
        return $this->getTopicDecimal('price_regular_early');
    }

    /**
     * Gets our special price during the early bird phase as a string containing
     * amount and currency.
     *
     * @return string the regular early bird event price
     */
    public function getEarlyBirdPriceSpecial(): string
    {
        return $this->hasEarlyBirdPriceSpecial()
            ? $this->formatPrice($this->getEarlyBirdPriceSpecialAmount()) : '';
    }

    /**
     * Gets our special price during the early bird phase as a decimal.
     *
     * If there is no special price during the early bird phase, this function
     * returns "0.00".
     *
     * @return string the special event price during the early bird phase
     */
    private function getEarlyBirdPriceSpecialAmount(): string
    {
        return $this->getTopicDecimal('price_special_early');
    }

    /**
     * Checks whether this seminar has a non-zero regular price set.
     *
     * @return bool TRUE if the seminar has a non-zero regular price,
     *                 FALSE if it is free.
     */
    public function hasPriceRegular(): bool
    {
        return $this->hasTopicDecimal('price_regular');
    }

    /**
     * Checks whether this seminar has a non-zero regular early bird price set.
     *
     * @return bool TRUE if the seminar has a non-zero regular early
     *                 bird price, FALSE otherwise
     */
    protected function hasEarlyBirdPriceRegular(): bool
    {
        return $this->hasTopicDecimal('price_regular_early');
    }

    /**
     * Checks whether this seminar has a non-zero special early bird price set.
     *
     * @return bool TRUE if the seminar has a non-zero special early
     *                 bird price, FALSE otherwise
     */
    protected function hasEarlyBirdPriceSpecial(): bool
    {
        return $this->hasTopicDecimal('price_special_early');
    }

    /**
     * Checks whether this event has a deadline for the early bird prices set.
     *
     * @return bool TRUE if the event has an early bird deadline set,
     *                 FALSE if not
     */
    private function hasEarlyBirdDeadline(): bool
    {
        return $this->hasRecordPropertyInteger('deadline_early_bird');
    }

    /**
     * Returns whether an early bird price applies.
     *
     * @return bool TRUE if this event has an early bird dealine set and
     *                 this deadline is not over yet
     */
    protected function earlyBirdApplies(): bool
    {
        return $this->hasEarlyBirdPrice() && !$this->isEarlyBirdDeadlineOver();
    }

    /**
     * Checks whether this event is sold with early bird prices.
     *
     * This will return TRUE if the event has a deadline and a price defined
     * for early-bird registrations. If the special price (e.g. for students)
     * is not used, then the student's early bird price is not checked.
     *
     * Attention: Both prices (standard and special) need to have an early bird
     * version for this function to return TRUE (if there is a regular special
     * price).
     *
     * @return bool TRUE if an early bird deadline and early bird prices
     *                 are set
     */
    public function hasEarlyBirdPrice(): bool
    {
        // whether the event has regular prices set (a normal one and an early bird)
        $priceRegularIsOk = $this->hasPriceRegular()
            && $this->hasEarlyBirdPriceRegular();

        // whether no special price is set, or both special prices
        // (normal and early bird) are set
        $priceSpecialIsOk = !$this->hasPriceSpecial()
            || ($this->hasPriceSpecial() && $this->hasEarlyBirdPriceSpecial());

        return $this->hasEarlyBirdDeadline()
            && $priceRegularIsOk
            && $priceSpecialIsOk;
    }

    /**
     * Gets our special price as a string containing amount and currency.
     * Returns an empty string if there is no special price set.
     *
     * @return string the special event price
     */
    public function getPriceSpecial(): string
    {
        return $this->hasPriceSpecial()
            ? $this->formatPrice($this->getPriceSpecialAmount()) : '';
    }

    /**
     * Gets our special price as a decimal.
     *
     * If there is no special price, this function returns "0.00".
     *
     * @return string the special event price
     */
    private function getPriceSpecialAmount(): string
    {
        return $this->getTopicDecimal('price_special');
    }

    /**
     * Checks whether this seminar has a non-zero special price set.
     *
     * @return bool TRUE if the seminar has a non-zero special price,
     *                 FALSE if it is free.
     */
    public function hasPriceSpecial(): bool
    {
        return $this->hasTopicDecimal('price_special');
    }

    /**
     * Gets our regular price (including full board) as a string containing
     * amount and currency. Returns an empty string if there is no regular price
     * (including full board) set.
     *
     * @return string the regular event price (including full board)
     */
    public function getPriceRegularBoard(): string
    {
        return $this->hasPriceRegularBoard()
            ? $this->formatPrice($this->getPriceRegularBoardAmount()) : '';
    }

    /**
     * Gets our regular price (including full board) as a decimal.
     *
     * If there is no regular price (including full board), this function
     * returns "0.00".
     *
     * @return string the regular event price (including full board)
     */
    private function getPriceRegularBoardAmount(): string
    {
        return $this->getTopicDecimal('price_regular_board');
    }

    /**
     * Checks whether this event has a non-zero regular price (including full
     * board) set.
     *
     * @return bool TRUE if the event has a non-zero regular price
     *                 (including full board), FALSE otherwise
     */
    public function hasPriceRegularBoard(): bool
    {
        return $this->hasTopicDecimal('price_regular_board');
    }

    /**
     * Gets our special price (including full board) as a string containing
     * amount and currency. Returns an empty string if there is no special price
     * (including full board) set.
     *
     * @return string the special event price (including full board)
     */
    public function getPriceSpecialBoard(): string
    {
        return $this->hasPriceSpecialBoard()
            ? $this->formatPrice($this->getPriceSpecialBoardAmount()) : '';
    }

    /**
     * Gets our special price (including full board) as a decimal.
     *
     * If there is no special price (including full board), this function
     * returns "0.00".
     *
     * @return string the special event price (including full board)
     */
    private function getPriceSpecialBoardAmount(): string
    {
        return $this->getTopicDecimal('price_special_board');
    }

    /**
     * Checks whether this event has a non-zero special price (including full
     * board) set.
     *
     * @return bool TRUE if the event has a non-zero special price
     *                 (including full board), FALSE otherwise
     */
    public function hasPriceSpecialBoard(): bool
    {
        return $this->hasTopicDecimal('price_special_board');
    }

    /**
     * @return bool
     */
    public function getPriceOnRequest(): bool
    {
        return $this->getTopicBoolean('price_on_request');
    }

    /**
     * @param bool $priceOnRequest
     *
     * @return void
     */
    public function setPriceOnRequest(bool $priceOnRequest)
    {
        $this->setRecordPropertyBoolean('price_on_request', $priceOnRequest);
    }

    /**
     * Gets the titles of allowed payment methods for this event.
     *
     * @return string[] our payment method titles, will be an empty array if there are no payment methods
     */
    public function getPaymentMethods(): array
    {
        if (!$this->hasPaymentMethods()) {
            return [];
        }

        return array_column($this->getPaymentMethodsAsArray(), 'title');
    }

    /**
     * Gets our allowed payment methods, just as plain text, including the detailed description.
     * Returns an empty string if this seminar does not have any payment methods.
     *
     * @return string our payment methods as plain text (or '' if there is an error)
     */
    public function getPaymentMethodsPlain(): string
    {
        if (!$this->hasPaymentMethods()) {
            return '';
        }

        $result = '';

        foreach ($this->getPaymentMethodsAsArray() as $paymentMethod) {
            $result .= $paymentMethod['title'] . ': ';
            $result .= $paymentMethod['description'] . LF . LF;
        }

        return $result;
    }

    /**
     * Gets our allowed payment methods, just as plain text separated by LF, without the detailed description.
     *
     * Returns an empty string if this seminar does not have any payment methods.
     *
     * @return string our payment methods as plain text (or '' if there is an error)
     */
    protected function getPaymentMethodsPlainShort(): string
    {
        if (!$this->hasPaymentMethods()) {
            return '';
        }

        return implode(LF, $this->getPaymentMethods());
    }

    /**
     * Get a single payment method, just as plain text, including the detailed description.
     *
     * Returns an empty string if the corresponding payment method could not be retrieved.
     *
     * @param int $uid the UID of a single payment method, must not be zero
     *
     * @return string the selected payment method as plain text (or '' if there is an error)
     */
    public function getSinglePaymentMethodPlain(int $uid): string
    {
        if ($uid <= 0) {
            return '';
        }

        $table = 'tx_seminars_payment_methods';
        $data = self::getConnectionForTable($table)->select(['*'], $table, ['uid' => $uid])->fetch();
        if (!\is_array($data)) {
            return '';
        }

        $result = (string)$data['title'];
        $description = (string)$data['description'];
        if ($description !== '') {
            $result .= ': ' . $description;
        }

        return $result . "\n\n";
    }

    /**
     * Get a single payment method, just as plain text, without the detailed description.
     *
     * Returns an empty string if the corresponding payment method could not be retrieved.
     *
     * @param int $uid the UID of a single payment method, must be >= 0
     *
     * @return string the selected payment method as plain text (or '' if there is an error)
     */
    public function getSinglePaymentMethodShort(int $uid): string
    {
        if ($uid <= 0) {
            return '';
        }

        $table = 'tx_seminars_payment_methods';
        $data = self::getConnectionForTable($table)->select(['*'], $table, ['uid' => $uid])->fetch();

        return \is_array($data) ? (string)$data['title'] : '';
    }

    /**
     * Checks whether this seminar has any payment methods set.
     *
     * @return bool TRUE if the seminar has any payment methods, FALSE
     *                 if it is free
     */
    public function hasPaymentMethods(): bool
    {
        return $this->hasTopicInteger('payment_methods');
    }

    /**
     * Gets the number of available payment methods.
     *
     * @return int the number of available payment methods, might 0
     */
    public function getNumberOfPaymentMethods(): int
    {
        return $this->getTopicInteger('payment_methods');
    }

    /**
     * Returns the name of the requested language from the static info tables.
     * If no language with this ISO code could not be found in the database,
     * an empty string is returned instead.
     *
     * @param string $isoCode the ISO 639 alpha-2 code of the language
     *
     * @return string the short local name of the language or an empty string if the language could not be found
     */
    public function getLanguageNameFromIsoCode(string $isoCode): string
    {
        $table = 'static_languages';
        $data = self::getConnectionForTable($table)
            ->select(['lg_name_local'], $table, ['lg_iso_2' => $isoCode])->fetch();

        return \is_array($data) ? (string)$data['lg_name_local'] : '';
    }

    /**
     * Returns the type of the record. This is one out of the following values:
     * 0 = single event (and default value of older records)
     * 1 = multiple event topic record
     * 2 = multiple event date record
     *
     * @return int the record type
     */
    public function getRecordType(): int
    {
        return $this->getRecordPropertyInteger('object_type');
    }

    /**
     * Checks whether this seminar has an event type set.
     *
     * @return bool TRUE if the seminar has an event type set, otherwise
     *                 FALSE
     */
    public function hasEventType(): bool
    {
        return $this->hasTopicInteger('event_type');
    }

    /**
     * Returns the UID of the event type that was selected for this event. If no
     * event type has been set, 0 will be returned.
     *
     * @return int UID of the event type for this event or 0 if no
     *                 event type is set
     */
    public function getEventTypeUid(): int
    {
        return $this->getTopicInteger('event_type');
    }

    /**
     * Returns the event type as a string (e.g. "Workshop" or "Lecture").
     * If the seminar has a event type selected, that one is returned.
     * Otherwise, an empty string will be returned.
     *
     * @return string the type of this event, will be empty if this event does not have a type
     */
    public function getEventType(): string
    {
        if (!$this->hasEventType()) {
            return '';
        }

        $table = 'tx_seminars_event_types';
        $data = self::getConnectionForTable($table)
            ->select(['title'], $table, ['uid' => $this->getTopicInteger('event_type')])->fetch();

        return \is_array($data) ? (string)$data['title'] : '';
    }

    /**
     * Sets the event type for this event.
     *
     * @param int $eventType the UID of the event type to set, must be >= 0
     *
     * @return void
     */
    public function setEventType(int $eventType)
    {
        if ($eventType < 0) {
            throw new \InvalidArgumentException('$eventType must be >= 0.', 1333291840);
        }

        $this->setRecordPropertyInteger('event_type', $eventType);
    }

    /**
     * Gets the minimum number of attendances required for this event
     * (ie. how many registrations are needed so this event can take place).
     *
     * @return int the minimum number of attendances, will be >= 0
     */
    public function getAttendancesMin(): int
    {
        return $this->getRecordPropertyInteger('attendees_min');
    }

    /**
     * Gets the maximum number of attendances for this event
     * (the total number of seats for this event).
     *
     * @return int the maximum number of attendances, will be >= 0
     */
    public function getAttendancesMax(): int
    {
        return $this->getRecordPropertyInteger('attendees_max');
    }

    /**
     * Gets the number of attendances for this seminar
     * (currently the paid attendances as well as the unpaid ones).
     *
     * @return int the number of attendances, will be >= 0
     */
    public function getAttendances(): int
    {
        $this->calculateStatisticsIfNeeded();

        return $this->numberOfAttendances;
    }

    /**
     * Checks whether there is at least one registration for this event
     * (counting the paid attendances as well as the unpaid ones).
     *
     * @return bool true if there is at least one registration for this event, false otherwise
     */
    public function hasAttendances(): bool
    {
        return $this->getAttendances() > 0;
    }

    /**
     * Gets the number of paid attendances for this seminar.
     *
     * @return int the number of paid attendances, will be >= 0
     */
    public function getAttendancesPaid(): int
    {
        $this->calculateStatisticsIfNeeded();

        return $this->numberOfAttendancesPaid;
    }

    /**
     * Gets the number of attendances that are not paid yet
     *
     * @return int the number of attendances that are not paid yet, will be >= 0
     */
    public function getAttendancesNotPaid(): int
    {
        return $this->getAttendances() - $this->getAttendancesPaid();
    }

    /**
     * Gets the number of vacancies for this seminar.
     *
     * @return int the number of vacancies (will be 0 if the seminar is overbooked)
     */
    public function getVacancies(): int
    {
        return \max(0, $this->getAttendancesMax() - $this->getAttendances());
    }

    /**
     * Gets the number of vacancies for this seminar. If there are at least as
     * many vacancies as configured as "showVacanciesThreshold" or this event
     * has an unlimited number of vacancies, a localized string "enough" is
     * returned instead. If there are no vacancies, a localized string
     * "fully booked" is returned.
     *
     * If this seminar does not require a registration or has been canceled, an empty string is returned.
     *
     * @return string string showing the number of vacancies, may be empty
     */
    public function getVacanciesString(): string
    {
        if ($this->isCanceled() || !$this->needsRegistration() || $this->isRegistrationDeadlineOver()) {
            return '';
        }

        if ($this->hasUnlimitedVacancies()) {
            return $this->translate('message_enough');
        }

        $vacancies = $this->getVacancies();
        $vacanciesThreshold = $this->getConfValueInteger('showVacanciesThreshold');

        if ($vacancies === 0) {
            $result = $this->translate('message_fullyBooked');
        } elseif ($vacancies >= $vacanciesThreshold) {
            $result = $this->translate('message_enough');
        } else {
            $result = (string)$vacancies;
        }

        return $result;
    }

    /**
     * Checks whether this seminar still has vacancies (is not full yet).
     *
     * @return bool true if the seminar has vacancies, false if it is full
     */
    public function hasVacancies(): bool
    {
        return !$this->isFull();
    }

    /**
     * Checks whether this seminar already is full.
     *
     * @return bool true if the seminar is full, false if it still has
     *                 vacancies or if there are unlimited vacancies
     */
    public function isFull(): bool
    {
        return !$this->hasUnlimitedVacancies() && $this->getAttendances() >= $this->getAttendancesMax();
    }

    /**
     * Checks whether this seminar has enough attendances to take place.
     *
     * @return bool true if the seminar has enough attendances, false otherwise
     */
    public function hasEnoughAttendances(): bool
    {
        return $this->getAttendances() >= $this->getAttendancesMin();
    }

    /**
     * Returns the number of associated registration records (i.e., the number in the counter cache).
     *
     * @return int
     */
    public function getNumberOfAssociatedRegistrationRecords(): int
    {
        return $this->getRecordPropertyInteger('registrations');
    }

    /**
     * Increases the number of associated registration records by one.
     *
     * This method does not save this model.
     *
     * @return void
     */
    public function increaseNumberOfAssociatedRegistrationRecords()
    {
        $this->setRecordPropertyInteger('registrations', $this->getNumberOfAssociatedRegistrationRecords() + 1);
    }

    /**
     * Returns TRUE if this seminar has at least one target group, FALSE
     * otherwise.
     *
     * @return bool TRUE if this seminar has at least one target group,
     *                 FALSE otherwise
     */
    public function hasTargetGroups(): bool
    {
        return $this->hasTopicInteger('target_groups');
    }

    /**
     * Returns a string of our event's target group titles separated by a comma
     * (or an empty string if there aren't any).
     *
     * @return string the target group titles of this seminar separated by a comma (or an empty string)
     */
    public function getTargetGroupNames(): string
    {
        if (!$this->hasTargetGroups()) {
            return '';
        }

        return \implode(', ', $this->getTargetGroupsAsArray());
    }

    /**
     * Returns an array of our events's target group titles (or an empty array if there are none).
     *
     * @return string[] the target groups of this event (or an empty array)
     */
    public function getTargetGroupsAsArray(): array
    {
        if (!$this->hasTargetGroups()) {
            return [];
        }

        return $this->getTopicMmRecordTitles('tx_seminars_target_groups', 'tx_seminars_seminars_target_groups_mm');
    }

    /**
     * Gets the number of target groups associated with this event.
     *
     * @return int the number of target groups associated with this
     *                 event, will be >= 0
     */
    public function getNumberOfTargetGroups(): int
    {
        return $this->getRecordPropertyInteger('target_groups');
    }

    /**
     * Returns the latest date/time to register for a seminar.
     * This is either the registration deadline (if set) or the begin date of an
     * event.
     *
     * @return int the latest possible moment to register for this event
     */
    public function getLatestPossibleRegistrationTime(): int
    {
        if ($this->hasRegistrationDeadline()) {
            return $this->getRecordPropertyInteger('deadline_registration');
        }
        if (!$this->getConfValueBoolean('allowRegistrationForStartedEvents')) {
            return $this->getBeginDateAsTimestamp();
        }

        return $this->hasEndDate() ? $this->getEndDateAsTimestamp() : $this->getBeginDateAsTimestamp();
    }

    /**
     * Returns the latest date/time to register with early bird rebate for an
     * event. The latest time to register with early bird rebate is exactly at
     * the early bird deadline.
     *
     * @return int the latest possible moment to register with early
     *                 bird discount for an event
     */
    private function getLatestPossibleEarlyBirdRegistrationTime(): int
    {
        return $this->getRecordPropertyInteger('deadline_early_bird');
    }

    /**
     * Returns the seminar registration deadline: the date and also the time
     * (depending on the TS variable showTimeOfRegistrationDeadline).
     * The returned string is formatted using the format configured in
     * dateFormatYMD and timeFormat.
     *
     * This function will return an empty string if this event does not have a
     * registration deadline.
     *
     * @return string the date + time of the deadline or an empty string
     *                if this event has no registration deadline
     */
    public function getRegistrationDeadline(): string
    {
        $result = '';

        if ($this->hasRegistrationDeadline()) {
            $result = \strftime(
                $this->getConfValueString('dateFormatYMD'),
                $this->getRecordPropertyInteger('deadline_registration')
            );
            if ($this->getConfValueBoolean('showTimeOfRegistrationDeadline')) {
                $result .= \strftime(
                    ' ' . $this->getConfValueString('timeFormat'),
                    $this->getRecordPropertyInteger('deadline_registration')
                );
            }
        }

        return $result;
    }

    /**
     * Checks whether this seminar has a deadline for registration set.
     *
     * @return bool TRUE if the seminar has a datetime set.
     */
    public function hasRegistrationDeadline(): bool
    {
        return $this->hasRecordPropertyInteger('deadline_registration');
    }

    /**
     * Returns the early bird deadline.
     * The returned string is formatted using the format configured in
     * dateFormatYMD and timeFormat.
     *
     * The TS parameter 'showTimeOfEarlyBirdDeadline' controls if the time
     * should also be returned in addition to the date.
     *
     * This function will return an empty string if this event does not have an
     * early-bird deadline.
     *
     * @return string the date and time of the early bird deadline or an
     *                early string if this event has no early-bird deadline
     */
    public function getEarlyBirdDeadline(): string
    {
        $result = '';

        if ($this->hasEarlyBirdDeadline()) {
            $result = strftime(
                $this->getConfValueString('dateFormatYMD'),
                $this->getRecordPropertyInteger('deadline_early_bird')
            );
            if ($this->getConfValueBoolean('showTimeOfEarlyBirdDeadline')) {
                $result .= strftime(
                    ' ' . $this->getConfValueString('timeFormat'),
                    $this->getRecordPropertyInteger('deadline_early_bird')
                );
            }
        }

        return $result;
    }

    /**
     * Returns the seminar unregistration deadline: the date and also the time
     * (depending on the TS variable showTimeOfUnregistrationDeadline).
     * The returned string is formatted using the format configured in
     * dateFormatYMD and timeFormat.
     *
     * This function will return an empty string if this event does not have a
     * unregistration deadline.
     *
     * @return string the date + time of the deadline or an empty string
     *                if this event has no unregistration deadline
     */
    public function getUnregistrationDeadline(): string
    {
        $result = '';

        if ($this->hasUnregistrationDeadline()) {
            $result = strftime(
                $this->getConfValueString('dateFormatYMD'),
                $this->getRecordPropertyInteger('deadline_unregistration')
            );
            if ($this->getConfValueBoolean('showTimeOfUnregistrationDeadline')) {
                $result .= strftime(
                    ' ' . $this->getConfValueString('timeFormat'),
                    $this->getRecordPropertyInteger('deadline_unregistration')
                );
            }
        }

        return $result;
    }

    /**
     * Checks whether this seminar has a deadline for unregistration set.
     *
     * @return bool TRUE if the seminar has a unregistration deadline set.
     */
    public function hasUnregistrationDeadline(): bool
    {
        return $this->hasRecordPropertyInteger('deadline_unregistration');
    }

    /**
     * Gets the event's unregistration deadline as UNIX timestamp. Will be 0
     * if the event has no unregistration deadline set.
     *
     * @return int the unregistration deadline as UNIX timestamp
     */
    public function getUnregistrationDeadlineAsTimestamp(): int
    {
        return $this->getRecordPropertyInteger('deadline_unregistration');
    }

    /**
     * Creates an organizerbag object and returns it.
     * Throws an exception if there are no organizers related to this event.
     *
     * @return \Tx_Seminars_Bag_Organizer an organizerbag object
     *
     * @throws \BadMethodCallException
     */
    public function getOrganizerBag(): \Tx_Seminars_Bag_Organizer
    {
        if (!$this->hasOrganizers()) {
            throw new \BadMethodCallException('There are no organizers related to this event.', 1333291857);
        }

        /** @var \Tx_Seminars_BagBuilder_Organizer $builder */
        $builder = GeneralUtility::makeInstance(\Tx_Seminars_BagBuilder_Organizer::class);
        $builder->limitToEvent($this->getUid());
        /** @var \Tx_Seminars_Bag_Organizer $bag */
        $bag = $builder->build();

        return $bag;
    }

    /**
     * Returns the first organizer.
     *
     * @return \Tx_Seminars_OldModel_Organizer|null
     */
    public function getFirstOrganizer()
    {
        if (!$this->hasOrganizers()) {
            return null;
        }

        $organizers = $this->getOrganizerBag();
        $organizers->rewind();
        /** @var \Tx_Seminars_OldModel_Organizer|null $current */
        $current = $organizers->current();

        return $current;
    }

    /**
     * Gets our organizers (as HTML code with hyperlinks to their homepage, if they have any).
     *
     * @return string the hyperlinked names of our organizers
     */
    public function getOrganizers(TemplateHelper $plugin): string
    {
        if (!$this->hasOrganizers()) {
            return '';
        }

        $result = [];

        /** @var \Tx_Seminars_OldModel_Organizer $organizer */
        foreach ($this->getOrganizerBag() as $organizer) {
            $result[] = $plugin->cObj->getTypoLink(
                \htmlspecialchars($organizer->getName(), ENT_QUOTES | ENT_HTML5),
                $organizer->getHomepage(),
                [],
                $plugin->getConfValueString('externalLinkTarget')
            );
        }

        return implode(', ', $result);
    }

    /**
     * Gets our organizer's names (and URLs), separated by LF.
     *
     * @return string names and homepages of our organizers or an empty string if there are no organizers
     */
    protected function getOrganizersRaw(): string
    {
        if (!$this->hasOrganizers()) {
            return '';
        }

        $result = [];

        /** @var \Tx_Seminars_OldModel_Organizer $organizer */
        foreach ($this->getOrganizerBag() as $organizer) {
            $result[] = $organizer->getName() . ($organizer->hasHomepage() ? ', ' . $organizer->getHomepage() : '');
        }

        return implode(LF, $result);
    }

    /**
     * Gets our organizers' names and e-mail addresses in the format '"John Doe" <john.doe@example.com>'.
     *
     * The name is not encoded yet.
     *
     * @return string[] the organizers' names and e-mail addresses
     */
    public function getOrganizersNameAndEmail(): array
    {
        if (!$this->hasOrganizers()) {
            return [];
        }

        $result = [];

        /** @var \Tx_Seminars_OldModel_Organizer $organizer */
        foreach ($this->getOrganizerBag() as $organizer) {
            $result[] = '"' . $organizer->getName() . '"' . ' <' . $organizer->getEMailAddress() . '>';
        }

        return $result;
    }

    /**
     * Gets our organizers' e-mail addresses in the format
     * "john.doe@example.com".
     *
     * @return string[] the organizers' e-mail addresses
     */
    public function getOrganizersEmail(): array
    {
        if (!$this->hasOrganizers()) {
            return [];
        }

        $result = [];

        /** @var \Tx_Seminars_OldModel_Organizer $organizer */
        foreach ($this->getOrganizerBag() as $organizer) {
            $result[] = $organizer->getEMailAddress();
        }

        return $result;
    }

    /**
     * Gets our organizers' e-mail footers.
     *
     * @return string[] the organizers' e-mail footers, will be empty if no
     *               organizer was set, or all organizers have no e-mail footer
     */
    public function getOrganizersFooter(): array
    {
        if (!$this->hasOrganizers()) {
            return [];
        }

        $result = [];

        /** @var \Tx_Seminars_OldModel_Organizer $organizer */
        foreach ($this->getOrganizerBag() as $organizer) {
            $emailFooter = $organizer->getEmailFooter();
            if ($emailFooter !== '') {
                $result[] = $emailFooter;
            }
        }

        return $result;
    }

    /**
     * Checks whether we have any organizers set, but does not check the
     * validity of that entry.
     *
     * @return bool TRUE if we have any organizers related to this seminar, FALSE otherwise
     */
    public function hasOrganizers(): bool
    {
        return $this->hasRecordPropertyInteger('organizers');
    }

    /**
     * Gets the number of organizers.
     *
     * @return int the number of organizers, might be 0
     */
    public function getNumberOfOrganizers(): int
    {
        return $this->getRecordPropertyInteger('organizers');
    }

    /**
     * Gets our organizing partners comma-separated (as HTML code with
     * hyperlinks to their homepage, if they have any).
     *
     * Returns an empty string if this event has no organizing partners or
     * something went wrong with the database query.
     *
     * @return string the hyperlinked names of our organizing partners, or an empty string
     */
    public function getOrganizingPartners(TemplateHelper $plugin): string
    {
        if (!$this->hasOrganizingPartners()) {
            return '';
        }
        $result = [];

        /** @var \Tx_Seminars_Bag_Organizer $organizerBag */
        $organizerBag = GeneralUtility::makeInstance(
            \Tx_Seminars_Bag_Organizer::class,
            'tx_seminars_seminars_organizing_partners_mm.uid_local = ' . $this->getUid() . ' AND ' .
            'tx_seminars_seminars_organizing_partners_mm.uid_foreign = tx_seminars_organizers.uid',
            'tx_seminars_seminars_organizing_partners_mm'
        );

        /** @var \Tx_Seminars_OldModel_Organizer $organizer */
        foreach ($organizerBag as $organizer) {
            $result[] = $plugin->cObj->getTypoLink(
                $organizer->getName(),
                $organizer->getHomepage(),
                [],
                $plugin->getConfValueString('externalLinkTarget')
            );
        }

        return implode(', ', $result);
    }

    /**
     * Checks whether we have any organizing partners set.
     *
     * @return bool TRUE if we have any organizing partners related to this event, FALSE otherwise
     */
    public function hasOrganizingPartners(): bool
    {
        return $this->hasRecordPropertyInteger('organizing_partners');
    }

    /**
     * Gets the number of organizing partners associated with this event.
     *
     * @return int the number of organizing partners associated with this event, will be >= 0
     */
    public function getNumberOfOrganizingPartners(): int
    {
        return $this->getRecordPropertyInteger('organizing_partners');
    }

    /**
     * Checks whether this event has a separate details page set (which may be an internal or external URL).
     *
     * @return bool TRUE if this event has a separate details page, FALSE otherwise
     */
    public function hasSeparateDetailsPage(): bool
    {
        return $this->hasRecordPropertyString('details_page');
    }

    /**
     * Returns this event's separate details page URL (which may be
     * internal or external) or page ID.
     *
     * @return string the URL to this events separate details page, will be
     *                empty if this event has no separate details page set
     */
    public function getDetailsPage(): string
    {
        return $this->getRecordPropertyString('details_page');
    }

    /**
     * Gets a plain text list of property values (if they exist),
     * formatted as strings (and nicely lined up) in the following format:
     *
     * key1: value1
     *
     * @param string $keysList comma-separated list of key names
     *
     * @return string formatted output (may be empty)
     */
    public function dumpSeminarValues(string $keysList): string
    {
        $keys = GeneralUtility::trimExplode(',', $keysList, true);
        $keysWithLabels = [];

        $maxLength = 0;
        foreach ($keys as $currentKey) {
            $loweredKey = \strtolower($currentKey);
            $currentLabel = \rtrim($this->translate('label_' . $currentKey), ':');
            $keysWithLabels[$loweredKey] = $currentLabel;
            $maxLength = \max($maxLength, \mb_strlen($currentLabel, 'utf-8'));
        }
        $result = '';
        foreach ($keysWithLabels as $currentKey => $currentLabel) {
            switch ($currentKey) {
                case 'date':
                    $value = $this->getDate('-');
                    break;
                case 'place':
                    $value = $this->getPlaceShort();
                    break;
                case 'price_regular':
                    $value = $this->getPriceRegular();
                    break;
                case 'price_regular_early':
                    $value = $this->getEarlyBirdPriceRegular();
                    break;
                case 'price_special':
                    $value = $this->getPriceSpecial();
                    break;
                case 'price_special_early':
                    $value = $this->getEarlyBirdPriceSpecial();
                    break;
                case 'speakers':
                    $value = $this->getSpeakersShort($this);
                    break;
                case 'time':
                    $value = $this->getTime('-');
                    break;
                case 'titleanddate':
                    $value = $this->getTitleAndDate('-');
                    break;
                case 'event_type':
                    $value = $this->getEventType();
                    break;
                case 'vacancies':
                    if ($this->hasUnlimitedVacancies()) {
                        $value = $this->translate('label_unlimited');
                    } else {
                        $value = (string)$this->getVacancies();
                    }
                    break;
                case 'title':
                    $value = $this->getTitle();
                    break;
                case 'attendees':
                    $value = $this->getAttendances();
                    break;
                case 'enough_attendees':
                    $value = $this->hasEnoughAttendances()
                        ? $this->translate('label_yes')
                        : $this->translate('label_no');
                    break;
                case 'is_full':
                    $value = $this->isFull()
                        ? $this->translate('label_yes')
                        : $this->translate('label_no');
                    break;
                default:
                    $value = $this->getRecordPropertyString($currentKey);
            }

            // Check whether there is a value to display.
            // If not, we will not use the padding and break the line directly after the label.
            if ($value !== '') {
                $padding = \str_pad('', $maxLength - \mb_strlen($currentLabel, 'utf-8'));
                $result .= $currentLabel . ': ' . $padding . $value . "\n";
            } else {
                $result .= $currentLabel . ':' . LF;
            }
        }

        return $result;
    }

    /**
     * Checks whether a certain user already is registered for this seminar.
     *
     * @param int $uid UID of the FE user to check, must be > 0
     *
     * @return bool whether if the user already is registered
     */
    public function isUserRegistered(int $uid): bool
    {
        $table = 'tx_seminars_attendances';
        $count = self::getConnectionForTable($table)
            ->count('*', $table, ['seminar' => $this->getUid(), 'user' => $uid]);

        return $count > 0;
    }

    /**
     * Checks whether a certain user already is registered for this seminar.
     *
     * @param int $feUserUid UID of the FE user to check, must be > 0
     *
     * @return string empty string if everything is OK, else a localized error message
     */
    public function isUserRegisteredMessage(int $feUserUid): string
    {
        return $this->isUserRegistered($feUserUid) ? $this->translate('message_alreadyRegistered') : '';
    }

    /**
     * Checks whether a certain user is entered as a default VIP for all events
     * but also checks whether this user is entered as a VIP for this event,
     * ie. he/she is allowed to view the list of registrations for this event.
     *
     * @param int $userUid UID of the FE user to check, must be > 0
     * @param int $defaultEventVipsFeGroupUid UID of the default event VIP front-end user group
     *
     * @return bool whether the user is a VIP for this event
     */
    public function isUserVip(int $userUid, int $defaultEventVipsFeGroupUid): bool
    {
        $isDefaultVip = $defaultEventVipsFeGroupUid !== 0
            && FrontEndLoginManager::getInstance()->isLoggedIn()
            && FrontEndLoginManager::getInstance()->getLoggedInUser()
                ->hasGroupMembership((string)$defaultEventVipsFeGroupUid);

        if ($isDefaultVip) {
            $isVip = true;
        } else {
            $table = 'tx_seminars_seminars_feusers_mm';
            $count = self::getConnectionForTable($table)
                ->count('*', $table, ['uid_local' => $this->getUid(), 'uid_foreign' => $userUid]);
            $isVip = $count > 0;
        }

        return $isVip;
    }

    /**
     * Checks whether a FE user is logged in and whether he/she may view this
     * seminar's registrations list or see a link to it.
     * This function can be used to check whether
     * a) a link may be created to the page with the list of registrations
     *    (for $whichPlugin = (seminar_list|my_events|my_vip_events))
     * b) the user is allowed to view the list of registrations
     *    (for $whichPlugin = (list_registrations|list_vip_registrations))
     * c) the user is allowed to export the list of registrations as CSV
     *    ($whichPlugin = csv_export)
     *
     * @param string $whichPlugin
     *        the type of plugin: seminar_list, my_events, my_vip_events,
     *        list_registrations or list_vip_registrations
     * @param int $registrationsListPID
     *        the value of the registrationsListPID parameter
     *        (only relevant for (seminar_list|my_events|my_vip_events))
     * @param int $registrationsVipListPID
     *        the value of the registrationsVipListPID parameter
     *        (only relevant for (seminar_list|my_events|my_vip_events))
     * @param int $defaultEventVipsFeGroupID
     *        the value of the defaultEventVipsGroupID parameter
     *        (only relevant for (list_vip_registration|my_vip_events))
     * @param string $accessLevel
     *        who is allowed to view the front-end registration lists:
     *        "attendees_and_managers", "login" or "world"
     *
     * @return bool TRUE if a FE user is logged in and the user may view
     *                 the registrations list or may see a link to that
     *                 page, FALSE otherwise
     */
    public function canViewRegistrationsList(
        string $whichPlugin,
        int $registrationsListPID = 0,
        int $registrationsVipListPID = 0,
        int $defaultEventVipsFeGroupID = 0,
        string $accessLevel = 'attendees_and_managers'
    ): bool {
        if (!$this->needsRegistration()) {
            return false;
        }

        switch ($accessLevel) {
            case 'world':
                $result = $this->canViewRegistrationsListForWorldAccess(
                    $whichPlugin,
                    $registrationsListPID,
                    $registrationsVipListPID,
                    $defaultEventVipsFeGroupID
                );
                break;
            case 'login':
                $result = $this->canViewRegistrationsListForLoginAccess(
                    $whichPlugin,
                    $registrationsListPID,
                    $registrationsVipListPID,
                    $defaultEventVipsFeGroupID
                );
                break;
            case 'attendees_and_managers':
                // The fall-through is intended.
            default:
                $result = $this->canViewRegistrationsListForAttendeesAndManagersAccess(
                    $whichPlugin,
                    $registrationsListPID,
                    $registrationsVipListPID,
                    $defaultEventVipsFeGroupID
                );
        }

        return $result;
    }

    /**
     * Checks whether a FE user is logged in and whether he/she may view this
     * seminar's registrations list or see a link to it.
     *
     * This function assumes that the access level for FE registration lists is
     * "attendees and managers".
     *
     * @param string $whichPlugin
     *        the type of plugin: seminar_list, my_events, my_vip_events,
     *        list_registrations or list_vip_registrations
     * @param int $registrationsListPID
     *        the value of the registrationsListPID parameter
     *        (only relevant for (seminar_list|my_events|my_vip_events))
     * @param int $registrationsVipListPID
     *        the value of the registrationsVipListPID parameter
     *        (only relevant for (seminar_list|my_events|my_vip_events))
     * @param int $defaultEventVipsFeGroupID
     *        the value of the defaultEventVipsGroupID parameter
     *        (only relevant for (list_vip_registration|my_vip_events))
     *
     * @return bool TRUE if a FE user is logged in and the user may view
     *                 the registrations list or may see a link to that
     *                 page, FALSE otherwise
     */
    protected function canViewRegistrationsListForAttendeesAndManagersAccess(
        string $whichPlugin,
        int $registrationsListPID = 0,
        int $registrationsVipListPID = 0,
        int $defaultEventVipsFeGroupID = 0
    ): bool {
        if (!FrontEndLoginManager::getInstance()->isLoggedIn()) {
            return false;
        }

        $hasListPid = ($registrationsListPID > 0);
        $hasVipListPid = ($registrationsVipListPID > 0);

        $loginManager = FrontEndLoginManager::getInstance();
        $currentUserUid = $loginManager->isLoggedIn()
            ? $loginManager->getLoggedInUser(\Tx_Seminars_Mapper_FrontEndUser::class)->getUid() : 0;

        switch ($whichPlugin) {
            case 'seminar_list':
                // In the standard list view, we could have any kind of link.
                $result = $this->canViewRegistrationsList('my_events', $registrationsListPID)
                    || $this->canViewRegistrationsList(
                        'my_vip_events',
                        0,
                        $registrationsVipListPID,
                        $defaultEventVipsFeGroupID
                    );
                break;
            case 'my_events':
                $result = $this->isUserRegistered($currentUserUid) && $hasListPid;
                break;
            case 'my_vip_events':
                $result = $this->isUserVip($currentUserUid, $defaultEventVipsFeGroupID) && $hasVipListPid;
                break;
            case 'list_registrations':
                $result = $this->isUserRegistered($currentUserUid);
                break;
            case 'list_vip_registrations':
                $result = $this->isUserVip($currentUserUid, $defaultEventVipsFeGroupID);
                break;
            case 'csv_export':
                $result = $this->isUserVip($currentUserUid, $defaultEventVipsFeGroupID)
                    && $this->getConfValueBoolean('allowCsvExportForVips');
                break;
            default:
                $result = false;
        }

        return $result;
    }

    /**
     * Checks whether a FE user is logged in and whether he/she may view this
     * seminar's registrations list or see a link to it.
     *
     * This function assumes that the access level for FE registration lists is
     * "login".
     *
     * @param string $whichPlugin
     *        the type of plugin: seminar_list, my_events, my_vip_events,
     *        list_registrations or list_vip_registrations
     * @param int $registrationsListPID
     *        the value of the registrationsListPID parameter
     *        (only relevant for (seminar_list|my_events|my_vip_events))
     * @param int $registrationsVipListPID
     *        the value of the registrationsVipListPID parameter
     *        (only relevant for (seminar_list|my_events|my_vip_events))
     * @param int $defaultEventVipsFeGroupID
     *        the value of the defaultEventVipsGroupID parameter
     *        (only relevant for (list_vip_registration|my_vip_events))
     *
     * @return bool TRUE if a FE user is logged in and the user may view
     *                 the registrations list or may see a link to that
     *                 page, FALSE otherwise
     */
    protected function canViewRegistrationsListForLoginAccess(
        string $whichPlugin,
        int $registrationsListPID = 0,
        int $registrationsVipListPID = 0,
        int $defaultEventVipsFeGroupID = 0
    ): bool {
        $loginManager = FrontEndLoginManager::getInstance();
        if (!$loginManager->isLoggedIn()) {
            return false;
        }

        $currentUserUid = $loginManager->getLoggedInUser(\Tx_Seminars_Mapper_FrontEndUser::class)->getUid();
        $hasListPid = ($registrationsListPID > 0);
        $hasVipListPid = ($registrationsVipListPID > 0);

        switch ($whichPlugin) {
            case 'csv_export':
                $result = $this->isUserVip($currentUserUid, $defaultEventVipsFeGroupID)
                    && $this->getConfValueBoolean('allowCsvExportForVips');
                break;
            case 'my_vip_events':
                $result = $this->isUserVip($currentUserUid, $defaultEventVipsFeGroupID) && $hasVipListPid;
                break;
            case 'list_vip_registrations':
                $result = $this->isUserVip($currentUserUid, $defaultEventVipsFeGroupID);
                break;
            case 'list_registrations':
                $result = true;
                break;
            default:
                $result = $hasListPid;
        }

        return $result;
    }

    /**
     * Checks whether a FE user is logged in and whether he/she may view this
     * seminar's registrations list or see a link to it.
     *
     * This function assumes that the access level for FE registration lists is
     * "world".
     *
     * @param string $whichPlugin
     *        the type of plugin: seminar_list, my_events, my_vip_events,
     *        list_registrations or list_vip_registrations
     * @param int $registrationsListPID
     *        the value of the registrationsListPID parameter
     *        (only relevant for (seminar_list|my_events|my_vip_events))
     * @param int $registrationsVipListPID
     *        the value of the registrationsVipListPID parameter
     *        (only relevant for (seminar_list|my_events|my_vip_events))
     * @param int $defaultEventVipsFeGroupID
     *        the value of the defaultEventVipsGroupID parameter
     *        (only relevant for (list_vip_registration|my_vip_events))
     *
     * @return bool TRUE if a FE user is logged in and the user may view
     *                 the registrations list or may see a link to that
     *                 page, FALSE otherwise
     */
    protected function canViewRegistrationsListForWorldAccess(
        string $whichPlugin,
        int $registrationsListPID = 0,
        int $registrationsVipListPID = 0,
        int $defaultEventVipsFeGroupID = 0
    ): bool {
        $loginManager = FrontEndLoginManager::getInstance();
        $isLoggedIn = $loginManager->isLoggedIn();

        $hasListPid = ($registrationsListPID > 0);
        $hasVipListPid = ($registrationsVipListPID > 0);
        $currentUserUid = $isLoggedIn ? $loginManager->getLoggedInUser(\Tx_Seminars_Mapper_FrontEndUser::class)->getUid(
        ) : 0;

        switch ($whichPlugin) {
            case 'csv_export':
                $result = $isLoggedIn && $this->isUserVip($currentUserUid, $defaultEventVipsFeGroupID)
                    && $this->getConfValueBoolean('allowCsvExportForVips');
                break;
            case 'my_vip_events':
                $result = $isLoggedIn && $this->isUserVip($currentUserUid, $defaultEventVipsFeGroupID)
                    && $hasVipListPid;
                break;
            case 'list_vip_registrations':
                $result = $isLoggedIn && $this->isUserVip($currentUserUid, $defaultEventVipsFeGroupID);
                break;
            case 'list_registrations':
                $result = true;
                break;
            default:
                $result = $hasListPid;
        }

        return $result;
    }

    /**
     * Checks whether a FE user is logged in and whether he/she may view this
     * seminar's registrations list.
     * This function is intended to be used from the registrations list,
     * NOT to check whether a link to that list should be shown.
     *
     * @param string $whichPlugin
     *        the type of plugin: list_registrations or list_vip_registrations
     * @param string $accessLevel
     *        who is allowed to view the front-end registration lists:
     *        "attendees_and_managers", "login" or "world"
     *
     * @return string an empty string if everything is OK, a localized error
     *                message otherwise
     */
    public function canViewRegistrationsListMessage(
        string $whichPlugin,
        string $accessLevel = 'attendees_and_managers'
    ): string {
        if (!$this->needsRegistration()) {
            return $this->translate('message_noRegistrationNecessary');
        }
        if ($accessLevel === 'world') {
            return '';
        }
        if (!FrontEndLoginManager::getInstance()->isLoggedIn()) {
            return $this->translate('message_notLoggedIn');
        }
        if (
            !$this->canViewRegistrationsList(
                $whichPlugin,
                0,
                0,
                0,
                $accessLevel
            )
        ) {
            return $this->translate('message_accessDenied');
        }

        return '';
    }

    /**
     * Checks whether it is possible at all to register for this seminar,
     * ie. it needs registration at all,
     *     has not been canceled,
     *     has a date set (or registration for events without a date is allowed),
     *     has not begun yet,
     *     the registration deadline is not over yet,
     *     and there are still vacancies.
     *
     * @return bool TRUE if registration is possible, FALSE otherwise
     */
    public function canSomebodyRegister(): bool
    {
        $registrationManager = \Tx_Seminars_Service_RegistrationManager::getInstance();
        $allowsRegistrationByDate
            = $registrationManager->allowsRegistrationByDate($this);
        $allowsRegistrationBySeats
            = $registrationManager->allowsRegistrationBySeats($this);

        return $this->needsRegistration() && !$this->isCanceled()
            && $allowsRegistrationByDate && $allowsRegistrationBySeats;
    }

    /**
     * Checks whether it is possible at all to register for this seminar,
     * ie. it needs registration at all,
     *     has not been canceled,
     *     has either a date set (registration for events without a date is allowed),
     *     has not begun yet,
     *     the registration deadline is not over yet
     *     and there are still vacancies,
     * and returns a localized error message if registration is not possible.
     *
     * @return string empty string if everything is OK, else a localized
     *                error message
     */
    public function canSomebodyRegisterMessage(): string
    {
        $message = '';
        $registrationManager = \Tx_Seminars_Service_RegistrationManager::getInstance();

        if (!$this->needsRegistration()) {
            $message = $this->translate('message_noRegistrationNecessary');
        } elseif ($this->isCanceled()) {
            $message = $this->translate('message_seminarCancelled');
        } elseif (
            !$this->hasDate() &&
            !$this->getConfValueBoolean('allowRegistrationForEventsWithoutDate')
        ) {
            $message = $this->translate('message_noDate');
        } elseif ($this->hasDate() && $this->isRegistrationDeadlineOver()) {
            $message = $this->translate('message_seminarRegistrationIsClosed');
        } elseif (!$registrationManager->allowsRegistrationBySeats($this)) {
            $message = $this->translate('message_noVacancies');
        } elseif (!$registrationManager->registrationHasStarted($this)) {
            $message = sprintf(
                $this->translate('message_registrationOpensOn'),
                $this->getRegistrationBegin()
            );
        }

        return $message;
    }

    /**
     * Checks whether this event has been canceled.
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->getStatus() === \Tx_Seminars_Model_Event::STATUS_CANCELED;
    }

    /**
     * Checks whether the latest possibility to register for this event is over.
     *
     * The latest moment is either the time the event starts, or a set registration deadline.
     *
     * @return bool
     */
    public function isRegistrationDeadlineOver(): bool
    {
        return $GLOBALS['SIM_EXEC_TIME'] >= $this->getLatestPossibleRegistrationTime();
    }

    /**
     * Checks whether the latest possibility to register with early bird rebate for this event is over.
     *
     * The latest moment is just before a set early bird deadline.
     *
     * @return bool TRUE if the deadline has passed, FALSE otherwise
     */
    public function isEarlyBirdDeadlineOver(): bool
    {
        return $GLOBALS['SIM_EXEC_TIME'] >= $this->getLatestPossibleEarlyBirdRegistrationTime();
    }

    /**
     * Checks whether registration is necessary for this event.
     *
     * @return bool
     */
    public function needsRegistration(): bool
    {
        return !$this->isEventTopic() && $this->getRecordPropertyBoolean('needs_registration');
    }

    /**
     * Checks whether this event has unlimited vacancies (needs_registration true and max_attendances 0).
     *
     * @return bool
     */
    public function hasUnlimitedVacancies(): bool
    {
        return $this->needsRegistration() && $this->getAttendancesMax() === 0;
    }

    /**
     * Checks whether this event allows multiple registrations by the same
     * FE user.
     *
     * @return bool TRUE if multiple registrations are allowed,
     *                 FALSE otherwise
     */
    public function allowsMultipleRegistrations(): bool
    {
        return $this->getTopicBoolean('allows_multiple_registrations');
    }

    /**
     * Calculates the attendee statistics. If these numbers already are available, this method is a no-op.
     *
     * @return void
     */
    protected function calculateStatisticsIfNeeded()
    {
        if (!$this->statisticsHaveBeenCalculated) {
            $this->calculateStatistics();
        }
    }

    /**
     * (Re-)calculates the number of participants for this seminar.
     *
     * @return void
     */
    public function calculateStatistics()
    {
        $this->registrationsHaveBeenRetrieved = false;

        $this->numberOfAttendances = $this->getOfflineRegistrations()
            + $this->sumSeatsOfRegistrations($this->getNonQueueRegistrations());
        $this->numberOfAttendancesPaid = $this->sumSeatsOfRegistrations($this->getPaidRegistrations());
        $this->numberOfAttendancesOnQueue = $this->sumSeatsOfRegistrations($this->getQueueRegistrations());

        $this->statisticsHaveBeenCalculated = true;
    }

    private function getNonQueueRegistrations(): array
    {
        $this->retrieveRegistrations();

        return \array_filter(
            $this->registrations,
            static function (array $registration): bool {
                return !(bool)$registration['registration_queue'];
            }
        );
    }

    private function getQueueRegistrations(): array
    {
        $this->retrieveRegistrations();

        return \array_filter(
            $this->registrations,
            static function (array $registration): bool {
                return (bool)$registration['registration_queue'];
            }
        );
    }

    private function getPaidRegistrations(): array
    {
        $this->retrieveRegistrations();

        return \array_filter(
            $this->getNonQueueRegistrations(),
            static function (array $registration): bool {
                return (int)$registration['datepaid'] > 0;
            }
        );
    }

    /**
     * @param array[] $registrations
     *
     * @return int
     */
    private function sumSeatsOfRegistrations(array $registrations): int
    {
        $total = 0;
        foreach ($registrations as $registration) {
            $total += \max(1, (int)$registration['seats']);
        }

        return $total;
    }

    /**
     * @return void
     */
    private function retrieveRegistrations()
    {
        if ($this->registrationsHaveBeenRetrieved) {
            return;
        }

        $table = 'tx_seminars_attendances';
        $this->registrations = self::getConnectionForTable($table)
            ->select(['*'], $table, ['seminar' => $this->getUid()])
            ->fetchAll();

        $this->registrationsHaveBeenRetrieved = true;
    }

    /**
     * Retrieves the topic from the DB and returns it as an object.
     *
     * In case of an error, the return value will be null.
     *
     * @return \Tx_Seminars_OldModel_Event|null
     */
    private function loadTopic()
    {
        return self::fromUid($this->getRecordPropertyInteger('topic'));
    }

    /**
     * Checks whether we are a date record.
     *
     * @return bool TRUE if we are a date record, FALSE otherwise.
     */
    public function isEventDate(): bool
    {
        return $this->getRecordPropertyInteger('object_type') === \Tx_Seminars_Model_Event::TYPE_DATE;
    }

    /**
     * Checks whether we are a topic record.
     *
     * @return bool TRUE if we are a topic record, FALSE otherwise.
     */
    public function isEventTopic(): bool
    {
        return $this->getRecordPropertyInteger('object_type') === \Tx_Seminars_Model_Event::TYPE_TOPIC;
    }

    /**
     * Gets the UID of the topic record if we are a date record. Otherwise, the
     * UID of this record is returned.
     *
     * @return int either the UID of this record or its topic record,
     *                 depending on whether we are a date record
     */
    public function getTopicOrSelfUid(): int
    {
        $topic = $this->getTopic();

        return $topic !== null ? $topic->getUid() : $this->getUid();
    }

    /**
     * Checks a integer element of the record data array for existence and
     * non-emptiness. If we are a date record, it'll be retrieved from the
     * corresponding topic record.
     *
     * @param string $key key of the element to check
     *
     * @return bool TRUE if the corresponding integer exists and is non-empty
     */
    protected function hasTopicInteger(string $key): bool
    {
        $topic = $this->getTopic();

        return $topic !== null ? $topic->hasRecordPropertyInteger($key) : $this->hasRecordPropertyInteger($key);
    }

    /**
     * Gets an int element of the record data array.
     * If the array has not been initialized properly, 0 is returned instead.
     * If we are a date record, it'll be retrieved from the corresponding
     * topic record.
     *
     * @param string $key the name of the field to retrieve
     *
     * @return int the corresponding element from the record data array
     */
    protected function getTopicInteger(string $key): int
    {
        $topic = $this->getTopic();

        return $topic !== null ? $topic->getRecordPropertyInteger($key) : $this->getRecordPropertyInteger($key);
    }

    /**
     * Checks a string element of the record data array for existence and
     * non-emptiness. If we are a date record, it'll be retrieved from the
     * corresponding topic record.
     *
     * @param string $key key of the element to check
     *
     * @return bool TRUE if the corresponding string exists and is non-empty
     */
    private function hasTopicString(string $key): bool
    {
        $topic = $this->getTopic();

        return $topic !== null ? $topic->hasRecordPropertyString($key) : $this->hasRecordPropertyString($key);
    }

    /**
     * Gets a trimmed string element of the record data array.
     * If the array has not been initialized properly, an empty string is
     * returned instead. If we are a date record, it'll be retrieved from the
     * corresponding topic record.
     *
     * @param string $key the name of the field to retrieve
     *
     * @return string the corresponding element from the record data array
     */
    protected function getTopicString(string $key): string
    {
        $topic = $this->getTopic();

        return $topic !== null ? $topic->getRecordPropertyString($key) : $this->getRecordPropertyString($key);
    }

    /**
     * Checks a decimal element of the record data array for existence and a
     * value != 0.00. If we are a date record, it'll be retrieved from the
     * corresponding topic record.
     *
     * @param string $key key of the element to check
     *
     * @return bool TRUE if the corresponding decimal value exists
     *                 and is not 0.00
     */
    private function hasTopicDecimal(string $key): bool
    {
        $topic = $this->getTopic();

        return $topic !== null ? $topic->hasRecordPropertyDecimal($key) : $this->hasRecordPropertyDecimal($key);
    }

    /**
     * Gets a decimal element of the record data array.
     * If the array has not been initialized properly, an empty string is
     * returned instead. If we are a date record, it'll be retrieved from the
     * corresponding topic record.
     *
     * @param string $key the name of the field to retrieve
     *
     * @return string the corresponding element from the record data array
     */
    protected function getTopicDecimal(string $key): string
    {
        $topic = $this->getTopic();

        return $topic !== null ? $topic->getRecordPropertyDecimal($key) : $this->getRecordPropertyDecimal($key);
    }

    /**
     * Gets an element of the record data array, converted to a boolean.
     * If the array has not been initialized properly, FALSE is returned.
     *
     * If we are a date record, it'll be retrieved from the corresponding topic
     * record.
     *
     * @param string $key the name of the field to retrieve
     *
     * @return bool the corresponding element from the record data array
     */
    protected function getTopicBoolean(string $key): bool
    {
        $topic = $this->getTopic();

        return $topic !== null ? $topic->getRecordPropertyBoolean($key) : $this->getRecordPropertyBoolean($key);
    }

    /**
     * Checks whether we have any lodging options.
     *
     * @return bool TRUE if we have at least one lodging option,
     *                 FALSE otherwise
     */
    public function hasLodgings(): bool
    {
        return $this->hasRecordPropertyInteger('lodgings');
    }

    /**
     * Gets the lodging options associated with this event.
     *
     * @return array[] lodging options, consisting each of a nested
     *               array with the keys "caption" (for the title) and "value"
     *               (for the UID), might be empty
     */
    public function getLodgings(): array
    {
        return $this->hasLodgings()
            ? $this->getMmRecordsForSelection('tx_seminars_lodgings', 'tx_seminars_seminars_lodgings_mm') : [];
    }

    /**
     * Gets the lodging options associated with this event.
     *
     * @return string
     */
    protected function getLodgingTitles(): string
    {
        return $this->hasLodgings()
            ? \implode("\n", $this->getMmRecordTitles('tx_seminars_lodgings', 'tx_seminars_seminars_lodgings_mm'))
            : '';
    }

    /**
     * Checks whether we have any food options.
     *
     * @return bool TRUE if we have at least one food option, FALSE otherwise
     */
    public function hasFoods(): bool
    {
        return $this->hasRecordPropertyInteger('foods');
    }

    /**
     * Gets the food options associated with this event.
     *
     * @return array[] food options, consisting each of a nested array
     *               with the keys "caption" (for the title) and "value" (for
     *               the UID), might be empty
     */
    public function getFoods(): array
    {
        return $this->hasFoods()
            ? $this->getMmRecordsForSelection('tx_seminars_foods', 'tx_seminars_seminars_foods_mm') : [];
    }

    /**
     * Gets the food options associated with this event.
     *
     * @return string
     */
    protected function getFoodTitles(): string
    {
        return $this->hasFoods()
            ? \implode("\n", $this->getMmRecordTitles('tx_seminars_foods', 'tx_seminars_seminars_foods_mm'))
            : '';
    }

    /**
     * Checks whether this event has any option checkboxes.
     *
     * @return bool whether this event has at least one option checkbox
     */
    public function hasCheckboxes(): bool
    {
        return $this->hasRecordPropertyInteger('checkboxes');
    }

    /**
     * Gets the option checkboxes associated with this event. If we are a date
     * record, the option checkboxes of the corresponding topic record will be retrieved.
     *
     * @return array[] option checkboxes, consisting each of a nested array
     *                 with the keys "caption" (for the title) and "value" (for the UID), might be empty
     */
    public function getCheckboxes(): array
    {
        return $this->hasCheckboxes()
            ? $this->getMmRecordsForSelection('tx_seminars_checkboxes', 'tx_seminars_seminars_checkboxes_mm') : [];
    }

    /**
     * Gets the UIDs and titles of records referenced by this record. If we are
     * a date record and $useTopicRecord is TRUE, the referenced records of the
     * corresponding topic record will be retrieved.
     *
     * @param string $foreignTable
     *        the name of the foreign table (must not be empty), must have the fields uid and title
     * @param string $mmTable
     *        the name of the m:m table, having the fields uid_local, uid_foreign and sorting, must not be empty
     *
     * @return array[] referenced records, consisting each of a nested
     *               array with the keys "caption" (for the title) and "value"
     *               (for the UID), might be empty
     */
    private function getTopicMmRecords(string $foreignTable, string $mmTable): array
    {
        return $this->getMmRecordsByUid($foreignTable, $mmTable, $this->getTopicInteger('uid'));
    }

    /**
     * @param string $foreignTable
     * @param string $mmTable
     *
     * @return array[] options, consisting each of a nested array with the keys "caption" (for the title) and "value"
     *                 (for the UID), might be empty
     */
    protected function getMmRecordsForSelection(string $foreignTable, string $mmTable): array
    {
        return $this->mmRecordsToSelection($this->getMmRecords($foreignTable, $mmTable));
    }

    /**
     * @param string $foreignTable
     * @param string $mmTable
     *
     * @return array[] options, consisting each of a nested array with the keys "caption" (for the title) and "value"
     *                 (for the UID), might be empty
     */
    protected function getTopicMmRecordsForSelection(string $foreignTable, string $mmTable): array
    {
        return $this->mmRecordsToSelection($this->getTopicMmRecords($foreignTable, $mmTable));
    }

    private function mmRecordsToSelection(array $records): array
    {
        $options = [];
        foreach ($records as $record) {
            $options[] = ['caption' => $record['title'], 'value' => (int)$record['uid']];
        }

        return $options;
    }

    /**
     * Gets the PID of the system folder where the registration records of this
     * event should be stored. If no folder is set in this event's topmost
     * organizer record (ie. the page configured in
     * plugin.tx_seminars.attendancesPID should be used), this function will return 0.
     *
     * @return int the PID of the system folder where registration records
     *                 for this event should be stored or 0 if no folder is set
     */
    public function getAttendancesPid(): int
    {
        if (!$this->hasOrganizers()) {
            return 0;
        }

        return $this->getFirstOrganizer()->getAttendancesPid();
    }

    /**
     * Checks whether this event's topmost organizer has a PID set to store the registration records in.
     *
     * @return bool TRUE if a the system folder for registration is specified in this event's topmost organizers record,
     *                 FALSE otherwise
     */
    public function hasAttendancesPid(): bool
    {
        return $this->getAttendancesPid() !== 0;
    }

    /**
     * Gets this event's owner (the FE user who has created this event).
     *
     * @return FrontEndUser|null
     */
    public function getOwner()
    {
        if (!$this->hasRecordPropertyInteger('owner_feuser')) {
            return null;
        }

        /** @var \Tx_Oelib_Mapper_FrontEndUser $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_FrontEndUser::class);
        /** @var FrontEndUser|null $owner */
        $owner = $mapper->find($this->getRecordPropertyInteger('owner_feuser'));

        return $owner;
    }

    /**
     * Checks whether this event has an existing owner (the FE user who has
     * created this event).
     *
     * @return bool TRUE if this event has an existing owner, FALSE otherwise
     */
    public function hasOwner(): bool
    {
        return $this->hasRecordPropertyInteger('owner_feuser');
    }

    /**
     * Checks whether the logged-in FE user is the owner of this event.
     *
     * @return bool TRUE if a FE user is logged in and the user is
     *                 the owner of this event, FALSE otherwise
     */
    public function isOwnerFeUser(): bool
    {
        $loginManager = FrontEndLoginManager::getInstance();
        if (!$loginManager->isLoggedIn()) {
            return false;
        }

        return $this->getRecordPropertyInteger('owner_feuser')
            === $loginManager->getLoggedInUser(\Tx_Seminars_Mapper_FrontEndUser::class)->getUid();
    }

    /**
     * Checkes whether the "travelling terms" checkbox (ie. the second "terms"
     * checkbox) should be displayed in the registration form for this event.
     *
     * If we are a date record, this is checked for the corresponding topic
     * record.
     *
     * Note: This is not related to entries in the showRegistrationFields
     * configuration variable. This function checks this on a per-event basis
     * whereas showRegistrationFields is a global option.
     *
     * @return bool TRUE if the "travelling terms" checkbox should
     *                 be displayed, FALSE otherwise
     */
    public function hasTerms2(): bool
    {
        return $this->getTopicBoolean('uses_terms_2');
    }

    /**
     * Gets the teaser text (not RTE'ed). If this is a date record, the
     * corresponding topic's teaser text is retrieved.
     *
     * @return string this event's teaser text (or '' if there is an error)
     */
    public function getTeaser(): string
    {
        return $this->getTopicString('teaser');
    }

    /**
     * Checks whether this event (or this event' topic record) has a teaser
     * text.
     *
     * @return bool TRUE if we have a non-empty teaser text,
     *                 FALSE otherwise
     */
    public function hasTeaser(): bool
    {
        return $this->hasTopicString('teaser');
    }

    /**
     * Retrieves a value from this record. The return value will be an empty
     * string if the key is not defined in $this->recordData or if it has not
     * been filled in.
     *
     * If the data needs to be decoded to be readable (eg. the speakers
     * payment or the gender), this function will already return the clear text
     * version.
     *
     * @param string $key the key of the data to retrieve (the key doesn't need to be trimmed)
     *
     * @return string the data retrieved from $this->recordData, may be empty
     */
    public function getEventData(string $key): string
    {
        $trimmedKey = trim($key);

        switch ($trimmedKey) {
            case 'uid':
                $result = (string)$this->getUid();
                break;
            case 'tstamp':
                // The fallthrough is intended.
            case 'crdate':
                $result = \strftime(
                    $this->getConfValueString('dateFormatYMD') . ' ' . $this->getConfValueString('timeFormat'),
                    $this->getRecordPropertyInteger($trimmedKey)
                );
                break;
            case 'title':
                $result = $this->getTitle();
                break;
            case 'subtitle':
                $result = $this->getSubtitle();
                break;
            case 'teaser':
                $result = $this->getTeaser();
                break;
            case 'description':
                $result = $this->getDescription();
                break;
            case 'event_type':
                $result = $this->getEventType();
                break;
            case 'accreditation_number':
                $result = $this->getAccreditationNumber();
                break;
            case 'credit_points':
                $result = $this->getCreditPoints();
                break;
            case 'date':
                $result = $this->getDate('-');
                break;
            case 'time':
                $result = $this->getTime('-');
                break;
            case 'deadline_registration':
                $result = $this->getRegistrationDeadline();
                break;
            case 'deadline_early_bird':
                $result = $this->getEarlyBirdDeadline();
                break;
            case 'deadline_unregistration':
                $result = $this->getUnregistrationDeadline();
                break;
            case 'place':
                $result = $this->getPlaceWithDetailsRaw();
                break;
            case 'room':
                $result = $this->getRoom();
                break;
            case 'lodgings':
                $result = $this->getLodgingTitles();
                break;
            case 'foods':
                $result = $this->getFoodTitles();
                break;
            case 'speakers':
                // The fallthrough is intended.
            case 'partners':
                // The fallthrough is intended.
            case 'tutors':
                // The fallthrough is intended.
            case 'leaders':
                $result = $this->getSpeakersWithDescriptionRaw($trimmedKey);
                break;
            case 'price_regular':
                $result = $this->getPriceRegular();
                break;
            case 'price_regular_early':
                $result = $this->getEarlyBirdPriceRegular();
                break;
            case 'price_regular_board':
                $result = $this->getPriceRegularBoard();
                break;
            case 'price_special':
                $result = $this->getPriceSpecial();
                break;
            case 'price_special_early':
                $result = $this->getEarlyBirdPriceSpecial();
                break;
            case 'price_special_board':
                $result = $this->getPriceSpecialBoard();
                break;
            case 'additional_information':
                $result = $this->getAdditionalInformation();
                break;
            case 'payment_methods':
                $result = $this->getPaymentMethodsPlainShort();
                break;
            case 'organizers':
                $result = $this->getOrganizersRaw();
                break;
            case 'attendees_min':
                $result = (string)$this->getAttendancesMin();
                break;
            case 'attendees_max':
                $result = (string)$this->getAttendancesMax();
                break;
            case 'attendees':
                $result = (string)$this->getAttendances();
                break;
            case 'vacancies':
                $result = (string)$this->getVacancies();
                break;
            case 'enough_attendees':
                $result = $this->hasEnoughAttendances() ? $this->translate('label_yes') : $this->translate('label_no');
                break;
            case 'is_full':
                $result = $this->isFull() ? $this->translate('label_yes') : $this->translate('label_no');
                break;
            case 'cancelled':
                $result = $this->isCanceled() ? $this->translate('label_yes') : $this->translate('label_no');
                break;
            default:
                $result = '';
        }

        $carriageReturnRemoved = (strpos($result, CR) === false)
            ? $result
            : str_replace(CR, LF, $result);

        return preg_replace('/\\x0a{2,}/', LF, $carriageReturnRemoved);
    }

    /**
     * Gets the list of available prices, prepared for a drop-down list.
     * In the sub-arrays, the "caption" element contains the description of
     * the price (e.g. "Standard price" or "Early-bird price"), the "value"
     * element contains a code for the price, but not the price itself (so two
     * different price categories that cost the same are no problem). In
     * addition, the "amount" element contains the amount (without currency).
     *
     * If there is an early-bird price available and the early-bird deadline has
     * not passed yet, the early-bird price is used.
     *
     * This function returns an array of arrays, e.g.
     *
     * 'regular' => (
     *   'value'   => 'regular',
     *   'amount'  => '50.00',
     *   'caption' => 'Regular price: 50 EUR'
     * ),
     * 'regular_board' => (
     *   'value'   => 'regular_board',
     *   'amount'  => '80.00',
     *   'caption' => 'Regular price with full board: 80 EUR'
     * )
     *
     * So the keys for the sub-arrays and their "value" elements are the same.
     *
     * The possible keys are:
     * regular, regular_early, regular_board,
     * special, special_early, special_board
     *
     * The return array's pointer will already be reset to its first element.
     *
     * @return string[][] the available prices as a reset array of arrays with the keys "caption" (for the title) and "value
     *                    (for the price code), might be empty
     */
    public function getAvailablePrices(): array
    {
        $result = [];

        if ($this->hasEarlyBirdPriceRegular() && $this->earlyBirdApplies()) {
            $result['regular_early'] = [
                'value' => 'regular_early',
                'amount' => $this->getEarlyBirdPriceRegularAmount(),
                'caption' => $this->translate('label_price_earlybird_regular')
                    . ': ' . $this->getEarlyBirdPriceRegular(),
            ];
        } else {
            $result['regular'] = [
                'value' => 'regular',
                'amount' => $this->getPriceRegularAmount(),
                'caption' => $this->translate('label_price_regular')
                    . ': ' . $this->getPriceRegular(),
            ];
        }
        if ($this->hasPriceRegularBoard()) {
            $result['regular_board'] = [
                'value' => 'regular_board',
                'amount' => $this->getPriceRegularBoardAmount(),
                'caption' => $this->translate('label_price_board_regular')
                    . ': ' . $this->getPriceRegularBoard(),
            ];
        }

        if ($this->hasPriceSpecial()) {
            if ($this->hasEarlyBirdPriceSpecial() && $this->earlyBirdApplies()) {
                $result['special_early'] = [
                    'value' => 'special_early',
                    'amount' => $this->getEarlyBirdPriceSpecialAmount(),
                    'caption' => $this->translate('label_price_earlybird_special')
                        . ': ' . $this->getEarlyBirdPriceSpecial(),
                ];
            } else {
                $result['special'] = [
                    'value' => 'special',
                    'amount' => $this->getPriceSpecialAmount(),
                    'caption' => $this->translate('label_price_special')
                        . ': ' . $this->getPriceSpecial(),
                ];
            }
        }
        if ($this->hasPriceSpecialBoard()) {
            $result['special_board'] = [
                'value' => 'special_board',
                'amount' => $this->getPriceSpecialBoardAmount(),
                'caption' => $this->translate('label_price_board_special')
                    . ': ' . $this->getPriceSpecialBoard(),
            ];
        }

        // reset the pointer for the result array to the first element
        reset($result);

        return $result;
    }

    /**
     * Checks whether a given price category currently is available for this
     * event.
     *
     * The allowed price category codes are:
     * regular, regular_early, regular_board, special, special_early, special_board
     *
     * @param string $priceCode code for the price category to check, may be empty or null
     *
     * @return bool TRUE if $priceCode matches a currently available
     *                 price, FALSE otherwise
     */
    public function isPriceAvailable(string $priceCode): bool
    {
        $availablePrices = $this->getAvailablePrices();

        return !empty($priceCode) && isset($availablePrices[$priceCode]);
    }

    /**
     * Checks whether this event currently has at least one non-free price
     * (taking into account whether we still are in the early-bird period).
     *
     * @return bool true if this event currently has at least one non-zero price, false otherwise
     */
    public function hasAnyPrice(): bool
    {
        if ($this->earlyBirdApplies()) {
            $result = $this->hasEarlyBirdPriceRegular()
                || $this->hasEarlyBirdPriceSpecial();
        } else {
            $result = $this->hasPriceRegular() || $this->hasPriceSpecial();
        }

        // There is no early-bird version of the prices that include full board.
        $result = $result || $this->hasPriceRegularBoard() || $this->hasPriceSpecialBoard();

        return $result;
    }

    /**
     * Checks whether a front-end user is already blocked during the time for
     * a given event by other booked events.
     *
     * For this, only events that forbid multiple registrations are checked.
     *
     * @param int $uid UID of the FE user to check, must be > 0
     *
     * @return bool true if user is blocked by another registration, false otherwise
     *
     * @throws \InvalidArgumentException
     */
    public function isUserBlocked(int $uid): bool
    {
        if ($uid <= 0) {
            throw new \InvalidArgumentException('$uid must be > 0, but actually is: ' . $uid, 1533310684);
        }
        if ($this->allowsMultipleRegistrations() || $this->skipCollisionCheck() || !$this->hasDate()) {
            return false;
        }

        $additionalTables = 'tx_seminars_attendances';
        $queryWhere = $this->getQueryForCollidingEvents();
        // Filter to those events to which the given FE user is registered.
        $queryWhere .= ' AND tx_seminars_seminars.uid = ' .
            'tx_seminars_attendances.seminar' .
            ' AND tx_seminars_attendances.user = ' . $uid;

        /** @var \Tx_Seminars_Bag_Event $blockingEvents */
        $blockingEvents = GeneralUtility::makeInstance(\Tx_Seminars_Bag_Event::class, $queryWhere, $additionalTables);

        return !$blockingEvents->isEmpty();
    }

    /**
     * @return string
     */
    private function getQueryForCollidingEvents(): string
    {
        if ($this->hasTimeslots()) {
            $timeSpans = [];
            /** @var \Tx_Seminars_OldModel_TimeSlot $timeSlot */
            foreach ($this->getTimeSlots() as $timeSlot) {
                $timeSpans[] = [
                    $timeSlot->getBeginDateAsTimestamp(),
                    $timeSlot->getEndDateAsTimestampEvenIfOpenEnded(),
                ];
            }
        } else {
            $timeSpans = [[$this->getBeginDateAsTimestamp(), $this->getEndDateAsTimestampEvenIfOpenEnded()]];
        }

        $queryWhereParts = [];
        foreach ($timeSpans as list($timeSpanBegin, $timeSpanEnd)) {
            $queryWhereParts[] = $this->getQueryForCollidingEventsForTimeSpan($timeSpanBegin, $timeSpanEnd);
        }

        return '(' . \implode(' OR ', $queryWhereParts) . ')';
    }

    /**
     * Checks whether the collision check should be skipped for this event.
     *
     * @return bool whether the collision check should be skipped for this event
     */
    private function skipCollisionCheck(): bool
    {
        return $this->getConfValueBoolean('skipRegistrationCollisionCheck') ||
            $this->getRecordPropertyBoolean('skip_collision_check');
    }

    /**
     * Creates a WHERE clause that selects events that collide with this event's times.
     *
     * This query will only take events into account that do *not* allow multiple registrations.
     *
     * For open-ended events, only the begin date is checked.
     *
     * @param int $beginDate
     * @param int $endDate
     *
     * @return string WHERE clause (without the "WHERE" keyword), will not be empty
     */
    private function getQueryForCollidingEventsForTimeSpan(int $beginDate, int $endDate): string
    {
        return 'tx_seminars_seminars.uid <> ' . $this->getUid() .
            ' AND allows_multiple_registrations = 0' .
            ' AND skip_collision_check = 0' .
            ' AND (' . $this->getQueryPartForCollidingEventWithoutTimeSlots($beginDate, $endDate) .
            ' OR ' . $this->getQueryPartForCollidingEventWithTimeSlots($beginDate, $endDate) .
            ')';
    }

    /**
     * @param int $beginDate
     * @param int $endDate
     *
     * @return string
     */
    private function getQueryPartForCollidingEventWithoutTimeSlots(int $beginDate, int $endDate): string
    {
        return '(timeslots = 0 AND (' .
            '(' .
            // Check for events that have a begin date in our time-frame.
            // This will automatically rule out events without a date.
            'begin_date > ' . $beginDate . ' AND begin_date < ' . $endDate .
            ') OR (' .
            // Check for events that have an end date in our time-frame.
            // This will automatically rule out events without a date.
            'end_date > ' . $beginDate . ' AND end_date < ' . $endDate .
            ') OR (' .
            // Check for events that have a non-zero start date,
            // start before this event and end after it.
            'begin_date > 0 AND ' .
            'begin_date <= ' . $beginDate . ' AND end_date >= ' . $endDate .
            ')' .
            '))';
    }

    /**
     * @param int $beginDate
     * @param int $endDate
     *
     * @return string
     */
    private function getQueryPartForCollidingEventWithTimeSlots(int $beginDate, int $endDate): string
    {
        return '(timeslots != 0 AND EXISTS (' .
            'SELECT * FROM tx_seminars_timeslots AS t ' .
            'WHERE t.deleted = 0 AND t.seminar = tx_seminars_seminars.uid ' .
            ' AND (' .
            '(' .
            // Check for time slots that have a begin date in our time-frame.
            't.begin_date > ' . $beginDate . ' AND t.begin_date < ' . $endDate .
            ') OR (' .
            // Check for time slots that have an end date in our time-frame.
            't.end_date > ' . $beginDate . ' AND t.end_date < ' . $endDate .
            ') OR (' .
            // Check for time slots that start before this event and end after it.
            't.begin_date <= ' . $beginDate . ' AND t.end_date >= ' . $endDate .
            ')' .
            ')' .
            '))';
    }

    /**
     * Gets the date.
     * Returns an empty string if the seminar record is a topic record.
     * Otherwise will return the date or a localized string "will be
     * announced" if there's no date set.
     *
     * Returns just one day if we take place on only one day.
     * Returns a date range if we take several days.
     *
     * @param string $dash the character or HTML entity used to separate start date and end date
     *
     * @return string the seminar date (or an empty string or a localized message)
     */
    public function getDate(string $dash = '&#8211;'): string
    {
        $result = '';

        if ($this->getRecordPropertyInteger('object_type') !== \Tx_Seminars_Model_Event::TYPE_TOPIC) {
            $result = parent::getDate($dash);
        }

        return $result;
    }

    /**
     * Returns TRUE if the seminar is hidden, otherwise FALSE.
     *
     * @return bool TRUE if the seminar is hidden, FALSE otherwise
     */
    public function isHidden(): bool
    {
        return $this->getRecordPropertyBoolean('hidden');
    }

    /**
     * Returns TRUE if unregistration is possible. That means the unregistration
     * deadline hasn't been reached yet.
     *
     * If the unregistration deadline is not set globally via TypoScript and not
     * set in the current event record, the unregistration will not be possible
     * and this method returns FALSE.
     *
     * @return bool TRUE if unregistration is possible, FALSE otherwise
     */
    public function isUnregistrationPossible(): bool
    {
        if (!$this->needsRegistration()) {
            return false;
        }

        $canUnregisterByQueue = $this->getConfValueBoolean('allowUnregistrationWithEmptyWaitingList')
            || ($this->hasRegistrationQueue() && $this->hasAttendancesOnRegistrationQueue());

        $deadline = $this->getUnregistrationDeadlineFromModelAndConfiguration();
        if ($deadline !== 0 || $this->hasBeginDate()) {
            $canUnregisterByDate = (int)$GLOBALS['SIM_EXEC_TIME'] < $deadline;
        } else {
            $canUnregisterByDate = $this->getUnregistrationDeadlineFromConfiguration() !== 0;
        }

        return $canUnregisterByQueue && $canUnregisterByDate;
    }

    /**
     * Checks if this event has a registration queue.
     *
     * @return bool true if this event has a registration queue, false otherwise
     */
    public function hasRegistrationQueue(): bool
    {
        return $this->getRecordPropertyBoolean('queue_size');
    }

    /**
     * Gets the number of attendances on the registration queue.
     *
     * @return int number of attendances on the registration queue
     */
    public function getAttendancesOnRegistrationQueue(): int
    {
        $this->calculateStatisticsIfNeeded();

        return $this->numberOfAttendancesOnQueue;
    }

    /**
     * Checks whether there is at least one registration on the waiting list.
     *
     * @return bool true if there is at least one registration on the waiting list, false otherwise
     */
    public function hasAttendancesOnRegistrationQueue(): bool
    {
        return $this->getAttendancesOnRegistrationQueue() > 0;
    }

    /**
     * Checks whether there's a (begin) date set or any time slots exist.
     * If there's an end date but no begin date, this function still will return
     * FALSE.
     *
     * @return bool
     */
    public function hasDate(): bool
    {
        return $this->hasBeginDate() || $this->hasTimeslots();
    }

    /**
     * Returns TRUE if the seminar has at least one time slot, otherwise FALSE.
     *
     * @return bool TRUE if the seminar has at least one time slot,
     *                 otherwise FALSE
     */
    public function hasTimeslots(): bool
    {
        return $this->hasRecordPropertyInteger('timeslots');
    }

    /**
     * Returns our time slots in an array.
     *
     * @return array[] time slots or an empty array if there are no time slots
     *               the array contains the following elements:
     *               - 'date' as key and the time slot's begin date as value
     *               - 'time' as key and the time slot's time as value
     *               - 'entry_date' as key and the time slot's entry date as
     *               value
     *               - 'room' as key and the time slot's room as value
     *               - 'place' as key and the time slot's place as value
     *               - 'speakers' as key and the time slot's speakers as value
     */
    public function getTimeSlotsAsArrayWithMarkers(): array
    {
        $result = [];

        /** @var \Tx_Seminars_OldModel_TimeSlot $timeSlot */
        foreach ($this->getTimeSlots() as $timeSlot) {
            $result[] = [
                'uid' => $timeSlot->getUid(),
                'date' => $timeSlot->getDate(),
                'time' => $timeSlot->getTime(),
                'entry_date' => $timeSlot->getEntryDate(),
                'room' => $timeSlot->getRoom(),
                'place' => $timeSlot->getPlaceShort(),
                'speakers' => $timeSlot->getSpeakersShortCommaSeparated(),
            ];
        }

        return $result;
    }

    /**
     * @return \Tx_Seminars_Bag_TimeSlot
     */
    public function getTimeSlots(): \Tx_Seminars_Bag_TimeSlot
    {
        /** @var \Tx_Seminars_Bag_TimeSlot $timeSlotBag */
        $timeSlotBag = GeneralUtility::makeInstance(
            \Tx_Seminars_Bag_TimeSlot::class,
            'tx_seminars_timeslots.seminar = ' . $this->getUid(),
            '',
            '',
            'tx_seminars_timeslots.begin_date ASC'
        );

        return $timeSlotBag;
    }

    /**
     * Checks whether this seminar has at least one category.
     *
     * @return bool TRUE if the seminar has at least one category,
     *                 FALSE otherwise
     */
    public function hasCategories(): bool
    {
        return $this->hasTopicInteger('categories');
    }

    /**
     * Gets the number of categories associated with this event.
     *
     * @return int the number of categories associated with this event,
     *                 will be >= 0
     */
    public function getNumberOfCategories(): int
    {
        return $this->getTopicInteger('categories');
    }

    /**
     * Gets this event's category titles and icons as an associative
     * array (which may be empty), using the category UIDs as keys.
     *
     * @return array[] two-dimensional associative array with the UID as first level key
     *               and "title" and "icon" as second level keys. "Title" will
     *               contain the category title and "icon" will contain the
     *               category icon. Will be an empty array in if the event has
     *               no categories.
     */
    public function getCategories(): array
    {
        if (!$this->hasCategories()) {
            return [];
        }

        /** @var \Tx_Seminars_BagBuilder_Category $builder */
        $builder = GeneralUtility::makeInstance(\Tx_Seminars_BagBuilder_Category::class);
        $builder->limitToEvents((string)$this->getTopicOrSelfUid());
        $builder->sortByRelationOrder();

        $result = [];
        /** @var \Tx_Seminars_OldModel_Category $category */
        foreach ($builder->build() as $category) {
            $result[$category->getUid()] = ['title' => $category->getTitle(), 'icon' => $category->getIcon()];
        }

        return $result;
    }

    /**
     * Returns whether this event has at least one attached file.
     *
     * If this is an event date, this function will return true if the date
     * record or the topic record has at least one file.
     */
    public function hasAttachedFiles(): bool
    {
        return $this->hasRecordPropertyString('attached_files') || $this->hasTopicString('attached_files');
    }

    /**
     * Gets our attached files as an array of arrays with the elements "name"
     * and "size" of the attached file.
     *
     * The displayed file name is relative to the tx_seminars upload directory
     * and is linked to the actual file's URL.
     *
     * The file size will have, depending on the file size, one of the following
     * units appended: K for Kilobytes, M for Megabytes and G for Gigabytes.
     *
     * The returned array will be sorted like the files are sorted in the back-
     * end form.
     *
     * If this event is an event date, this function will return both the
     * topic's file and the date's files (in that order).
     *
     * Note: This functions' return values already are htmlspecialchared.
     *
     * @param AbstractPlugin $plugin an object for a live page
     *
     * @return array<array<string, string>> an array of arrays with the elements "name" and
     *               "size" of the attached file, will be empty if
     *               there are no attached files
     */
    public function getAttachedFiles(AbstractPlugin $plugin): array
    {
        if (!$this->hasAttachedFiles()) {
            return [];
        }

        $topic = $this->getTopic();
        $allFiles = $topic instanceof \Tx_Seminars_OldModel_Event ? $topic->getAttachedFiles($plugin) : [];

        $uploadFolderPath = PATH_site . 'uploads/tx_seminars/';
        $uploadFolderUrl = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'uploads/tx_seminars/';
        $fileNamesFromSelf = GeneralUtility::trimExplode(',', $this->getRecordPropertyString('attached_files'), true);
        foreach ($fileNamesFromSelf as $fileName) {
            $matches = [];
            \preg_match('/\\.(\\w+)$/', \basename($fileName), $matches);

            $allFiles[] = [
                'name' => $plugin->cObj->typoLink(
                    \htmlspecialchars(\basename($fileName), ENT_QUOTES | ENT_HTML5),
                    ['parameter' => $uploadFolderUrl . $fileName]
                ),
                'type' => \htmlspecialchars($matches[1] ?? 'none', ENT_QUOTES | ENT_HTML5),
                'size' => GeneralUtility::formatSize(\filesize($uploadFolderPath . $fileName)),
            ];
        }

        return $allFiles;
    }

    /**
     * Sets our attached files.
     *
     * @param string $attachedFiles
     *        a comma-separated list of the names of attached files which have to exist in "uploads/tx_seminars/"
     *
     * @return void
     */
    public function setAttachedFiles(string $attachedFiles)
    {
        $this->setRecordPropertyString('attached_files', $attachedFiles);
    }

    /**
     * Gets the file name of our image.
     *
     * @return string the file name of our image (relative to the extension's
     *                upload path) or '' if this event has no image
     */
    public function getImage(): string
    {
        return $this->getTopicString('image');
    }

    /**
     * Checks whether we have an image.
     *
     * @return bool
     */
    public function hasImage(): bool
    {
        return $this->hasTopicString('image');
    }

    /**
     * Checks whether this event has any requiring events, ie. topics that are
     * prerequisite for this event
     *
     * @return bool TRUE if this event has any requiring events, FALSE
     *                 otherwise
     */
    public function hasRequirements(): bool
    {
        return $this->hasTopicInteger('requirements');
    }

    /**
     * Checks whether this event has any depending events, ie. topics for which
     * this event is prerequisite.
     *
     * @return bool TRUE if this event has any depending events, FALSE
     *                 otherwise
     */
    public function hasDependencies(): bool
    {
        return $this->hasTopicInteger('dependencies');
    }

    /**
     * Returns the required events for the current event topic, ie. topics that
     * are prerequisites for this event.
     *
     * @return \Tx_Seminars_Bag_Event the required events, will be empty if this
     *                               event has no required events
     */
    public function getRequirements(): \Tx_Seminars_Bag_Event
    {
        /** @var \Tx_Seminars_BagBuilder_Event $builder */
        $builder = GeneralUtility::makeInstance(\Tx_Seminars_BagBuilder_Event::class);
        $builder->limitToRequiredEventTopics($this->getTopicOrSelfUid());
        /** @var \Tx_Seminars_Bag_Event $bag */
        $bag = $builder->build();

        return $bag;
    }

    /**
     * Returns the depending events for the current event topic, ie. topics for
     * which this event is a prerequisite.
     *
     * @return \Tx_Seminars_Bag_Event the depending events, will be empty if
     *                               this event has no depending events
     */
    public function getDependencies(): \Tx_Seminars_Bag_Event
    {
        /** @var \Tx_Seminars_BagBuilder_Event $builder */
        $builder = GeneralUtility::makeInstance(\Tx_Seminars_BagBuilder_Event::class);
        $builder->limitToDependingEventTopics($this->getTopicOrSelfUid());
        /** @var \Tx_Seminars_Bag_Event $bag */
        $bag = $builder->build();

        return $bag;
    }

    /**
     * Checks whether this event has been confirmed.
     *
     * @return bool TRUE if the event has been confirmed, FALSE otherwise
     */
    public function isConfirmed(): bool
    {
        return $this->getStatus() == \Tx_Seminars_Model_Event::STATUS_CONFIRMED;
    }

    /**
     * Checks whether this event has been planned.
     *
     * @return bool TRUE if the event has been planned, FALSE otherwise
     */
    public function isPlanned(): bool
    {
        return $this->getStatus() == \Tx_Seminars_Model_Event::STATUS_PLANNED;
    }

    /**
     * Gets the staus of this event.
     *
     * @return int the status of this event, will be >= 0
     */
    private function getStatus(): int
    {
        return $this->getRecordPropertyInteger('cancelled');
    }

    /**
     * Sets whether this event is planned, canceled or confirmed.
     *
     * @param int $status STATUS_PLANNED, STATUS_CONFIRMED or STATUS_CANCELED
     *
     * @return void
     */
    public function setStatus(int $status)
    {
        $this->setRecordPropertyInteger('cancelled', $status);
    }

    /**
     * Returns the cancelation deadline of this event, depending on the
     * cancelation deadlines of the speakers.
     *
     * Before this function is called assure that this event has a begin date.
     *
     * @return int the cancelation deadline of this event as timestamp, will be >= 0
     *
     * @throws \BadMethodCallException
     */
    public function getCancelationDeadline(): int
    {
        if (!$this->hasBeginDate()) {
            throw new \BadMethodCallException(
                'The event has no begin date. Please call this function only if the event has a begin date.',
                1333291877
            );
        }
        if (!$this->hasSpeakers()) {
            return $this->getBeginDateAsTimestamp();
        }

        $beginDate = $this->getBeginDateAsTimestamp();
        $deadline = $beginDate;
        /** @var \Tx_Seminars_OldModel_Speaker $speaker */
        foreach ($this->getSpeakerBag() as $speaker) {
            $speakerDeadline = $beginDate - ($speaker->getCancelationPeriodInDays() * \Tx_Oelib_Time::SECONDS_PER_DAY);
            $deadline = min($speakerDeadline, $deadline);
        }

        return $deadline;
    }

    /**
     * Sets the "cancelation_deadline_reminder_sent" flag.
     *
     * @return void
     */
    public function setCancelationDeadlineReminderSentFlag()
    {
        $this->setRecordPropertyBoolean(
            'cancelation_deadline_reminder_sent',
            true
        );
    }

    /**
     * Sets the "event_takes_place_reminder_sent" flag.
     *
     * @return void
     */
    public function setEventTakesPlaceReminderSentFlag()
    {
        $this->setRecordPropertyBoolean(
            'event_takes_place_reminder_sent',
            true
        );
    }

    /**
     * Checks whether this event has a license expiry.
     *
     * @return bool TRUE if this event has a license expiry, FALSE otherwise
     */
    public function hasExpiry(): bool
    {
        return $this->hasRecordPropertyInteger('expiry');
    }

    /**
     * Gets this event's license expiry date as a formatted date.
     *
     * @return string this event's license expiry date as a formatted date,
     *                will be empty if this event has no license expiry
     */
    public function getExpiry(): string
    {
        if (!$this->hasExpiry()) {
            return '';
        }

        return strftime(
            $this->getConfValueString('dateFormatYMD'),
            $this->getRecordPropertyInteger('expiry')
        );
    }

    /**
     * Checks whether this event has a begin date for the registration.
     *
     * @return bool TRUE if this event has a begin date for the registration,
     *                 FALSE otherwise
     */
    public function hasRegistrationBegin(): bool
    {
        return $this->hasRecordPropertyInteger('begin_date_registration');
    }

    /**
     * Returns the begin date for the registration of this event as UNIX
     * time-stamp.
     *
     * @return int the begin date for the registration of this event as UNIX
     *                 time-stamp, will be 0 if no begin date for the
     *                 registration is set
     */
    public function getRegistrationBeginAsUnixTimestamp(): int
    {
        return $this->getRecordPropertyInteger('begin_date_registration');
    }

    /**
     * Returns the begin date for the registration of this event.
     * The returned string is formatted using the format configured in
     * dateFormatYMD and timeFormat.
     *
     * This function will return an empty string if this event does not have a
     * registration begin date.
     *
     * @return string the date and time of the registration begin date, will be
     *                an empty string if this event registration begin date
     */
    public function getRegistrationBegin(): string
    {
        if (!$this->hasRegistrationBegin()) {
            return '';
        }

        return strftime(
            $this->getConfValueString('dateFormatYMD') . ' '
            . $this->getConfValueString('timeFormat'),
            $this->getRecordPropertyInteger('begin_date_registration')
        );
    }

    /**
     * Returns the places associated with this event.
     *
     * @return \Tx_Oelib_List with the models for the places of this event, will be empty if this event has no places
     */
    public function getPlaces(): \Tx_Oelib_List
    {
        if (!$this->hasPlace()) {
            return new \Tx_Oelib_List();
        }

        $places = $this->getPlacesAsArray();

        /** @var \Tx_Seminars_Mapper_Place $mapper */
        $mapper = GeneralUtility::makeInstance(\Tx_Seminars_Mapper_Place::class);
        return $mapper->getListOfModels($places);
    }

    /**
     * Checks whether this event has any offline registrations.
     *
     * @return bool TRUE if this event has at least one offline registration,
     *                 FALSE otherwise
     */
    public function hasOfflineRegistrations(): bool
    {
        return $this->hasRecordPropertyInteger('offline_attendees');
    }

    /**
     * Returns the number of offline registrations for this event.
     *
     * @return int the number of offline registrations for this event, will
     *                 be 0 if this event has no offline registrations
     */
    public function getOfflineRegistrations(): int
    {
        return $this->getRecordPropertyInteger('offline_attendees');
    }

    /**
     * Checks whether the organizers have already been informed that the event has enough registrations.
     *
     * @return bool
     */
    public function haveOrganizersBeenNotifiedAboutEnoughAttendees(): bool
    {
        return $this->getRecordPropertyBoolean('organizers_notified_about_minimum_reached');
    }

    /**
     * Sets that the organizers have already been informed that the event has enough registrations.
     *
     * @return void
     */
    public function setOrganizersBeenNotifiedAboutEnoughAttendees()
    {
        $this->setRecordPropertyBoolean('organizers_notified_about_minimum_reached', true);
    }

    /**
     * Checks whether notification e-mail to the organizers are muted.
     *
     * @return bool
     */
    public function shouldMuteNotificationEmails(): bool
    {
        return $this->getRecordPropertyBoolean('mute_notification_emails');
    }

    /**
     * Makes sure that notification e-mail to the organizers are muted.
     *
     * @return void
     */
    public function muteNotificationEmails()
    {
        $this->setRecordPropertyBoolean('mute_notification_emails', true);
    }

    /**
     * Checks whether automatic confirmation/cancelation for this event is enabled.
     *
     * @return bool
     */
    public function shouldAutomaticallyConfirmOrCancel(): bool
    {
        return $this->getRecordPropertyBoolean('automatic_confirmation_cancelation');
    }

    /**
     * Returns the unregistration deadline set by configuration and the begin
     * date as UNIX timestamp.
     *
     * This function may only be called if this event has a begin date.
     *
     * @return int the unregistration deadline as UNIX timestamp determined
     *                 by configuration and the begin date, will be 0 if the
     *                 unregistrationDeadlineDaysBeforeBeginDate is not set
     */
    private function getUnregistrationDeadlineFromConfiguration(): int
    {
        if (!$this->hasConfValueInteger('unregistrationDeadlineDaysBeforeBeginDate')) {
            return 0;
        }

        $secondsForUnregistration = \Tx_Oelib_Time::SECONDS_PER_DAY
            * $this->getConfValueInteger('unregistrationDeadlineDaysBeforeBeginDate');

        return $this->getBeginDateAsTimestamp() - $secondsForUnregistration;
    }

    /**
     * Returns the effective unregistration deadline for this event as UNIX
     * timestamp.
     *
     * @return int the unregistration deadline for this event as UNIX
     *                 timestamp, will be 0 if this event has no begin date
     */
    public function getUnregistrationDeadlineFromModelAndConfiguration(): int
    {
        if ($this->hasUnregistrationDeadline()) {
            return $this->getUnregistrationDeadlineAsTimestamp();
        }

        if (!$this->hasBeginDate()) {
            return 0;
        }

        return $this->getUnregistrationDeadlineFromConfiguration();
    }

    /**
     * Returns this event's publication hash.
     *
     * The publication hash will be empty for published events and non-empty for
     * events that have not been published yet.
     *
     * The publication hash is not related to whether an event is hidden:
     * Visible events may also have a non-empty publication hash.
     *
     * @return string this event's publication hash, will be empty for published
     *                events
     */
    public function getPublicationHash(): string
    {
        return $this->getRecordPropertyString('publication_hash');
    }

    /**
     * Sets this event's publication hash.
     *
     * @param string $hash
     *        the publication hash, use a non-empty string to mark an event as
     *        "not published yet" and an empty string to mark an event as
     *        published
     *
     * @return void
     */
    public function setPublicationHash(string $hash)
    {
        $this->setRecordPropertyString('publication_hash', $hash);
    }

    /**
     * Checks whether this event has been published.
     *
     * Note: The publication status of an event is not related to whether it is
     * hidden or not.
     *
     * @return bool TRUE if this event has been published, FALSE otherwise
     */
    public function isPublished(): bool
    {
        return !$this->hasRecordPropertyString('publication_hash');
    }

    private function getPaymentMethodsAsArray(): array
    {
        return $this->getAssociationAsArray(
            'tx_seminars_payment_methods',
            'tx_seminars_seminars_payment_methods_mm',
            ['title', 'description']
        );
    }

    /**
     * @param string $foreignTableName
     * @param string $mmTableName
     * @param string[] $foreignFields
     *
     * @return array
     */
    private function getAssociationAsArray(string $foreignTableName, string $mmTableName, array $foreignFields): array
    {
        $queryBuilder = self::getQueryBuilderForTable($foreignTableName);

        foreach ($foreignFields as $foreignField) {
            $queryBuilder->addSelect($foreignField);
        }

        return $queryBuilder
            ->from($foreignTableName)
            ->join(
                $foreignTableName,
                $mmTableName,
                'mm',
                $queryBuilder->expr()->eq(
                    $foreignTableName . '.uid',
                    $queryBuilder->quoteIdentifier('mm.uid_foreign')
                )
            )
            ->where(
                $queryBuilder->expr()->eq(
                    'mm.uid_local',
                    $queryBuilder->createNamedParameter($this->getTopicOrSelfUid(), \PDO::PARAM_INT)
                )
            )
            ->orderBy('mm.sorting')
            ->execute()
            ->fetchAll();
    }
}
