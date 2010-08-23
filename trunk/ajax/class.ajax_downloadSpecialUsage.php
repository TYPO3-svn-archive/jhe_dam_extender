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
if (!defined ('PATH_typo3conf')) die ('Could not access this script directly!');

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_tslib.'class.tslib_eidtools.php');
require_once(PATH_t3lib.'class.t3lib_div.php');

class ajax_downloadSpecialUsage extends tslib_pibase {

	var $extKey        = 'jhe_dam_extender';

	/**
	 * Main Methode
	 *
	 * @return	string
	 */
	public function main() {
		tslib_eidtools::connectDB();
		$feUserObject = tslib_eidtools::initFeUser();

		//retrieving GET data
		$mediaFolder = t3lib_div::_GET('mediaFolder');
		$selectCategory = t3lib_div::_GET('selectCategory');
		$specialUsage = t3lib_div::_GET('specialUsage');

		//getting title and folder of special usage
		$su = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'usage_ea619ffddc',
			'tx_jhedamextender_usage',
			'uid = ' . $specialUsage
		);
		$suTitle = array_values($GLOBALS['TYPO3_DB']->sql_fetch_assoc($su));
		$suTitle = $suTitle['0'];

		//getting mainFolder path from ext_conf_template
		$extconf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
		$mainFolder =$extconf['mainFolder'];

		//creating path to special usage folder
		$folderSpecialUsage = $mainFolder.$suTitle;

		//putting together where clause for counting related files
		$where = ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0';
		$where .= ' AND tx_dam_mm_cat.uid_foreign = ' . $selectCategory;
		$where .= ' AND ((tx_dam.tx_jhedamextender_usage LIKE \*%' . $specialUsage . '%\')';
		$where .= ' OR (tx_dam.tx_jhedamextender_usage NOT LIKE \'%' . $specialUsage .'%\' AND tx_dam.file_path LIKE \''. $folderSpecialUsage .'%\'))';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'COUNT(\'tx_dam.*\')',
			'tx_dam',
			'tx_dam_mm_cat',
			'tx_dam_cat',
			$where
		);
		list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		//putting together where clause for getting category title and generating file list items
		$where = ''; //reset variable
		$where = ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0';
		$where .= ' AND tx_dam_mm_cat.uid_foreign = ' . $selectCategory;
		$where .= ' AND ((tx_dam.tx_jhedamextender_usage LIKE \'%' . $specialUsage . '%\')';
		$where .= ' OR (tx_dam.tx_jhedamextender_usage NOT LIKE \'%' . $specialUsage .'%\' AND tx_dam.file_path LIKE \''. $folderSpecialUsage .'%\'))';

		//Getting category title
		$resCat = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'tx_dam_cat.title as catTitle, tx_dam.*',
			'tx_dam',
			'tx_dam_mm_cat',
			'tx_dam_cat',
			$where
		);
		$category = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCat);
		$catTitle = $this->replaceUmlauts($category['catTitle']);

		// Make listing query, pass query to SQL database:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'tx_dam_cat.title as catTitle, tx_dam.*',
			'tx_dam',
			'tx_dam_mm_cat',
			'tx_dam_cat',
			$where,
			'',
			'title'
		);

		//Getting the list for every directory which is not empty
		$items=array();
		while($currentRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$items[]= $currentRow;
		}

		//creating zip file for data
		$zip = new ZipArchive();
		$filename = strtolower($suTitle) . '_' . strtolower($catTitle) . '_' . date('d-m-Y_Hi',time()). '.zip';
		$path = 'typo3temp/temp/' . $filename;

		if ($zip->open($path, ZIPARCHIVE::CREATE)!==TRUE) {
   			exit("cannot open <$path>\n");
		}

		foreach($items as $file){
			$fileFolder = substr($file['file_path'], strlen($mainFolder));
			$compare = strncmp($fileFolder, $suTitle.'/', strlen($suTitle.'/'));

			if($compare == '-1'){
				$fileFolder = $fileFolder;
			} else if($compare == '0' && $fileFolder == $suTitle.'/') {
				$fileFolder = substr($fileFolder, strlen($suTitle.'/'));
			} else if ($compare == '0' && substr($fileFolder, strlen($suTitle.'/')) != FALSE) {
				$fileFolder = substr($fileFolder, strlen($suTitle.'/'));
			}

			$zip->addFile($file['file_path'] . $file['file_name'], $fileFolder . $file['file_name']);
		}

		$countFiles = $zip->numFiles;
		$zip->close();

		return $path;
	}

	/**
	 * Replaces german umlauts in filenames
	 *
	 * @param	sting		$string: filename string from somewhere
	 * @return	string		$string: new better readable filename
	 */
	public function replaceUmlauts($string) {
		$input = array("ä", "ö", "ü", "Ä", "Ö", "Ü", "ß");
		$output = array("ae", "oe", "ue", "Ae", "Oe", "Ue", "ss");

		return str_replace($input, $output, $string);

	}
}

$output = t3lib_div::makeInstance('ajax_downloadSpecialUsage');
echo $output->main();
?>