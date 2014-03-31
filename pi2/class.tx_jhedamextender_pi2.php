<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010-14 Jari-Hermann Ernst <jari-hermann.ernst@bad-gmbh.de>
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

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath("jhe_dam_extender") . 'util/util.php');

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
	public function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIFlexForm();
		$this->conf['mediaFolder'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'mediaFolder');
		$this->conf['parentCat'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'parentCat');
		$this->conf['backPage'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'backPage');

		$content='
			<div' . $this->pi_classParam('catMenue') . '>' . $this->getCatMenue($this->conf) . '</div>
		';

	return $this->pi_wrapInBaseClass($content);

	}

	/**
	 * Generates a menu structure depending on the category tree
	 *
	 * @param	array		$conf: PlugIn Configuration
	 * @return	string		$result: ul content
	 */
	public function getCatMenue($conf){
		$this->conf = $conf;

		$orderBy = 'tx_dam_cat.title';
		$currentTable = 'tx_dam_cat';
		$where = 'tx_dam_cat.deleted = 0 AND tx_dam_cat.hidden = 0 AND tx_dam_cat.pid = ' . $this->conf['mediaFolder'] . ' AND tx_dam_cat.parent_id = ' . $this->conf['parentCat'];

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_dam_cat.*',
			$currentTable,
			$where,
			'',
			$orderBy
		);

		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$this->conf['catId'] = $row['uid'];
			$this->conf['catTitle'] = $row['title'];

			$result .= '<li>' . $this->makeLink($this->conf) . '</li>';

			if($this->countChilds($this->conf) > 0) {
				$result .= $this->getChilds($this->conf);
			}
		}

		$result = '<ul>' .$result . '</ul>';

		return $result;

	}

	/**
	 * Counts category childs
	 *
	 * @param	array		$conf: PlugIn Configuration
	 * @return	string		$result: number of child categories
	 */
	public function countChilds($conf) {
		$this->conf = $conf;

		$orderBy = 'tx_dam_cat.title';
		$currentTable = 'tx_dam_cat';
		$where = 'tx_dam_cat.deleted = 0 AND tx_dam_cat.hidden = 0 AND tx_dam_cat.pid = ' . $this->conf['mediaFolder'] . ' AND tx_dam_cat.parent_id = ' . $this->conf['catId'] . '';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(tx_dam_cat.title)',
			$currentTable,
			$where,
			'',
			$orderBy
		);
		list($result) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		return $result;
	}

	/**
	 * gets all child categories for a given parent category
	 *
	 * @param	array		$conf: PlugIn Configuration
	 * @return	string		$result: ul content
	 */
	public function getChilds($conf) {
		$this->conf = $conf;

		$orderBy = 'tx_dam_cat.title';
		$currentTable = 'tx_dam_cat';
		$where = 'tx_dam_cat.deleted = 0 AND tx_dam_cat.hidden = 0 AND tx_dam_cat.pid = ' . $this->conf['mediaFolder'] . ' AND tx_dam_cat.parent_id = ' . $this->conf['catId'] . '';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_dam_cat.*',
			$currentTable,
			$where,
			'',
			$orderBy
		);

		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$this->conf['catId'] = $row['uid'];
			$this->conf['catTitle'] = $row['title'];

			$result .= '<li>' . $this->makeLink($this->conf) . '</li>';

			if($this->countChilds($this->conf) > 0) {
				$result .= $this->getChilds($this->conf);
			}
		}

		$result = '<ul>'.$result.'</ul>';

		return $result;
	}

	/**
	 * Generates the links
	 *
	 * @param	array		$conf: PlugIn Configuration
	 * @return	string		$result: Link to page and category data
	 */
	public function makeLink($conf) {
		$this->conf = $conf;

		$noOfChilds = $this->countChilds($conf);

		if($noOfChilds == 0) {
			$params = array(
				'damcat' => $this->conf['catId'],
				'no_cache' => 1
			);

			$pid = $this->conf['backPage'];
			$target = '_self';

			$result = $this->pi_linkToPage(
				$this->conf['catTitle'],
				$pid,
				$target,
				$params
			);
		} else {
			$result = '<span class="notlinked">' . $this->conf['catTitle'] . '</span>';
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi2/class.tx_jhedamextender_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi2/class.tx_jhedamextender_pi2.php']);
}
?>