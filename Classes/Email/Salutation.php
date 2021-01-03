<?php

declare(strict_types=1);

namespace OliverKlee\Seminar\Email;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates a salutation for e-mails.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class Salutation
{
    /**
     * @var \Tx_Oelib_Translator
     */
    private $translator = null;

    /**
     * the constructor
     */
    public function __construct()
    {
        $this->translator = \Tx_Oelib_TranslatorRegistry::get('seminars');
    }

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
                $salutationParts['dear'] = $this->translator->translate('email_hello_informal');
                $salutationParts['name'] = $user->getFirstOrFullName();
                break;
            default:
                $gender = $user->getGender();
                $salutationParts['dear'] = $this->translator->translate('email_hello_formal_' . $gender);
                $salutationParts['title'] = $this->translator->translate('email_salutation_title_' . $gender);
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
            foreach ($hooks as $classReference) {
                $result[] = GeneralUtility::getUserObj($classReference);
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

        $result .= ' ' . sprintf($this->translator->translate('email_eventDate'), $event->getDate('-'));

        if ($event->hasTime() && !$event->hasTimeslots()) {
            $timeToLabelWithPlaceholders = $this->translator->translate('email_timeTo');
            $time = $event->getTime(' ' . $timeToLabelWithPlaceholders . ' ');
            $label = ' ' . (!$event->isOpenEnded()
                    ? $this->translator->translate('email_timeFrom')
                    : $this->translator->translate('email_timeAt'));
            $result .= sprintf($label, $time);
        }

        return $result;
    }
}
