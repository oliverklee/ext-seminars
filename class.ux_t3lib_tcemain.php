<?php
/***************************************************************
* Copyright notice
*
* (c) 2007-2008 Niels Pardon (mail@niels-pardon.de)
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
 * Class 'ux_t3lib_TCEmain' for the TYPO3 4.1 core.
 *
 * It will be obsolete as soon as we are requiring at TYPO3 4.2.
 * This class doesn't fit the tx_seminars coding guide lines as the majority of
 * the code is just copied from t3lib_TCEmain.
 *
 * This class extends t3lib_TCEmain via XCLASS with a new hook in
 * t3lib_TCEmain::process_datamap().
 *
 * @package		TYPO3
 * @subpackage	tx_seminars
 * @author		Niels Pardon <mail@niels-pardon.de>
 * @author		Oliver Hader <oh@inpublica.de>
 *
 * @see		https://bugs.oliverklee.com/show_bug.cgi?id=961
 */
class ux_t3lib_TCEmain extends t3lib_TCEmain {
	/**
	 * Processing the data-array
	 * Call this function to process the data-array set by start()
	 *
	 * @return	void
	 */
	function process_datamap() {
		global $TCA, $TYPO3_CONF_VARS;

			// Editing frozen:
		if ($this->BE_USER->workspace!==0 && $this->BE_USER->workspaceRec['freeze'])	{
			$this->newlog('All editing in this workspace has been frozen!',1);
			return FALSE;
		}

			// First prepare user defined objects (if any) for hooks which extend this function:
		$hookObjectsArr = array();
		if (is_array ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'])) {
			foreach ($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'] as $classRef) {
				$hookObjectsArr[] = &t3lib_div::getUserObj($classRef);
			}
		}

			// Organize tables so that the pages-table is always processed first. This is required if you want to make sure that content pointing to a new page will be created.
		$orderOfTables = Array();
		if (isset($this->datamap['pages']))	{		// Set pages first.
			$orderOfTables[]='pages';
		}
		reset($this->datamap);
		while (list($table,) = each($this->datamap))	{
			if ($table!='pages')	{
				$orderOfTables[]=$table;
			}
		}

			// Process the tables...
		foreach($orderOfTables as $table)	{
				/* Check if
					- table is set in $TCA,
					- table is NOT readOnly
					- the table is set with content in the data-array (if not, there's nothing to process...)
					- permissions for tableaccess OK
				*/
			$modifyAccessList = $this->checkModifyAccessList($table);
			if (!$modifyAccessList)	{
				$id = 0;
				$this->log($table,$id,2,0,1,"Attempt to modify table '%s' without permission",1,array($table));
			}
			if (isset($TCA[$table]) && !$this->tableReadOnly($table) && is_array($this->datamap[$table]) && $modifyAccessList)	{
				if ($this->reverseOrder)	{
					$this->datamap[$table] = array_reverse($this->datamap[$table], 1);
				}

					// For each record from the table, do:
					// $id is the record uid, may be a string if new records...
					// $incomingFieldArray is the array of fields
				foreach($this->datamap[$table] as $id => $incomingFieldArray)	{
					if (is_array($incomingFieldArray))	{

							// Hook: processDatamap_preProcessIncomingFieldArray
						foreach($hookObjectsArr as $hookObj)	{
							if (method_exists($hookObj, 'processDatamap_preProcessFieldArray')) {
								$hookObj->processDatamap_preProcessFieldArray($incomingFieldArray, $table, $id, $this);
							}
						}

							// ******************************
							// Checking access to the record
							// ******************************
						$createNewVersion = FALSE;
						$recordAccess = FALSE;
						$old_pid_value = '';
						$resetRejected = FALSE;
						$this->autoVersioningUpdate = FALSE;

						if (!t3lib_div::testInt($id)) {               // Is it a new record? (Then Id is a string)
							$fieldArray = $this->newFieldArray($table);	// Get a fieldArray with default values
							if (isset($incomingFieldArray['pid']))	{	// A pid must be set for new records.
									// $value = the pid
								$pid_value = $incomingFieldArray['pid'];

									// Checking and finding numerical pid, it may be a string-reference to another value
								$OK = 1;
								if (strstr($pid_value,'NEW'))	{	// If a NEW... id
									if (substr($pid_value,0,1)=='-') {$negFlag=-1;$pid_value=substr($pid_value,1);} else {$negFlag=1;}
									if (isset($this->substNEWwithIDs[$pid_value]))	{	// Trying to find the correct numerical value as it should be mapped by earlier processing of another new record.
										$old_pid_value = $pid_value;
										$pid_value=intval($negFlag*$this->substNEWwithIDs[$pid_value]);
									} else {$OK = 0;}	// If not found in the substArray we must stop the process...
								} elseif ($pid_value>=0 && $this->BE_USER->workspace!==0 && $TCA[$table]['ctrl']['versioning_followPages'])	{	// PID points to page, the workspace is an offline space and the table follows page during versioning: This means we must check if the PID page has a version in the workspace with swapmode set to 0 (zero = page+content) and if so, change the pid to the uid of that version.
									if ($WSdestPage = t3lib_BEfunc::getWorkspaceVersionOfRecord($this->BE_USER->workspace, 'pages', $pid_value, 'uid,t3ver_swapmode'))	{	// Looks for workspace version of page.
										if ($WSdestPage['t3ver_swapmode']==0)	{	// if swapmode is zero, then change pid value.
											$pid_value = $WSdestPage['uid'];
										}
									}
								}
								$pid_value = intval($pid_value);

									// The $pid_value is now the numerical pid at this point
								if ($OK)	{
									$sortRow = $TCA[$table]['ctrl']['sortby'];
									if ($pid_value>=0)	{	// Points to a page on which to insert the element, possibly in the top of the page
										if ($sortRow)	{	// If this table is sorted we better find the top sorting number
											$fieldArray[$sortRow] = $this->getSortNumber($table,0,$pid_value);
										}
										$fieldArray['pid'] = $pid_value;	// The numerical pid is inserted in the data array
									} else {	// points to another record before ifself
										if ($sortRow)	{	// If this table is sorted we better find the top sorting number
											$tempArray=$this->getSortNumber($table,0,$pid_value);	// Because $pid_value is < 0, getSortNumber returns an array
											$fieldArray['pid'] = $tempArray['pid'];
											$fieldArray[$sortRow] = $tempArray['sortNumber'];
										} else {	// Here we fetch the PID of the record that we point to...
											$tempdata = $this->recordInfo($table,abs($pid_value),'pid');
											$fieldArray['pid']=$tempdata['pid'];
										}
									}
								}
							}
							$theRealPid = $fieldArray['pid'];

								// Now, check if we may insert records on this pid.
							if ($theRealPid>=0)	{
								$recordAccess = $this->checkRecordInsertAccess($table,$theRealPid);		// Checks if records can be inserted on this $pid.
								if ($recordAccess)	{
									$this->addDefaultPermittedLanguageIfNotSet($table,$incomingFieldArray);
									$recordAccess = $this->BE_USER->recordEditAccessInternals($table,$incomingFieldArray,TRUE);
									if (!$recordAccess)		{
										$this->newlog("recordEditAccessInternals() check failed. [".$this->BE_USER->errorMsg."]",1);
									} elseif(!$this->bypassWorkspaceRestrictions)	{
											// Workspace related processing:
										if ($res = $this->BE_USER->workspaceAllowLiveRecordsInPID($theRealPid,$table))	{	// If LIVE records cannot be created in the current PID due to workspace restrictions, prepare creation of placeholder-record
											if ($res<0)	{
												$recordAccess = FALSE;
												$this->newlog('Stage for versioning root point and users access level did not allow for editing',1);
											}
										} else {	// So, if no live records were allowed, we have to create a new version of this record:
											if ($TCA[$table]['ctrl']['versioningWS'])	{
												$createNewVersion = TRUE;
											} else {
												$recordAccess = FALSE;
												$this->newlog('Record could not be created in this workspace in this branch',1);
											}
										}
									}
								}
							} else {
								debug('Internal ERROR: pid should not be less than zero!');
							}
							$status = 'new';						// Yes new record, change $record_status to 'insert'
						} else {	// Nope... $id is a number
							$fieldArray = array();
							$recordAccess = $this->checkRecordUpdateAccess($table,$id);
							if (!$recordAccess)		{
								$propArr = $this->getRecordProperties($table,$id);
								$this->log($table,$id,2,0,1,"Attempt to modify record '%s' (%s) without permission. Or non-existing page.",2,array($propArr['header'],$table.':'.$id),$propArr['event_pid']);
							} else {	// Next check of the record permissions (internals)
								$recordAccess = $this->BE_USER->recordEditAccessInternals($table,$id);
								if (!$recordAccess)		{
									$propArr = $this->getRecordProperties($table,$id);
									$this->newlog("recordEditAccessInternals() check failed. [".$this->BE_USER->errorMsg."]",1);
								} else {	// Here we fetch the PID of the record that we point to...
									$tempdata = $this->recordInfo($table,$id,'pid'.($TCA[$table]['ctrl']['versioningWS']?',t3ver_wsid,t3ver_stage':''));
									$theRealPid = $tempdata['pid'];

										// Prepare the reset of the rejected flag if set:
									if ($TCA[$table]['ctrl']['versioningWS'] && $tempdata['t3ver_stage']<0)	{
										$resetRejected = TRUE;
									}

										// Checking access in case of offline workspace:
									if (!$this->bypassWorkspaceRestrictions && $errorCode = $this->BE_USER->workspaceCannotEditRecord($table,$tempdata))	{
										$recordAccess = FALSE;		// Versioning is required and it must be offline version!

											// Auto-creation of version: In offline workspace, test if versioning is enabled and look for workspace version of input record. If there is no versionized record found we will create one and save to that.
										if ($this->BE_USER->workspaceAllowAutoCreation($table,$id,$theRealPid))	{
											$tce = t3lib_div::makeInstance('t3lib_TCEmain');
											$tce->stripslashes_values = 0;

												// Setting up command for creating a new version of the record:
											$cmd = array();
											$cmd[$table][$id]['version'] = array(
												'action' => 'new',
												'treeLevels' => -1,	// Default is to create a version of the individual records...
												'label' => 'Auto-created for WS #'.$this->BE_USER->workspace
											);
											$tce->start(array(),$cmd);
											$tce->process_cmdmap();
											$this->errorLog = array_merge($this->errorLog,$tce->errorLog);

											if ($tce->copyMappingArray[$table][$id])	{
												$this->uploadedFileArray[$table][$tce->copyMappingArray[$table][$id]] = $this->uploadedFileArray[$table][$id];
												$id = $this->autoVersionIdMap[$table][$id] = $tce->copyMappingArray[$table][$id];
												$recordAccess = TRUE;
												$this->autoVersioningUpdate = TRUE;
											} else $this->newlog("Could not be edited in offline workspace in the branch where found (failure state: '".$errorCode."'). Auto-creation of version failed!",1);
										} else $this->newlog("Could not be edited in offline workspace in the branch where found (failure state: '".$errorCode."'). Auto-creation of version not allowed in workspace!",1);
									}
								}
							}
							$status = 'update';	// the default is 'update'
						}

							// If access was granted above, proceed to create or update record:
						if ($recordAccess)	{

							list($tscPID) = t3lib_BEfunc::getTSCpid($table,$id,$old_pid_value ? $old_pid_value : $fieldArray['pid']);	// Here the "pid" is set IF NOT the old pid was a string pointing to a place in the subst-id array.
							$TSConfig = $this->getTCEMAIN_TSconfig($tscPID);
							if ($status=='new' && $table=='pages' && is_array($TSConfig['permissions.']))	{
								$fieldArray = $this->setTSconfigPermissions($fieldArray,$TSConfig['permissions.']);
							}
							if ($createNewVersion)	{
								$newVersion_placeholderFieldArray = $fieldArray;
							}

								// Processing of all fields in incomingFieldArray and setting them in $fieldArray
							$fieldArray = $this->fillInFieldArray($table,$id,$fieldArray,$incomingFieldArray,$theRealPid,$status,$tscPID);

								// NOTICE! All manipulation beyond this point bypasses both "excludeFields" AND possible "MM" relations / file uploads to field!

								// Forcing some values unto field array:
							$fieldArray = $this->overrideFieldArray($table,$fieldArray);	// NOTICE: This overriding is potentially dangerous; permissions per field is not checked!!!
							if ($createNewVersion)	{
								$newVersion_placeholderFieldArray = $this->overrideFieldArray($table,$newVersion_placeholderFieldArray);
							}

								// Setting system fields
							if ($status=='new')	{
								if ($TCA[$table]['ctrl']['crdate'])	{
									$fieldArray[$TCA[$table]['ctrl']['crdate']]=time();
									if ($createNewVersion)	$newVersion_placeholderFieldArray[$TCA[$table]['ctrl']['crdate']]=time();
								}
								if ($TCA[$table]['ctrl']['cruser_id'])	{
									$fieldArray[$TCA[$table]['ctrl']['cruser_id']]=$this->userid;
									if ($createNewVersion)	$newVersion_placeholderFieldArray[$TCA[$table]['ctrl']['cruser_id']]=$this->userid;
								}
							} elseif ($this->checkSimilar) {	// Removing fields which are equal to the current value:
								$fieldArray = $this->compareFieldArrayWithCurrentAndUnset($table,$id,$fieldArray);
							}
							if ($TCA[$table]['ctrl']['tstamp'] && count($fieldArray))	{
								$fieldArray[$TCA[$table]['ctrl']['tstamp']]=time();
								if ($createNewVersion)	$newVersion_placeholderFieldArray[$TCA[$table]['ctrl']['tstamp']]=time();
							}
							if ($resetRejected)	{
								$fieldArray['t3ver_stage'] = 0;
							}

								// Hook: processDatamap_postProcessFieldArray
							foreach($hookObjectsArr as $hookObj)	{
								if (method_exists($hookObj, 'processDatamap_postProcessFieldArray')) {
									$hookObj->processDatamap_postProcessFieldArray($status, $table, $id, $fieldArray, $this);
								}
							}

								// Performing insert/update. If fieldArray has been unset by some userfunction (see hook above), don't do anything
								// Kasper: Unsetting the fieldArray is dangerous; MM relations might be saved already and files could have been uploaded that are now "lost"
							if (is_array($fieldArray)) {
								if ($status=='new')	{
									if ($createNewVersion)	{	// This creates a new version of the record with online placeholder and offline version
										$versioningType = $table==='pages' ? $this->BE_USER->workspaceVersioningTypeGetClosest(t3lib_div::intInRange($TYPO3_CONF_VARS['BE']['newPagesVersioningType'],-1,1)) : -1;
										if ($this->BE_USER->workspaceVersioningTypeAccess($versioningType))	{
											$newVersion_placeholderFieldArray['t3ver_label'] = 'INITIAL PLACEHOLDER';
											$newVersion_placeholderFieldArray['t3ver_state'] = 1;	// Setting placeholder state value for temporary record
											$newVersion_placeholderFieldArray['t3ver_wsid'] = $this->BE_USER->workspace;	// Setting workspace - only so display of place holders can filter out those from other workspaces.
											$newVersion_placeholderFieldArray[$TCA[$table]['ctrl']['label']] = '[PLACEHOLDER, WS#'.$this->BE_USER->workspace.']';
											$this->insertDB($table,$id,$newVersion_placeholderFieldArray,FALSE);	// Saving placeholder as 'original'

												// For the actual new offline version, set versioning values to point to placeholder:
											$fieldArray['pid'] = -1;
											$fieldArray['t3ver_oid'] = $this->substNEWwithIDs[$id];
											$fieldArray['t3ver_id'] = 1;
											$fieldArray['t3ver_state'] = -1;	// Setting placeholder state value for version (so it can know it is currently a new version...)
											$fieldArray['t3ver_label'] = 'First draft version';
											$fieldArray['t3ver_wsid'] = $this->BE_USER->workspace;
											if ($table==='pages') {		// Swap mode set to "branch" so we can build branches for pages.
												$fieldArray['t3ver_swapmode'] = $versioningType;
											}
											$phShadowId = $this->insertDB($table,$id,$fieldArray,TRUE,0,TRUE);	// When inserted, $this->substNEWwithIDs[$id] will be changed to the uid of THIS version and so the interface will pick it up just nice!
											if ($phShadowId)	{
												$this->placeholderShadowing($table,$phShadowId);
											}
										} else $this->newlog('Versioning type "'.$versioningType.'" was not allowed, so could not create new record.',1);
									} else {
										$this->insertDB($table,$id,$fieldArray,FALSE,$incomingFieldArray['uid']);
									}
								} else {
									$this->updateDB($table,$id,$fieldArray);
									$this->placeholderShadowing($table,$id);
								}
							}

								/*
								 * Hook: processDatamap_afterDatabaseOperations
								 *
								 * Note: When using the hook after INSERT operations, you will only get the temporary NEW... id passed to your hook as $id,
								 *		 but you can easily translate it to the real uid of the inserted record using the $this->substNEWwithIDs array.
								 */
								$this->hook_processDatamap_afterDatabaseOperations($hookObjectsArr, $status, $table, $id, $fieldArray);
						}	// if ($recordAccess)	{
					}	// if (is_array($incomingFieldArray))	{
				}
			}
		}

			// Process the stack of relations to remap/correct
		$this->processRemapStack();

		$this->dbAnalysisStoreExec();
		$this->removeRegisteredFiles();

		/*
		 * Hook: processDatamap_afterAllOperations
		 *
		 * Note: When this hook gets called, all operations on the submitted
		 * data have been finished.
		 */
		foreach($hookObjectsArr as $hookObj) {
			if (method_exists($hookObj, 'processDatamap_afterAllOperations')) {
				$hookObj->processDatamap_afterAllOperations($this);
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.ux_t3lib_tcemain.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/class.ux_t3lib_tcemain.php']);
}
?>
