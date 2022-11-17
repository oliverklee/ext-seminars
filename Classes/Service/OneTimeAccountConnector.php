<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Connects to FE user accounts and sessions data created by the "onetimeaccount" extension.
 */
class OneTimeAccountConnector implements SingletonInterface
{
    /**
     * @var FrontendUserAuthentication
     */
    private $frontEndUserAuthentication;

    /**
     * @throws \RuntimeException
     */
    public function __construct()
    {
        $frontEndController = $GLOBALS['TSFE'] ?? null;
        if (!$frontEndController instanceof TypoScriptFrontendController) {
            throw new \RuntimeException('No frontend found.', 1668702167);
        }
        $frontEndUserAuthentication = $frontEndController->fe_user;
        if (!$frontEndUserAuthentication instanceof FrontendUserAuthentication) {
            throw new \RuntimeException('Frontend found, but without a FE user authentication.', 1668702517);
        }

        $this->frontEndUserAuthentication = $frontEndUserAuthentication;
    }

    /**
     * Checks whether a login session is active, and that is has been created by the "onetimeaccount" extension.
     */
    public function existsOneTimeAccountLoginSession(): bool
    {
        return $this->frontEndUserAuthentication->getKey('user', 'onetimeaccount') === true;
    }

    /**
     * Returns the user UID of a FE user created by the "onetimeaccount" extension (without a FE login session).
     *
     * @return positive-int|null
     */
    public function getOneTimeAccountUserUid(): ?int
    {
        $uid = $this->frontEndUserAuthentication->getSessionData('onetimeaccountUserUid');
        if (!\is_int($uid) || $uid <= 0) {
            return null;
        }

        return $uid;
    }
}
