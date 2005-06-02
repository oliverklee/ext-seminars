<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Oliver Klee (typo3-coding@oliverklee.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin 'My Seminars' for the 'seminars' extension.
 *
 * @author	Oliver Klee <typo-coding@oliverklee.de>
 */

require_once(t3lib_extMgm::extPath('salutationswitcher').'class.tx_salutationswitcher.php');

class tx_seminars_pi2 extends tx_salutationswitcher {
	/** Same as class name */
	var $prefixId = 'tx_seminars_pi2';
	/**  Path to this script relative to the extension dir. */
	var $scriptRelPath = 'pi2/class.tx_seminars_pi2.php';
	/** The extension key. */
	var $extKey = 'seminars';

	/** the HTML template subparts */
	var $templateCache = array();


	/**
	 * [Put your description here]
	 *
	 * @param	string		Default content string, ignore
	 * @param	array		TypoScript configuration for the plugin
	 * @return	string		HTML for the plugin
	 */
	function main($content,$conf)	{
		switch((string)$conf["CMD"])	{
			case "singleView":
				list($t) = explode(":",$this->cObj->currentRecord);
				$this->internal["currentTable"]=$t;
				$this->internal["currentRow"]=$this->cObj->data;
				return $this->pi_wrapInBaseClass($this->singleView($content,$conf));
			break;
			default:
				if (strstr($this->cObj->currentRecord,"tt_content"))	{
					$conf["pidList"] = $this->cObj->data["pages"];
					$conf["recursive"] = $this->cObj->data["recursive"];
				}
				return $this->pi_wrapInBaseClass($this->listView($content,$conf));
			break;
		}
	}

	/**
	 * [Put your description here]
	 *
	 * @param	[type]		$content: ...
	 * @param	[type]		$conf: ...
	 * @return	[type]		...
	 */
	function listView($content,$conf)	{
		$this->conf=$conf;		// Setting the TypoScript passed to this function in $this->conf
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();		// Loading the LOCAL_LANG values

		$lConf = $this->conf["listView."];	// Local settings for the listView function

		if ($this->piVars["showUid"])	{	// If a single element should be displayed:
			$this->internal["currentTable"] = "tx_seminars_seminars";
			$this->internal["currentRow"] = $this->pi_getRecord("tx_seminars_seminars",$this->piVars["showUid"]);

			$content = $this->singleView($content,$conf);
			return $content;
		} else {
			$items=array(
				"1"=> $this->pi_getLL("list_mode_1","Mode 1"),
				"2"=> $this->pi_getLL("list_mode_2","Mode 2"),
				"3"=> $this->pi_getLL("list_mode_3","Mode 3"),
			);
			if (!isset($this->piVars["pointer"]))	$this->piVars["pointer"]=0;
			if (!isset($this->piVars["mode"]))	$this->piVars["mode"]=1;

				// Initializing the query parameters:
			list($this->internal["orderBy"],$this->internal["descFlag"]) = explode(":",$this->piVars["sort"]);
			$this->internal["results_at_a_time"]=t3lib_div::intInRange($lConf["results_at_a_time"],0,1000,3);		// Number of results to show in a listing.
			$this->internal["maxPages"]=t3lib_div::intInRange($lConf["maxPages"],0,1000,2);;		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
			$this->internal["searchFieldList"]="title,subtitle,description,room,notes";
			$this->internal["orderByList"]="uid,title,subtitle";

				// Get number of records:
			$res = $this->pi_exec_query("tx_seminars_seminars",1);
			list($this->internal["res_count"]) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

				// Make listing query, pass query to SQL database:
			$res = $this->pi_exec_query("tx_seminars_seminars");
			$this->internal["currentTable"] = "tx_seminars_seminars";

				// Put the whole list together:
			$fullTable="";	// Clear var;
		#	$fullTable.=t3lib_div::view_array($this->piVars);	// DEBUG: Output the content of $this->piVars for debug purposes. REMEMBER to comment out the IP-lock in the debug() function in t3lib/config_default.php if nothing happens when you un-comment this line!

				// Adds the mode selector.
			$fullTable.=$this->pi_list_modeSelector($items);

				// Adds the whole list table
			$fullTable.=$this->pi_list_makelist($res);

				// Adds the search box:
			$fullTable.=$this->pi_list_searchBox();

				// Adds the result browser:
			$fullTable.=$this->pi_list_browseresults();

				// Returns the content from the plugin.
			return $fullTable;
		}
	}
	/**
	 * [Put your description here]
	 *
	 * @param	[type]		$content: ...
	 * @param	[type]		$conf: ...
	 * @return	[type]		...
	 */
	function singleView($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();


			// This sets the title of the page for use in indexed search results:
		if ($this->internal["currentRow"]["title"])	$GLOBALS["TSFE"]->indexedDocTitle=$this->internal["currentRow"]["title"];

		$content='<div'.$this->pi_classParam("singleView").'>
			<H2>Record "'.$this->internal["currentRow"]["uid"].'" from table "'.$this->internal["currentTable"].'":</H2>
			<table>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("title").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("title").'</p></td>
				</tr>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("subtitle").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("subtitle").'</p></td>
				</tr>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("description").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("description").'</p></td>
				</tr>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("begin_date").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("begin_date").'</p></td>
				</tr>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("end_date").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("end_date").'</p></td>
				</tr>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("place").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("place").'</p></td>
				</tr>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("room").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("room").'</p></td>
				</tr>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("speakers").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("speakers").'</p></td>
				</tr>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("price_regular").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("price_regular").'</p></td>
				</tr>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("price_special").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("price_special").'</p></td>
				</tr>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("organizers").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("organizers").'</p></td>
				</tr>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("needs_registration").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("needs_registration").'</p></td>
				</tr>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("attendees_min").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("attendees_min").'</p></td>
				</tr>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("attendees_max").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("attendees_max").'</p></td>
				</tr>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("cancelled").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("cancelled").'</p></td>
				</tr>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("enough_attendees").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("enough_attendees").'</p></td>
				</tr>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("is_full").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("is_full").'</p></td>
				</tr>
				<tr>
					<td nowrap valign="top"'.$this->pi_classParam("singleView-HCell").'><p>'.$this->getFieldHeader("notes").'</p></td>
					<td valign="top"><p>'.$this->getFieldContent("notes").'</p></td>
				</tr>
				<tr>
					<td nowrap'.$this->pi_classParam("singleView-HCell").'><p>Last updated:</p></td>
					<td valign="top"><p>'.date("d-m-Y H:i",$this->internal["currentRow"]["tstamp"]).'</p></td>
				</tr>
				<tr>
					<td nowrap'.$this->pi_classParam("singleView-HCell").'><p>Created:</p></td>
					<td valign="top"><p>'.date("d-m-Y H:i",$this->internal["currentRow"]["crdate"]).'</p></td>
				</tr>
			</table>
		<p>'.$this->pi_list_linkSingle($this->pi_getLL("back","Back"),0).'</p></div>'.
		$this->pi_getEditPanel();

		return $content;
	}
	/**
	 * [Put your description here]
	 *
	 * @param	[type]		$c: ...
	 * @return	[type]		...
	 */
	function pi_list_row($c)	{
		$editPanel = $this->pi_getEditPanel();
		if ($editPanel)	$editPanel="<TD>".$editPanel."</TD>";

		return '<tr'.($c%2 ? $this->pi_classParam("listrow-odd") : "").'>
				<td><p>'.$this->getFieldContent("uid").'</p></td>
				<td valign="top"><p>'.$this->getFieldContent("title").'</p></td>
				<td valign="top"><p>'.$this->getFieldContent("subtitle").'</p></td>
				<td valign="top"><p>'.$this->getFieldContent("begin_date").'</p></td>
				<td valign="top"><p>'.$this->getFieldContent("end_date").'</p></td>
				<td valign="top"><p>'.$this->getFieldContent("place").'</p></td>
				<td valign="top"><p>'.$this->getFieldContent("speakers").'</p></td>
				<td valign="top"><p>'.$this->getFieldContent("price_regular").'</p></td>
				<td valign="top"><p>'.$this->getFieldContent("price_special").'</p></td>
				<td valign="top"><p>'.$this->getFieldContent("organizers").'</p></td>
				<td valign="top"><p>'.$this->getFieldContent("needs_registration").'</p></td>
				<td valign="top"><p>'.$this->getFieldContent("attendees_min").'</p></td>
				<td valign="top"><p>'.$this->getFieldContent("attendees_max").'</p></td>
				<td valign="top"><p>'.$this->getFieldContent("cancelled").'</p></td>
				<td valign="top"><p>'.$this->getFieldContent("enough_attendees").'</p></td>
				<td valign="top"><p>'.$this->getFieldContent("is_full").'</p></td>
				'.$editPanel.'
			</tr>';
	}
	/**
	 * [Put your description here]
	 *
	 * @return	[type]		...
	 */
	function pi_list_header()	{
		return '<tr'.$this->pi_classParam("listrow-header").'>
				<td><p>'.$this->getFieldHeader_sortLink("uid").'</p></td>
				<td><p>'.$this->getFieldHeader_sortLink("title").'</p></td>
				<td><p>'.$this->getFieldHeader_sortLink("subtitle").'</p></td>
				<td nowrap><p>'.$this->getFieldHeader("begin_date").'</p></td>
				<td nowrap><p>'.$this->getFieldHeader("end_date").'</p></td>
				<td nowrap><p>'.$this->getFieldHeader("place").'</p></td>
				<td nowrap><p>'.$this->getFieldHeader("speakers").'</p></td>
				<td nowrap><p>'.$this->getFieldHeader("price_regular").'</p></td>
				<td nowrap><p>'.$this->getFieldHeader("price_special").'</p></td>
				<td nowrap><p>'.$this->getFieldHeader("organizers").'</p></td>
				<td nowrap><p>'.$this->getFieldHeader("needs_registration").'</p></td>
				<td nowrap><p>'.$this->getFieldHeader("attendees_min").'</p></td>
				<td nowrap><p>'.$this->getFieldHeader("attendees_max").'</p></td>
				<td nowrap><p>'.$this->getFieldHeader("cancelled").'</p></td>
				<td nowrap><p>'.$this->getFieldHeader("enough_attendees").'</p></td>
				<td nowrap><p>'.$this->getFieldHeader("is_full").'</p></td>
			</tr>';
	}
	/**
	 * [Put your description here]
	 *
	 * @param	[type]		$fN: ...
	 * @return	[type]		...
	 */
	function getFieldContent($fN)	{
		switch($fN) {
			case "title":
				// This will wrap the title in a link.
				// The "1" means that the display of single items is CACHED! Set to zero to disable caching.
				return $this->pi_list_linkSingle($this->internal["currentRow"]["title"],$this->internal["currentRow"]["uid"], 0);
			break;
			case "begin_date":
				return strftime("%d-%m-%y %H:%M:%S",$this->internal["currentRow"]["begin_date"]);
			break;
			case "end_date":
				return strftime("%d-%m-%y %H:%M:%S",$this->internal["currentRow"]["end_date"]);
			break;
			default:
				return $this->internal["currentRow"][$fN];
			break;
		}
	}
	/**
	 * [Put your description here]
	 *
	 * @param	[type]		$fN: ...
	 * @return	[type]		...
	 */
	function getFieldHeader($fN)	{
		switch($fN) {
			case "title":
				return $this->pi_getLL("listFieldHeader_title","<em>title</em>");
			break;
			default:
				return $this->pi_getLL("listFieldHeader_".$fN,"[".$fN."]");
			break;
		}
	}

	/**
	 * [Put your description here]
	 *
	 * @param	[type]		$fN: ...
	 * @return	[type]		...
	 */
	function getFieldHeader_sortLink($fN)	{
		return $this->pi_linkTP_keepPIvars($this->getFieldHeader($fN),array("sort"=>$fN.":".($this->internal["descFlag"]?0:1)));
	}

	/**
	 * Retrieve the subparts from the plugin template and write them to $this->templateCache.
	 */
	function getTemplateCode() {
		/** the whole template file as a string */
		$templateCode = $this->cObj->fileResource($this->conf['templateFile']);

		foreach (array('MY_VIEW_CE_TOP', 'MY_VIEW_MODE', 'MY_VIEW_HEAD', 'MY_VIEW_ITEM', 'MY_VIEW_FOOT', 'MY_VIEW_CE_BOTTOM') as $currentKey) {
			$this->templateCache[$currentKey] = $this->cObj->getSubpart($templateCode, '###'.$currentKey.'###');
		}
	} 
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi2/class.tx_seminars_pi2.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/seminars/pi2/class.tx_seminars_pi2.php']);
}

?>