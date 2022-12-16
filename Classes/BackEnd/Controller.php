<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\BackEnd;

use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Seminars\Configuration\SharedConfigurationCheck;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\DocumentTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
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

        $pageRenderer = $this->getPageRenderer();
        $pageRenderer->addCssFile(
            '../typo3conf/ext/seminars/Resources/Public/CSS/BackEnd/BackEnd.css',
            'stylesheet',
            'all',
            '',
            false
        );

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

        $this->availableSubModules = [];

        if ($backEndUser->check('tables_select', 'tx_seminars_seminars')) {
            $this->availableSubModules[1] = $languageService->getLL('subModuleTitle_events');
        }
        if ($backEndUser->check('tables_select', 'tx_seminars_attendances')) {
            $this->availableSubModules[2] = $languageService->getLL('subModuleTitle_registrations');
        }

        // Read the selected sub module (from the tab menu) and make it available within this class.
        $this->subModule = (int)GeneralUtility::_GET('subModule');

        // If $this->subModule is not a key of $this->availableSubModules,
        // set it to the key of the first element in $this->availableSubModules
        // so the first tab is activated.
        if (!\array_key_exists($this->subModule, $this->availableSubModules)) {
            reset($this->availableSubModules);
            $this->subModule = key($this->availableSubModules);
        }

        // Only generate the tab menu if the current back-end user has the
        // rights to show any of the tabs.
        if ($this->subModule > 0) {
            $moduleToken = FormProtectionFactory::get()->generateToken('moduleCall', self::MODULE_NAME);
            $content .= $this->getTabMenu(
                ['M' => self::MODULE_NAME, 'moduleToken' => $moduleToken, 'id' => $this->id],
                'subModule',
                (string)$this->subModule,
                $this->availableSubModules
            );
        }

        switch ($this->subModule) {
            case 2:
                $content .= GeneralUtility::makeInstance(RegistrationsList::class, $this)->show();
                break;
            case 1:
                if ($this->isGeneralEmailFormRequested()) {
                    $content .= $this->getGeneralMailForm();
                } else {
                    $content .= GeneralUtility::makeInstance(EventsList::class, $this)->show();
                }
                break;
            default:
            // nothing to do
            }

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
     * Checks whether the user requested the form for sending an e-mail and
     * whether all pre-conditions for showing the form are met.
     *
     * @return bool whether the form was requested and pre-conditions are met
     */
    private function isGeneralEmailFormRequested(): bool
    {
        if ($this->getEventUid() <= 0) {
            return false;
        }

        return GeneralUtility::_POST('action') === 'sendEmail';
    }

    /**
     * @return int
     */
    private function getEventUid(): int
    {
        return (int)GeneralUtility::_POST('eventUid');
    }

    /**
     * Returns the form to send an e-mail.
     *
     * @return string the HTML source for the form
     */
    private function getGeneralMailForm(): string
    {
        $form = GeneralUtility::makeInstance(GeneralEventMailForm::class, $this->getEventUid());
        $form->setPostData(GeneralUtility::_POST());

        return $form->render();
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

    /**
     * Creates a tab menu from an array definition.
     *
     * @param array $mainParams a parameter array which will be passed instead of the &id=.
     * @param string $elementName it the form elements name, probably something like "SET[...]
     * @param string $currentValue is the value to be selected currently.
     * @param array $menuItems is an array with the menu items for the selector box
     *
     * @return string HTML code for tab menu
     */
    protected function getTabMenu(
        array $mainParams,
        string $elementName,
        string $currentValue,
        array $menuItems
    ): string {
        $menuDefinition = [];
        foreach ($menuItems as $value => $label) {
            $allParameters = \array_merge($mainParams, [$elementName => (string)$value]);

            $menuDefinition[$value]['isActive'] = $currentValue === (string)$value;
            $menuDefinition[$value]['label'] = \htmlspecialchars($label, ENT_QUOTES | ENT_HTML5);
            $menuDefinition[$value]['url'] = $this->getRouteUrl(self::MODULE_NAME, $allParameters);
        }

        return $this->getTabMenuRaw($menuDefinition);
    }

    /**
     * Creates the HTML content for the tab menu.
     *
     * @param array $menuItems menu items for tabs
     *
     * @return string table HTML
     */
    private function getTabMenuRaw(array $menuItems): string
    {
        $options = '';
        foreach ($menuItems as $id => $definition) {
            $class = $definition['isActive'] ? 'active' : '';
            $label = $definition['label'];
            $url = \htmlspecialchars($definition['url'], ENT_QUOTES | ENT_HTML5);
            $params = $definition['addParams'];

            $options .= '<li class="' . $class . '">' .
                '<a href="' . $url . '" ' . $params . '>' . $label . '</a>' .
                '</li>';
        }

        return '<ul class="nav nav-tabs" role="tablist">' .
            $options .
            '</ul>';
    }

    /**
     * Returns the URL to a given module.
     *
     * @param string $moduleName name of the module
     * @param array $urlParameters URL parameters that should be added as key-value pairs
     *
     * @return string calculated URL
     */
    protected function getRouteUrl(string $moduleName, array $urlParameters = []): string
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        try {
            $uri = $uriBuilder->buildUriFromRoute($moduleName, $urlParameters);
        } catch (RouteNotFoundException $e) {
            // no route registered, use the fallback logic to check for a module
            // @phpstan-ignore-next-line This line is for TYPO3 9LTS only, and we check with 10LTS.
            $uri = $uriBuilder->buildUriFromModule($moduleName, $urlParameters);
        }

        return (string)$uri;
    }
}
