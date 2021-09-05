<?php

declare(strict_types=1);

use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class is a view which creates the requirements lists for the front end.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_FrontEnd_RequirementsList extends \Tx_Seminars_FrontEnd_AbstractView
{
    /**
     * @var \Tx_Seminars_OldModel_Event|null the event to build the requirements list for
     */
    private $event = null;

    /**
     * @var bool whether to limit the requirements to the events the user still needs to register
     */
    private $limitRequirementsToMissing = false;

    /**
     * @var \Tx_Seminars_Service_SingleViewLinkBuilder
     */
    private $linkBuilder = null;

    /**
     * Sets the event to which this view relates.
     *
     * @param \Tx_Seminars_OldModel_Event $event the event to build the requirements list for
     *
     * @return void
     */
    public function setEvent(\Tx_Seminars_OldModel_Event $event)
    {
        $this->event = $event;
    }

    /**
     * Limits the requirements list to the requirements the user still needs to register to.
     *
     * @return void
     */
    public function limitToMissingRegistrations()
    {
        if (!FrontEndLoginManager::getInstance()->isLoggedIn()) {
            throw new \BadMethodCallException(
                'No FE user is currently logged in. Please call this function only when a FE user is logged in.',
                1333293236
            );
        }
        $this->setMarker(
            'label_requirements',
            $this->translate('label_registration_requirements')
        );
        $this->limitRequirementsToMissing = true;
    }

    /**
     * Creates the list of required events.
     *
     * @return string HTML code of the list, will not be empty
     */
    public function render(): string
    {
        if (!$this->event instanceof Tx_Seminars_OldModel_Event) {
            throw new \BadMethodCallException(
                'No event was set, please set an event before calling render.',
                1333293250
            );
        }

        if ($this->linkBuilder == null) {
            /** @var \Tx_Seminars_Service_SingleViewLinkBuilder $linkBuilder */
            $linkBuilder = GeneralUtility::makeInstance(\Tx_Seminars_Service_SingleViewLinkBuilder::class);
            $this->injectLinkBuilder($linkBuilder);
        }
        $this->linkBuilder->setPlugin($this);

        $output = '';

        $eventMapper = MapperRegistry::get(\Tx_Seminars_Mapper_Event::class);
        /** @var \Tx_Seminars_OldModel_Event $requirement */
        foreach ($this->getRequirements() as $requirement) {
            $event = $eventMapper->find($requirement->getUid());

            $singleViewUrl = $this->linkBuilder->createRelativeUrlForEvent($event);
            $this->setMarker(
                'requirement_url',
                \htmlspecialchars($singleViewUrl, ENT_QUOTES | ENT_HTML5)
            );

            $this->setMarker(
                'requirement_title',
                \htmlspecialchars($event->getTitle(), ENT_QUOTES | ENT_HTML5)
            );
            $output .= $this->getSubpart('SINGLE_REQUIREMENT');
        }
        $this->setSubpart('SINGLE_REQUIREMENT', $output);

        return $this->getSubpart('FIELD_WRAPPER_REQUIREMENTS');
    }

    /**
     * Returns the requirements which should be displayed.
     *
     * @return \Tx_Seminars_Bag_Event the requirements still to be displayed,
     *                               might be empty
     */
    private function getRequirements(): \Tx_Seminars_Bag_Event
    {
        if ($this->limitRequirementsToMissing) {
            $result = \Tx_Seminars_Service_RegistrationManager::getInstance()
                ->getMissingRequiredTopics($this->event);
        } else {
            $result = $this->event->getRequirements();
        }

        return $result;
    }

    /**
     * Injects a link builder.
     *
     * @param \Tx_Seminars_Service_SingleViewLinkBuilder $linkBuilder
     *        the link builder instance to use
     *
     * @return void
     */
    public function injectLinkBuilder(
        \Tx_Seminars_Service_SingleViewLinkBuilder $linkBuilder
    ) {
        $this->linkBuilder = $linkBuilder;
    }
}
