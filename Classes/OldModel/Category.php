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
 * This class represents an event category.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Seminars_OldModel_Category extends Tx_Seminars_OldModel_Abstract {
	/**
	 * @var string the name of the SQL table this class corresponds to
	 */
	protected $tableName = 'tx_seminars_categories';

	/**
	 * Returns the icon of this category.
	 *
	 * @return string the file name of the icon (relative to the extension
	 *                upload path) of the category, will be empty if the
	 *                category has no icon
	 */
	public function getIcon() {
		return $this->getRecordPropertyString('icon');
	}
}