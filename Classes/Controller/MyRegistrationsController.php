<?php

declare(strict_types=1);

namespace OliverKlee\Seminars\Controller;

use OliverKlee\Seminars\Domain\Model\Registration\Registration;
use OliverKlee\Seminars\Domain\Repository\Registration\RegistrationRepository;
use OliverKlee\Seminars\OldModel\LegacyRegistration;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
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
        $loggedInUserUid = $this->getLoggedInUserUid();
        if ($loggedInUserUid <= 0) {
            return new ForwardResponse('notLoggedIn');
        }

        $registrations = $this->registrationRepository->findActiveRegistrationsByUser($loggedInUserUid);
        $this->view->assign('registrations', $registrations);

        return $this->htmlResponse();
    }

    /**
     * @IgnoreValidation("registration")
     */
    public function showAction(Registration $registration): ResponseInterface
    {
        $loggedInUserUid = $this->getLoggedInUserUid();
        if ($loggedInUserUid <= 0) {
            return new ForwardResponse('notLoggedIn');
        }
        if (!$registration->belongsToUser($loggedInUserUid)) {
            return new ForwardResponse('notFound');
        }

        $this->view->assign('registration', $registration);
        $this->view->assign('unregistrationIsPossible', $this->isUnregistrationPossible($registration));

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

    private function isUnregistrationPossible(Registration $registration): bool
    {
        $legacyRegistration = GeneralUtility::makeInstance(LegacyRegistration::class, (int)$registration->getUid());

        return $legacyRegistration->getSeminarObject()->isUnregistrationPossible();
    }

    public function notLoggedInAction(): ResponseInterface
    {
        return $this->htmlResponse()->withStatus(403);
    }

    public function notFoundAction(): ResponseInterface
    {
        return $this->htmlResponse()->withStatus(404);
    }
}
