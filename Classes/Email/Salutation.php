<?php

declare(strict_types=1);

namespace OliverKlee\Seminar\Email;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * This class creates a salutation for e-mails.
 */
class Salutation
{
    /**
     * Creates the salutation for the given user.
     *
     * The salutation is localized and gender-specific and contains the name of
     * the user.
     *
     * @param \Tx_Seminars_Model_FrontEndUser $user
     *        the user to create the salutation for
     *
     * @return string the localized, gender-specific salutation with a trailing comma, will not be empty
     */
    public function getSalutation(\Tx_Seminars_Model_FrontEndUser $user): string
    {
        $salutationParts = [];

        $salutationMode = ConfigurationRegistry::get('plugin.tx_seminars')->getAsString('salutation');
        switch ($salutationMode) {
            case 'informal':
                $salutationParts['dear'] = LocalizationUtility::translate('email_hello_informal', 'seminars');
                $salutationParts['name'] = $user->getFirstOrFullName();
                break;
            default:
                $gender = $user->getGender();
                $salutationParts['dear'] = LocalizationUtility::translate('email_hello_formal_' . $gender, 'seminars');
                $salutationParts['title'] = LocalizationUtility::translate('email_salutation_title_' . $gender, 'seminars');
                $salutationParts['name'] = $user->getLastOrFullName();
        }

        foreach ($this->getHooks() as $hook) {
            if (method_exists($hook, 'modifySalutation')) {
                $hook->modifySalutation($salutationParts, $user);
            }
        }

        return implode(' ', $salutationParts) . ',';
    }

    /**
     * Gets all hooks for this class.
     *
     * @return array the hook objects in an array, will be empty if no hooks have been set
     */
    private function getHooks(): array
    {
        $result = [];

        $hooks = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']['modifyEmailSalutation'];
        if (is_array($hooks)) {
            /** @var array<array-key, class-string> $hooks */
            foreach ($hooks as $classReference) {
                $result[] = GeneralUtility::makeInstance($classReference);
            }
        }

        return $result;
    }

    /**
     * Creates an e-mail introduction with the given event's title, date and
     * time prepended with the given introduction string.
     *
     * @param string $introductionBegin
     *        the start of the introduction, must not be empty and contain %s as
     *        place to fill the title of the event in
     * @param \Tx_Seminars_OldModel_Event $event the event the introduction is for
     *
     * @return string the introduction with the event's title and if available date and time, will not be empty
     *
     * @throws \InvalidArgumentException
     */
    public function createIntroduction(string $introductionBegin, \Tx_Seminars_OldModel_Event $event): string
    {
        if ($introductionBegin === '') {
            throw new \InvalidArgumentException('$introductionBegin must not be empty.', 1440109640);
        }

        $result = sprintf($introductionBegin, $event->getTitle());

        if (!$event->hasDate()) {
            return $result;
        }
        $result .= ' ' . sprintf(
            LocalizationUtility::translate('email_eventDate', 'seminars'),
            $event->getDate('-')
        );

        if ($event->hasTime() && !$event->hasTimeslots()) {
            $timeToLabelWithPlaceholders = LocalizationUtility::translate('email_timeTo', 'seminars');
            $time = $event->getTime(' ' . $timeToLabelWithPlaceholders . ' ');
            $label = ' ' . (!$event->isOpenEnded()
                    ? LocalizationUtility::translate('email_timeFrom', 'seminars')
                    : LocalizationUtility::translate('email_timeAt', 'seminars'));
            $result .= sprintf($label, $time);
        }

        return $result;
    }
}
