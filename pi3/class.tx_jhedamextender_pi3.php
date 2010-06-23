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
 * Hint: use extdeveval to insert/update function index above.
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'DAM special usage menue' for the 'jhe_dam_extender' extension.
 *
 * @author	Jari-Hermann Ernst <jari-hermann.ernst@bad-gmbh.de>
 * @package	TYPO3
 * @subpackage	tx_jhedamextender
 */
class tx_jhedamextender_pi3 extends tslib_pibase {
	var $prefixId      = 'tx_jhedamextender_pi3';		// Same as class name
	var $scriptRelPath = 'pi3/class.tx_jhedamextender_pi3.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'jhe_dam_extender';	// The extension key.
	var $pi_checkCHash = true;
	
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		
		$this->pi_initPIFlexForm();
		$this->conf['mediaFolder'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'mediaFolder');
	
		$this->conf['selectCategory'] = t3lib_div::_GET('damcat');
		
		$content='
				<div' . $this->pi_classParam('specialUsageList') . '>' . $this->getSpecialUsageItems($this->conf['mediaFolder'], $this->conf['selectCategory']) . '</div>
			';
	
		return $this->pi_wrapInBaseClass($content);
	}
	
	function getSpecialUsageItems($mediaFolder, $selectedCategory) {
		
		$this->internal['currentTable'] = 'tx_jhedamextender_usage';
		$this->internal['where'] = 'tx_jhedamextender_usage.deleted = 0 AND tx_jhedamextender_usage.hidden = 0 AND tx_jhedamextender_usage.pid = ' . $mediaFolder;

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			$this->internal['currentTable'],
			$this->internal['where']
		);

		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$result .= '<li>' . $this->makeLink($row['usage_ea619ffddc'], $row['uid'], $selectedCategory) . '</li>';
		}

		return '<ul>' .$result . '</ul>';

	}
	
	function makeLink($title, $specialUsageId, $selectedCategory) {
		$params = array(
			'specialUsage' => $specialUsageId,
			'damcat' => $selectedCategory,
			'no_cache' => 1
		);
		$pid = 9;
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



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi3/class.tx_jhedamextender_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi3/class.tx_jhedamextender_pi3.php']);
}

?>