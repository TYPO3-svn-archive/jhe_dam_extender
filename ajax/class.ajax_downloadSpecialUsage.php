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
if (!defined ('PATH_typo3conf')) die ('Could not access this script directly!');

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_tslib.'class.tslib_eidtools.php');
require_once(PATH_t3lib.'class.t3lib_div.php');
require_once(t3lib_extMgm::extPath("jhe_dam_extender") . 'util/util.php');

class ajax_downloadSpecialUsage extends tslib_pibase {

	var $extKey = 'jhe_dam_extender';
		 
	/**
	 * Main Methode
	 *
	 * @return string
	 */
	public function main() {
		$util = new util();
		tslib_eidtools::connectDB();
		$feUserObject = tslib_eidtools::initFeUser();

		//retrieving GET data
		$this->conf['mediaFolder'] = t3lib_div::_GET('mediaFolder');
		$this->conf['selectedCategory'] = t3lib_div::_GET('selectCategory');
		$this->conf['specialUsage'] = t3lib_div::_GET('specialUsage');

		//getting title and folder of special usage
		$su = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'usage_ea619ffddc',
			'tx_jhedamextender_usage',
			'uid = ' . $this->conf['specialUsage'] );
		list($suTitle) = $GLOBALS['TYPO3_DB']->sql_fetch_row($su);

		//getting mainFolder path from ext_conf_template
		$extconf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
		$this->conf['mainFolder'] = $extconf['mainFolder'];

		$orderBy = 'tx_dam.tx_jhedamextender_order';
		
		//check if the list should be prepared for a given category or a special usage
		if($this->conf['selectedCategory'] && $this->conf['specialUsage']){
			//we have a category case

			//putting together where clause for retrieving the related files
			$where = ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0';
			$where .= ' AND tx_dam_mm_cat.uid_foreign = ' . $this->conf['selectedCategory'];
			$where .= ' AND tx_dam.tx_jhedamextender_usage LIKE \'%' . $this->conf['specialUsage'] . '%\'';

			$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
				'tx_dam.*',
				'tx_dam',
				'tx_dam_mm_cat',
				'tx_dam_cat',
				$where,
				'',
				$orderBy
			);

			//prepare filename for zip file
			$filename = strtolower(str_replace(' ', '_', $suTitle)) . '_' . strtolower(str_replace(' ', '_', $this->getCategoryTitle($this->conf))) . '_' . date('d-m-Y_Hi', time()). '.zip';
		} else if (!$this->conf['selectedCategory'] && $this->conf['specialUsage']){
			//we have a special usage case
			
			$where = ' tx_dam.deleted = 0 AND tx_dam.hidden = 0';
			$where .= ' AND tx_dam.tx_jhedamextender_usage  = ' . $this->conf['specialUsage'];

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'tx_dam.*',
				'tx_dam',
				$where,
				'',
				$orderBy
			);

			//prepare filename for zip file
			$filename = strtolower(str_replace(' ', '_', $suTitle)) . '_' . date('d-m-Y_Hi', time()). '.zip';
		}

		//Getting the list for every directory which is not empty
		$items = array();
		while ($currentRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$items[] = $currentRow;
		}

		$path = $this->createZipFile($items, $filename);

		return $path;
	}

	public function getCategoryTitle($conf) {
		$this->conf = $conf;

		$where = 'deleted = 0 AND hidden = 0 AND pid = ' . $this->conf['mediaFolder'] . ' AND uid = ' . $this->conf['selectedCategory'] . '';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'title',
			'tx_dam_cat',
			$where
		);
		list($result) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		return $result;
	}

	public function createZipFile($items, $filename){
		$util = new util();

		//creating zip file for data
		$zip = new ZipArchive();
		$path = 'typo3temp/temp/' . $filename;
		if ($zip->open($path, ZIPARCHIVE::CREATE) !== TRUE) {
			exit("cannot open <$path>\n");
		}

		foreach($items as $file) {
			$pathSelection = $file['tx_jhedamextender_path'];
			$lowlevel_selection = $file['tx_jhedamextender_lowlevel_selection'];

			if($lowlevel_selection && $pathSelection){
				$specialPath = str_replace('_::_', '/', $util->replaceUmlauts(str_replace(' ', '_', $pathSelection))) . '/' . $util->replaceUmlauts(str_replace(' ', '_', $lowlevel_selection)) .'/';
			} elseif ($lowlevel_selection && !$pathSelection) {
				$specialPath = $util->replaceUmlauts(str_replace(' ', '_', $lowlevel_selection)) . '/';
			} elseif (!$lowlevel_selection && $pathSelection) {
				$specialPath = str_replace('_::_', '/', $util->replaceUmlauts(str_replace(' ', '_', $pathSelection))) .'/';
			} else{
				$specialPath = '/';
			}

			$zip->addFile($file['file_path'] . $file['file_name'], $specialPath . $file['tx_jhedamextender_order'] . '_' . $file['file_name']);
			$specialPath = '';
		}

		$zip->close();
		
		return $path;
	}
}
	 
$output = t3lib_div::makeInstance('ajax_downloadSpecialUsage');
echo $output->main();
?>