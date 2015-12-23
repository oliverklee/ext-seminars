<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class creates a salutation for e-mails.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_EmailSalutation {
	/**
	 * @var Tx_Oelib_Translator
	 */
	private $translator = NULL;

	/**
	 * the constructor
	 */
	public function __construct() {
		$this->translator = Tx_Oelib_TranslatorRegistry::getInstance()->get('seminars');
	}

	/**
	 * The destructor. Frees as much memory as possible.
	 */
	public function __destruct() {
		unset($this->translator);
	}

	/**
	 * Creates the salutation for the given user.
	 *
	 * The salutation is localized and gender-specific and contains the name of
	 * the user.
	 *
	 * @param Tx_Seminars_Model_FrontEndUser $user
	 *        the user to create the salutation for
	 *
	 * @return string the localized, gender-specific salutation with a trailing comma, will not be empty
	 */
	public function getSalutation(Tx_Seminars_Model_FrontEndUser $user) {
		$salutationParts = array();

		$salutationMode = Tx_Oelib_ConfigurationRegistry::get('plugin.tx_seminars')->getAsString('salutation');
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
				$hook->modifySalutation($salutationParts);
			}
		}

		return implode(' ', $salutationParts) . ',';
	}

	/**
	 * Gets all hooks for this class.
	 *
	 * @return array the hook objects in an array, will be empty if no hooks have been set
	 */
	private function getHooks() {
		$result = array();

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
	 * @param tx_seminars_seminar $event the event the introduction is for
	 *
	 * @return string the introduction with the event's title and if available date and time, will not be empty
	 *
	 * @throws \InvalidArgumentException
	 */
	public function createIntroduction($introductionBegin, tx_seminars_seminar $event) {
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