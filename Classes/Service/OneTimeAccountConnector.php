<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Connects to FE user accounts and sessions data created by the "onetimeaccount" extension.
 */
class OneTimeAccountConnector implements SingletonInterface
{
    private FrontendUserAuthentication $frontEndUserAuthentication;

    public function __construct()
    {
        $frontEndUserAuthentication = $this->getRequest()->getAttribute('frontend.user');
        \assert($frontEndUserAuthentication instanceof FrontendUserAuthentication);
        $this->frontEndUserAuthentication = $frontEndUserAuthentication;
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

    /**
     * Destroys any onetimeaccount sessions (without login).
     *
     * If a onetimeaccount user UID is available in the session, it will be deleted.
     */
    public function destroyOneTimeSession(): void
    {
        if (\is_int($this->getOneTimeAccountUserUid())) {
            $this->frontEndUserAuthentication->setAndSaveSessionData('onetimeaccountUserUid', null);
        }
    }

    private function getRequest(): ServerRequestInterface
    {
        $request = $GLOBALS['TYPO3_REQUEST'];
        \assert($request instanceof ServerRequestInterface);

        return $request;
    }
}
