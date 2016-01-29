<?php
namespace OliverKlee\Seminars\RealUrl;

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
 * This class adds a RealURL configuration.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Configuration {
	/**
	 * Adds RealURL configuration.
	 *
	 * @param mixed[][] $parameters the RealUrl configuration to modify
	 *
	 * @return mixed[][] the modified RealURL configuration
	 */
	public function addConfiguration(array $parameters) {
		$eventSingleViewPostVariables = array(
			'GETvar' => 'tx_seminars_pi1[showUid]',
			'lookUpTable' => array(
				'table' => 'tx_seminars_seminars',
				'id_field' => 'uid',
				'alias_field' => 'title',
				'addWhereClause' => ' AND NOT deleted',
				'useUniqueCache' => true,
				'useUniqueCache_conf' => array(
					'strtolower' => 1,
					'spaceCharacter' => '-',
				),
				'autoUpdate' => TRUE,
			),
		);

		return array_merge_recursive(
			$parameters['config'],
			array(
				'postVarSets' => array(
					'_DEFAULT' => array(
						'event' => array(
							$eventSingleViewPostVariables,
						),
					),
				),
			)
		);
	}
}