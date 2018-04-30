<?php
namespace OliverKlee\Seminars\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with TYPO3 source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Configuration controller.
 */
class ConfigurationController
{
    /**
     * Injects the request object for the current request or subrequest
     * As this controller goes only through the main() method, it is rather simple for now
     *
     * @param ServerRequestInterface $request the current request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface the response with the content
     */
    public function mainAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $GLOBALS['MCONF']['name'] = 'web_seminars';

        /** @var \OliverKlee\Seminars\BackEnd\Controller $SOBE */
        $SOBE = GeneralUtility::makeInstance(\OliverKlee\Seminars\BackEnd\Controller::class);
        $SOBE->init();
        $SOBE->main();
        $response->getBody()->write($SOBE->getContent());

        return $response;
    }
}
