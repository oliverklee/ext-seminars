<?php
/***************************************************************
* Copyright notice
*
* (c) 2009-2010 Bernd Schönbach <bernd@oliverklee.de>
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Class 'tx_seminars_EmailSalutation' for the 'seminars' extension.
 *
 * This class creates a salutation for e-mails.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class tx_seminars_EmailSalutation {
	/**
	 * @var tx_oelib_Translator the translator for the localized salutation
	 */
	private $translator = null;

	/**
	 * the constructor
	 */
	public function __construct() {
		$this->translator = tx_oelib_TranslatorRegistry::getInstance()->get('seminars');
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
	 * @param tx_seminars_Model_FrontEndUser $user
	 *        the user to create the salutation for
	 *
	 * @return string the localized, gender-specific salutation with a trailing
	 *                comma, will not be empty
	 */
	public function getSalutation(tx_seminars_Model_FrontEndUser $user) {
		$salutationParts = array();

		$salutationMode = tx_oelib_ConfigurationRegistry::getInstance()->get(
			'seminars')->getAsString('salutation');
		switch ($salutationMode) {
			case 'informal':
				$salutationParts['dear'] = $this->translator->translate(
					'email_hello_informal'
				);
				$salutationParts['name'] = $user->getFirstOrFullName();
				break;
			default:
				$gender = $user->getGender();
				$salutationParts['dear'] = $this->translator->translate(
					'email_hello_formal_' . $gender
				);
				$salutationParts['title'] = $this->translator->translate(
						'email_salutation_title_' . $gender
					);
				$salutationParts['name'] = $user->getLastOrFullName();
				break;
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
	 * @return array the hook objects in an array, will be empty if no hooks
	 *               have been set
	 */
	private function getHooks() {
		$result = array();

		$hooks = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['seminars']
			['modifyEmailSalutation'];
		if (is_array($hooks)) {
			foreach ($hooks as $classReference) {
				$result[] = t3lib_div::getUserObj($classReference);
			}
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_EmailSalutation.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.tx_seminars_EmailSalutation.php']);
}
?>