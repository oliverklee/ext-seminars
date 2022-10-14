<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\FrontEnd;

use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Seminars\Bag\EventBag;
use OliverKlee\Seminars\Mapper\EventMapper;
use OliverKlee\Seminars\OldModel\LegacyEvent;
use OliverKlee\Seminars\Service\RegistrationManager;
use OliverKlee\Seminars\Service\SingleViewLinkBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class is a view which creates the requirements lists for the front end.
 */
class RequirementsList extends AbstractView
{
    /**
     * @var LegacyEvent|null the event to build the requirements list for
     */
    private $event;

    /**
     * @var bool whether to limit the requirements to the events the user still needs to register
     */
    private $limitRequirementsToMissing = false;

    /**
     * @var SingleViewLinkBuilder|null
     */
    private $linkBuilder;

    /**
     * Sets the event to which this view relates.
     *
     * @param LegacyEvent $event the event to build the requirements list for
     */
    public function setEvent(LegacyEvent $event): void
    {
        $this->event = $event;
    }

    /**
     * Limits the requirements list to the requirements the user still needs to register to.
     */
    public function limitToMissingRegistrations(): void
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
        if (!$this->event instanceof LegacyEvent) {
            throw new \BadMethodCallException(
                'No event was set, please set an event before calling render.',
                1333293250
            );
        }

        if (!$this->linkBuilder instanceof SingleViewLinkBuilder) {
            $configuration = $this->getConfigurationWithFlexForms();
            $this->injectLinkBuilder(GeneralUtility::makeInstance(SingleViewLinkBuilder::class, $configuration));
        }

        $output = '';

        $eventMapper = MapperRegistry::get(EventMapper::class);
        /** @var LegacyEvent $requirement */
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
     * @return EventBag the requirements still to be displayed, might be empty
     */
    private function getRequirements(): EventBag
    {
        if ($this->limitRequirementsToMissing) {
            $result = RegistrationManager::getInstance()
                ->getMissingRequiredTopics($this->event);
        } else {
            $result = $this->event->getRequirements();
        }

        return $result;
    }

    public function injectLinkBuilder(SingleViewLinkBuilder $linkBuilder): void
    {
        $this->linkBuilder = $linkBuilder;
    }
}
