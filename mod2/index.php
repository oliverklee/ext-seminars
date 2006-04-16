<?php
/***************************************************************
* Copyright notice
*
* (c) 2006 Mario Rimann (typo3-coding@rimann.li)
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
 * Module 'Events' for the 'seminars' extension.
 *
 * @author	Mario Rimann <typo3-coding@rimann.li>
 */

unset($MCONF);

require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
require_once(PATH_t3lib.'class.t3lib_page.php');

$LANG->includeLLFile('EXT:seminars/mod2/locallang.php');

// This checks permissions and exits if the users has no permission for entry.
$BE_USER->modAccess($MCONF, 1);

class tx_seminars_module2 extends t3lib_SCbase {
	/**
	 * Initializes some variables and also starts the initialization of the parent class.
	 *
	 * @access	public
	 */
	function init() {
		/*
		 * This is a workaround for the wrong generated links. The workaround is needed to
		 * get the right values from the GET Parameter. This workaround is from Elmar Hinz
		 * who also noted this in the bugtracker (http://bugs.typo3.org/view.php?id=2178)
		 */
		$matches = array();
		foreach ($GLOBALS['_GET'] as $key => $value) {
			if (preg_match('/amp;(.*)/', $key, $matches)) {
				$GLOBALS['_GET'][$matches[1]] = $value;
			}
		}
		/* --- END OF Workaround --- */

		parent::init();
		return;
	}

	/**
	 * Main function of the module. Writes the content to $this->content.
	 *
	 * No return value; output is directly written to the page.
	 *
	 * @access	public
	 */
	function main() {
		global $LANG, $BACK_PATH;

		$this->content = '';

		// Read the selected sub module (from the tab menu) and make it available within this class.
		$this->subModule = t3lib_div::_GET('subModule');

		/**
		 * This variable will hold the information about the page. It will only be filled with values
		 * if the user has access to the page.
		 */
		$pageInfo = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
		// Access check:
		// The page will show only if there is a valid page and if this page may be viewed by the user.
		$hasAccess = is_array($pageInfo);

		if (($this->id && $hasAccess) || ($BE_USER->user['admin'])) {
			// start the document
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form = '<form action="" method="POST">';
			$this->doc->docType = 'xhtml_strict';

			// draw the header
			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);

			// define the sub modules that should be available in the tabmenu
			$this->availableSubModules = array();
			$this->availableSubModules[1] = $LANG->getLL('subModuleTitle_registrations');
			$this->availableSubModules[2] = $LANG->getLL('subModuleTitle_events');

			// generate the tabmenu
			$this->content .= $this->doc->getTabMenu(array('id' => $this->id),
				'subModule',
				$this->subModule,
				$this->availableSubModules);
			$this->content .= $this->doc->spacer(5);

			// Select which sub module to display.
			// If no sub module is specified, a default page will be displayed.
			switch ($this->subModule) {
				case 1:
					$this->content .= $LANG->getLL('notYetImplementedSubModule');
					break;
				case 2:
					$this->content .= $LANG->getLL('notYetImplementedSubModule');
					break;
				default:
					$this->content .= $this->showMainPage();
					break;
			}

			// Finish the Document (add </html> tags for example).
			$this->content .= $this->doc->endPage();
		} else {
			// The user doesn't have access.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content .= $this->doc->startPage($LANG->getLL('title'));
			$this->content .= $this->doc->header($LANG->getLL('title'));
			$this->content .= $this->doc->spacer(5);

			// end page
			$this->content .= $this->doc->endPage();
		}
		// Output the whole content.
		echo $this->content;
	}

	/**
	 * Generates and prints out the main page with an introduction about the capabilities of this module.
	 *
	 * @return	string		the HTML source code to display
	 *
	 * @access	public
	 */
	function showMainPage() {
		$content = '';
		$content .= 'This page will be filled up with a short introduction and maybe needful shortcuts.';

		return $content;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/index.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/mod2/index.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_seminars_module2');
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE) {
	include_once($INC_FILE);
}

$SOBE->main();

?>
