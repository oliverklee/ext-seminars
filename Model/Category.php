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

/**
 * This class represents a category.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_seminars_Model_Category extends tx_oelib_Model implements tx_seminars_Interface_Titled {
	/**
	 * Returns our title.
	 *
	 * @return string our title, will not be empty
	 */
	public function getTitle() {
		return $this->getAsString('title');
	}

	/**
	 * Sets our title.
	 *
	 * @param string $title our title to set, must not be empty
	 *
	 * @return void
	 */
	public function setTitle($title) {
		if ($title == '') {
			throw new InvalidArgumentException('The parameter $title must not be empty.', 1333296115);
		}

		$this->setAsString('title', $title);
	}

	/**
	 * Returns the icon of this category.
	 *
	 * @return string the file name of the icon (relative to the extension
	 *                upload path) of the category, will be empty if the
	 *                category has no icon
	 */
	public function getIcon() {
		return $this->getAsString('icon');
	}

	/**
	 * Sets the icon of this category.
	 *
	 * @param string $icon the file name of the icon (relative to the extension upload path) of the category, may be empty
	 *
	 * @return void
	 */
	public function setIcon($icon) {
		$this->setAsString('icon', $icon);
	}

	/**
	 * Returns whether this category has an icon.
	 *
	 * @return bool TRUE if this category has an icon, FALSE otherwise
	 */
	public function hasIcon() {
		return $this->hasString('icon');
	}

	/**
	 * Gets the UID of the single view page for events of this category.
	 *
	 * @return int the single view page, will be 0 if none has been set
	 */
	public function getSingleViewPageUid() {
		return $this->getAsInteger('single_view_page');
	}

	/**
	 * Checks whether this category has a single view page UID set.
	 *
	 * @return bool
	 *         TRUE if this category has a single view page set, FALSE otherwise
	 */
	public function hasSingleViewPageUid() {
		return $this->hasInteger('single_view_page');
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/Category.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/seminars/Model/Category.php']);
}