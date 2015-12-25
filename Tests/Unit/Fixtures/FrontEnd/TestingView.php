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
 * This class represents a view for testing purposes.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Seminars_Tests_Fixtures_FrontEnd_TestingView extends Tx_Seminars_FrontEnd_AbstractView {
	/**
	 * Renders the view and returns its content.
	 *
	 * @return string the view's content
	 */
	public function render() {
		return 'Hi, I am the testingFrontEndView!';
	}
}