<?php
namespace OliverKlee\Seminars\Controller;

use OliverKlee\Seminars\BackEnd\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Configuration controller.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ConfigurationController
{
    /**
     * Injects the request object for the current request or subrequest.
     *
     * As this controller goes only through the main() method, it is rather simple for now.
     *
     * @param ServerRequestInterface $request the current request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface the response with the content
     */
    public function mainAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $GLOBALS['MCONF']['name'] = 'web_seminars';

        /** @var Controller $backEndController */
        $backEndController = GeneralUtility::makeInstance(Controller::class);
        $backEndController->init();
        $backEndController->main();
        $response->getBody()->write($backEndController->getContent());

        return $response;
    }
}
