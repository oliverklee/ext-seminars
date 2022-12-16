<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Seminars\Configuration\SharedConfigurationCheck;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Back-end module "Events".
 */
class Controller extends AbstractModule
{
    /**
     * Renders the module.
     */
    public function mainAction(): ResponseInterface
    {
        $this->init();

        return new HtmlResponse($this->main());
    }

    /**
     * Main function of the module.
     *
     * @return string
     */
    public function main(): string
    {
        $languageService = $this->getLanguageService();
        $backEndUser = $this->getBackendUser();

        $document = GeneralUtility::makeInstance(DocumentTemplate::class);
        $content = $document->startPage($languageService->getLL('title')) .
            '<h1>' . $languageService->getLL('title') . '</h1></div>';

        if ($this->id <= 0) {
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                $languageService->getLL('message_noPageTypeSelected'),
                '',
                FlashMessage::INFO
            );
            $this->addFlashMessage($message);

            return $content . $this->getRenderedFlashMessages() . $document->endPage();
        }

        $pageAccess = BackendUtility::readPageAccess($this->id, $this->perms_clause);
        if (!\is_array($pageAccess) && !$backEndUser->isAdmin()) {
            return $content . $this->getRenderedFlashMessages() . $document->endPage();
        }

        if (!$this->hasStaticTemplate()) {
            $message = GeneralUtility::makeInstance(
                FlashMessage::class,
                $languageService->getLL('message_noStaticTemplateFound'),
                '',
                FlashMessage::WARNING
            );
            $this->addFlashMessage($message);

            return $content . $this->getRenderedFlashMessages() . $document->endPage();
        }

        $this->setPageData($pageAccess);
        $content .= GeneralUtility::makeInstance(EventsList::class, $this)->show();

        if (AbstractConfigurationCheck::shouldCheck('seminars')) {
            $configuration = ConfigurationRegistry::get('plugin.tx_seminars');
            $sharedConfigurationCheck = new SharedConfigurationCheck($configuration, 'plugin.tx_seminars');
            $sharedConfigurationCheck->check();
            $content .= \implode("\n", $sharedConfigurationCheck->getWarningsAsHtml());
        }

        return $content . $document->endPage();
    }

    /**
     * Adds a flash message to the queue.
     */
    protected function addFlashMessage(FlashMessage $flashMessage): void
    {
        $defaultFlashMessageQueue = GeneralUtility::makeInstance(FlashMessageService::class)
            ->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }

    /**
     * Returns the rendered flash messages.
     *
     * @return string
     */
    protected function getRenderedFlashMessages(): string
    {
        return GeneralUtility::makeInstance(FlashMessageService::class)
            ->getMessageQueueByIdentifier()->renderFlashMessages();
    }

    /**
     * Checks whether this extension's static template is included on the
     * current page.
     *
     * @return bool TRUE if the static template has been included, FALSE otherwise
     */
    private function hasStaticTemplate(): bool
    {
        return ConfigurationRegistry::get('plugin.tx_seminars')->getAsBoolean('isStaticTemplateLoaded');
    }
}
