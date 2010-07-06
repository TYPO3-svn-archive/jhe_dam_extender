<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Jari-Hermann Ernst <jari-hermann.ernst@bad-gmbh.de>
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
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   51: class tx_jhedamextender_pi2 extends tslib_pibase
 *   64:     function main($content, $conf)
 *   86:     function getCatMenue($mediaFolder)
 *  123:     function countChilds($catId, $mediaFolder)
 *  150:     function getChilds($catId, $mediaFolder)
 *  188:     function makeLink($title, $catId)
 *
 * TOTAL FUNCTIONS: 5
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'DAM category menue' for the 'jhe_dam_extender' extension.
 *
 * @author	Jari-Hermann Ernst <jari-hermann.ernst@bad-gmbh.de>
 * @package	TYPO3
 * @subpackage	tx_jhedamextender
 */
class tx_jhedamextender_pi2 extends tslib_pibase {
	var $prefixId      = 'tx_jhedamextender_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_jhedamextender_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'jhe_dam_extender';	// The extension key.
	var $pi_checkCHash = true;

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The		content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		$this->pi_initPIFlexForm();
		$this->conf['mediaFolder'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'mediaFolder');

		$content='
			<div' . $this->pi_classParam('catMenue') . '>' . $this->getCatMenue($this->conf['mediaFolder']) . '</div>
			';

		return $this->pi_wrapInBaseClass($content);

	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$mediaFolder: ...
	 * @return	[type]		...
	 */
	function getCatMenue($mediaFolder){

		$this->internal['groupBy'] = '';
		$this->internal['orderBy'] = 'tx_dam_cat.title';
		$this->internal['orderByList']='';
		$this->internal['currentTable'] = 'tx_dam_cat';
		$this->internal['limit'] = '';

		$this->internal['where'] = 'tx_dam_cat.deleted = 0 AND tx_dam_cat.hidden = 0 AND tx_dam_cat.pid = ' . $mediaFolder . ' AND tx_dam_cat.parent_id = 5';

#		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_dam_cat.*',
			$this->internal['currentTable'],
			$this->internal['where'],
			'',
			$this->internal['orderBy']
		);
#echo t3lib_div::debug($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery);
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			#$result .= '<li>' . $row['title'] . ' (' . $row['uid'] . ' / ' . $this->countChilds($row['uid'], $mediaFolder) . ')</li>';

			$result .= '<li>' . $this->makeLink($row['title'], $row['uid']) . '</li>';

			if($this->countChilds($row['uid'], $mediaFolder) > 0) {
				$result .= $this->getChilds($row['uid'], $mediaFolder);
			}
		}

		return '<ul>' .$result . '</ul>';

	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$catId: ...
	 * @param	[type]		$mediaFolder: ...
	 * @return	[type]		...
	 */
	function countChilds($catId, $mediaFolder) {
		$this->internal['groupBy'] = '';
		$this->internal['orderBy'] = 'tx_dam_cat.title';
		$this->internal['orderByList']='';
		$this->internal['currentTable'] = 'tx_dam_cat';
		$this->internal['limit'] = '';

		$this->internal['where'] = 'tx_dam_cat.deleted = 0 AND tx_dam_cat.hidden = 0 AND tx_dam_cat.pid = ' . $mediaFolder . ' AND tx_dam_cat.parent_id = ' . $catId . '';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(tx_dam_cat.title)',
			$this->internal['currentTable'],
			$this->internal['where'],
			'',
			$this->internal['orderBy']
		);

		list($result) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		return $result;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$catId: ...
	 * @param	[type]		$mediaFolder: ...
	 * @return	[type]		...
	 */
	function getChilds($catId, $mediaFolder) {

		$this->internal['groupBy'] = '';
		$this->internal['orderBy'] = 'tx_dam_cat.title';
		$this->internal['orderByList']='';
		$this->internal['currentTable'] = 'tx_dam_cat';
		$this->internal['limit'] = '';

		$this->internal['where'] = 'tx_dam_cat.deleted = 0 AND tx_dam_cat.hidden = 0 AND tx_dam_cat.pid = ' . $mediaFolder . ' AND tx_dam_cat.parent_id = ' . $catId . '';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_dam_cat.*',
			$this->internal['currentTable'],
			$this->internal['where'],
			'',
			$this->internal['orderBy']
		);

		#$result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			#$result .= '<li>' . $row['title'] . ' (' . $row['uid'] . ' / ' . $this->countChilds($row['uid'], $mediaFolder) . ')</li>';

			$result .= '<li>' . $this->makeLink($row['title'], $row['uid']) . '</li>';

			if($this->countChilds($row['uid'], $mediaFolder) > 0) {
				$result .= $this->getChilds($row['uid'], $mediaFolder);
			}
		}

		return '<ul>'.$result.'</ul>';
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$title: ...
	 * @param	[type]		$catId: ...
	 * @return	[type]		...
	 */
	function makeLink($title, $catId) {
		$params = array(
			'damcat' => $catId,
			'no_cache' => 1
		);
		$pid = 1;
		$target = '_self';

		$result = $this->pi_linkToPage(
			$title,
			$pid,
			$target,
			$params
		);
		return $result;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi2/class.tx_jhedamextender_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi2/class.tx_jhedamextender_pi2.php']);
}

?>