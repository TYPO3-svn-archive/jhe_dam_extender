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

class ajax_downloadSpecialUsage {

	/**
	 * Main Methode
	 *
	 * @return	string
	 */
	public function main() {
		tslib_eidtools::connectDB();
		$feUserObject = tslib_eidtools::initFeUser();

		$mediaFolder = t3lib_div::_GET('mediaFolder');
		$selectCategory = t3lib_div::_GET('selectCategory');
		$specialUsage = t3lib_div::_GET('specialUsage');
		$folderSpecialUsage = t3lib_div::_GET('folderSpecialUsage');

		list($this->internal['orderBy'],$this->internal['descFlag']) = explode(':',$this->piVars['sort']);
		$this->internal['results_at_a_time']=t3lib_div::intInRange($lConf['results_at_a_time'],0,1000,50);		// Number of results to show in a listing.
		$this->internal['maxPages']=t3lib_div::intInRange($lConf['maxPages'],0,1000,2);;		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
		$this->internal['searchFieldList']='title';
		$this->internal['groupBy'] = '';
		$this->internal['orderBy'] = 'title';
		$this->internal['orderByList']='title';
		$this->internal['currentTable'] = 'tx_dam';

		$this->internal['where'] = ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0';
		$this->internal['where'] .= ' AND tx_dam_mm_cat.uid_foreign = ' . $selectCategory;
		$this->internal['where'] .= ' AND ((tx_dam.tx_jhedamextender_usage = ' . $specialUsage . ')';
		$this->internal['where'] .= ' OR (tx_dam.tx_jhedamextender_usage != ' . $specialUsage .' AND tx_dam.file_path LIKE \''. $folderSpecialUsage .'%\'))';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'COUNT(\'tx_dam.*\')',
			$this->internal['currentTable'],
			'tx_dam_mm_cat',
			'tx_dam_cat',
			$this->internal['where']
		);
		list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		$this->internal['where'] = ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0';
		$this->internal['where'] .= ' AND tx_dam_mm_cat.uid_foreign = ' . $selectCategory;
		$this->internal['where'] .= ' AND ((tx_dam.tx_jhedamextender_usage = ' . $specialUsage . ')';
		$this->internal['where'] .= ' OR (tx_dam.tx_jhedamextender_usage != ' . $specialUsage .' AND tx_dam.file_path LIKE \''. $folderSpecialUsage .'%\'))';

		//Count results per directory
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'tx_dam_cat.title as catTitle, tx_dam.*',
			$this->internal['currentTable'],
			'tx_dam_mm_cat',
			'tx_dam_cat',
			$this->internal['where']
		);
		$category = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);

		// Make listing query, pass query to SQL database:
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'tx_dam_cat.title as catTitle, tx_dam.*',
			$this->internal['currentTable'],
			'tx_dam_mm_cat',
			'tx_dam_cat',
			$this->internal['where'],
			''.
			$this->internal['orderBy']
		);

		//Getting the list for every directory which is not empty
		$items=array();
		while($currentRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$items[]= $currentRow;
		}

		$zip = new ZipArchive();
		$filename = 'Produktmappe' . '_' . $category['catTitle'] . '_' . date('d-m-Y_Hi',time()). '.zip';
		$path = $folderSpecialUsage . $filename;

		if ($zip->open($path, ZIPARCHIVE::CREATE)!==TRUE) {
   			exit("cannot open <$path>\n");
		}

		foreach($items as $file){
			$fileFolder = substr($file['file_path'], strlen('fileadmin/Mediendatenbank/'));
			$compare = strncmp($fileFolder, 'Produktmappe/', strlen('Produktmappe/'));
			if($compare == '-1'){
				$fileFolder = $fileFolder;
			} else if($compare == '0' && $fileFolder == 'Produktmappe/') {
				$fileFolder = substr($fileFolder, strlen('Produktmappe/'));
			} else if ($compare == '0' && substr($fileFolder, strlen('Produktmappe/')) != FALSE) {
				$fileFolder = substr($fileFolder, strlen('Produktmappe/'));
			}
			$zip->addFile($file['file_path'] . $file['file_name'], $fileFolder . $file['file_name']);
		}

		$countFiles = $zip->numFiles;
		$zip->close();

		return $countFiles . '|' . $path . '|' . $filename;
	}

}

$output = t3lib_div::makeInstance('ajax_downloadSpecialUsage');
echo $output->main();
?>