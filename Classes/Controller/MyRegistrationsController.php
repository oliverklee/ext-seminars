<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller;

use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Plugin for showing registrations of the currently logged-in FE user.
 */
class MyRegistrationsController extends ActionController
{
    private Context $context;

    private RegistrationRepository $registrationRepository;

    public function __construct(Context $context, RegistrationRepository $registrationRepository)
    {
        $this->context = $context;
        $this->registrationRepository = $registrationRepository;
    }

    public function indexAction(): ResponseInterface
    {
        $userUid = $this->getLoggedInUserUid();
        if ($userUid <= 0) {
            return new ForwardResponse('notLoggedIn');
        }

        $registrations = $this->registrationRepository->findActiveRegistrationsByUser($userUid);
        $this->view->assign('registrations', $registrations);

        return $this->htmlResponse();
    }

    /**
     * @return int<0, max>
     */
    private function getLoggedInUserUid(): int
    {
        $uid = $this->context->getPropertyFromAspect('frontend.user', 'id');

        \assert(\is_int($uid) && $uid >= 0);

        return $uid;
    }

    public function notLoggedInAction(): ResponseInterface
    {
        return $this->htmlResponse()->withStatus(403);
    }
}
