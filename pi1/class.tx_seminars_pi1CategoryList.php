<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Niels Pardon (mail@niels-pardon.de)
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

require_once(t3lib_extMgm::extPath('seminars') . 'lib/tx_seminars_constants.php');
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_templatehelper.php');
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_seminarbagbuilder.php');
require_once(t3lib_extMgm::extPath('seminars') . 'class.tx_seminars_categorybagbuilder.php');

/**
 * Class 'pi1CategoryList' for the 'seminars' extension.
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 *
 * @author		Oliver Klee <typo3-coding@oliverklee.de>
 * @author		Niels Pardon <mail@niels-pardon.de>
 */
class tx_seminars_pi1CategoryList extends tx_seminars_templatehelper {
	/**
	 * @var	string		same as class name
	 */
	public $prefixId = 'tx_seminars_pi1';

	/**
	 * @var	string		path to this script relative to the extension dir
	 */
	public $scriptRelPath = 'pi1/class.tx_seminars_pi1CategoryList.php';

	/**
	 * The constructor. Initializes the TypoScript configuration, initializes
	 * the flex forms, gets the template HTML code, sets the localized labels
	 * and set the CSS classes from TypoScript.
	 *
	 * @param	array		TypoScript configuration for the plugin
	 * @param	tslib_cObj	the parent cObj content, needed for the flexforms
	 */
	public function __construct($conf, tslib_cObj $cObj) {
		$this->cObj = $cObj;
		$this->init($conf);
		$this->pi_initPIflexForm();

		$this->getTemplateCode();
		$this->setLabels();
		$this->setCSS();
	}

	/**
	 * Creates a HTML list of categories.
	 *
	 * This list is limited to categories for which there are events in the
	 * selected time-frame and in the selected sysfolders. Categories for which
	 * all events are canceled will always be ignored.
	 *
	 * @return	string		HTML code of the category list or a formatted
	 * 						message if there are no categories to display
	 */
	public function createCategoryList() {
		$seminarBagBuilder = t3lib_div::makeInstance(
			'tx_seminars_seminarbagbuilder'
		);
		$seminarBagBuilder->setSourcePages(
			$this->getConfValueString('pages'),
			$this->getConfValueInteger('recursive')
		);

		$seminarBagBuilder->ignoreCanceledEvents();
		try {
			$seminarBagBuilder->setTimeFrame(
				$this->getConfValueString(
					'timeframeInList', 's_template_special'
				)
			);
		} catch (Exception $exception) {
			// Ignores the exception because the user will be warned of the
			// problem by the configuration check.
		}

		$eventUids = $seminarBagBuilder->build()->getUids();

		$categoryBagBuilder = t3lib_div::makeInstance(
			'tx_seminars_categorybagbuilder'
		);
		$categoryBagBuilder->limitToEvents($eventUids);
		$categoryBag = $categoryBagBuilder->build();

		// Only lists categories for which there are events.
		if (($eventUids != '')
			&& ($categoryBag->getObjectCountWithoutLimit() > 0)
		) {
			$allCategories = '';
			$rowCounter = 0;

			while ($categoryBag->getCurrent()) {
				$link = $this->createLinkToListViewLimitedByCategory(
					$categoryBag->getCurrent()->getUid(),
					$categoryBag->getCurrent()->getTitle()
				);
				$this->setMarker('category_title', $link);

				$cssClass = ($rowCounter % 2) ? ' class="listrow-odd"' : '';
				$this->setMarker('class_category_item', $cssClass);

				$allCategories .= $this->getSubpart('SINGLE_CATEGORY_ITEM');
				$categoryBag->getNext();
				$rowCounter ++;
			}

			$this->setMarker('all_category_items', $allCategories);
			$result = $this->getSubpart('VIEW_CATEGORIES');
		} else {
			$result = $this->getSubpart('VIEW_NO_CATEGORIES');
		}

		return $result;
	}

	/**
	 * Creates a hyperlink with the title $title to the current list view,
	 * limited to the category provided by the parameter $categoryUid.
	 *
	 * @param	integer		UID of the category to which the list view should
	 * 						be limited, must be > 0
	 * @param	string		title of the link, must not be empty
	 *
	 * @return	string		link to the list view limited to the given
	 * 						category or an empty string if there is an error
	 */
	public function createLinkToListViewLimitedByCategory(
		$categoryUid, $title
	) {
		if ($categoryUid <= 0) {
			throw new Exception('$categoryUid must be > 0.');
		}
		if ($title == '') {
			throw new Exception('$title must not be empty.');
		}

		return $this->cObj->getTypoLink(
			$title,
			$this->getConfValueInteger('listPID'),
			array('tx_seminars_pi1[category]' => $categoryUid)
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1CategoryList.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi1/class.tx_seminars_pi1CategoryList.php']);
}
?>