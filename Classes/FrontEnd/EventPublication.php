<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\FrontEnd;

use OliverKlee\Oelib\Templating\TemplateHelper;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\Model\Event;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class publishes events which are hidden through editing or creation in the FE editor.
 *
 * @deprecated will be removed in seminars 5.0
 */
class EventPublication extends TemplateHelper
{
    /**
     * @var int
     */
    public const PUBLICATION_TYPE_NUMBER = 737;

    /**
     * @var string the prefix used for the piVars
     */
    public $prefixId = 'tx_seminars_publication';

    /**
     * faking $this->scriptRelPath so the locallang.xlf file is found
     *
     * @var string
     */
    public $scriptRelPath = 'Resources/Private/Language/locallang.xlf';

    /**
     * @var string the extension key
     */
    public $extKey = 'seminars';

    /**
     * Creates the HTML for the event publishing.
     *
     * This will just output a success or fail line for the event publishing page.
     *
     * @return string HTML code for the event publishing, will not be empty
     */
    public function render(): string
    {
        $this->init([]);

        if (empty($this->piVars['hash'])) {
            return $this->translate('message_publishingFailed');
        }

        $eventMapper = GeneralUtility::makeInstance(EventMapper::class);
        $event = $eventMapper->findByPublicationHash($this->piVars['hash']);

        if ($event instanceof Event && $event->isHidden()) {
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
