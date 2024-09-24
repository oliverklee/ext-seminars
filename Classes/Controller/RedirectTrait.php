<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * This convenience trait provides a method to redirect to a different action that is also compatible
 * with TYPO3 12LTS and 13LTS.
 *
 * @phpstan-require-extends ActionController
 *
 * @internal
 */
trait RedirectTrait
{
    /**
     * @param array<string, string|int> $arguments
     */
    private function buildRedirectToAction(
        string $actionName,
        ?string $controllerName = null,
        ?string $extensionName = null,
        array $arguments = [],
        ?int $pageUid = null
    ): ResponseInterface {
        if ($controllerName === null) {
            $controllerName = $this->request->getControllerName();
        }
        $this->uriBuilder->reset()->setCreateAbsoluteUri(true);
        if (\is_int($pageUid)) {
            $this->uriBuilder->setTargetPageUid($pageUid);
        }
        if (GeneralUtility::getIndpEnv('TYPO3_SSL')) {
            $this->uriBuilder->setAbsoluteUriScheme('https');
        }
        $uri = $this->uriBuilder->uriFor($actionName, $arguments, $controllerName, $extensionName);

        return $this->responseFactory->createResponse(307)->withHeader('Location', $uri);
    }
}
