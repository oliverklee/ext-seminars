<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class publishes events which are hidden through editing or creation in the FE-editor.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Tx_Seminars_FrontEnd_PublishEvent extends Tx_Oelib_TemplateHelper
{
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
    public function render()
    {
        $this->init([]);

        if (!isset($this->piVars['hash']) || ($this->piVars['hash'] == '')) {
            return $this->translate('message_publishingFailed');
        }

        /** @var Tx_Seminars_Mapper_Event $eventMapper */
        $eventMapper = GeneralUtility::makeInstance(Tx_Seminars_Mapper_Event::class);
        /** @var Tx_Seminars_Model_Event $event */
        $event = $eventMapper->findByPublicationHash($this->piVars['hash']);

        if (($event !== null) && $event->isHidden()) {
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
