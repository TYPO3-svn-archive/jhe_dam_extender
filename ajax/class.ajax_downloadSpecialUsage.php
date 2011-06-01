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

        $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;

        //retrieving GET data
        $mediaFolder = t3lib_div::_GET('mediaFolder');
        $selectCategory = t3lib_div::_GET('selectCategory');
        $specialUsage = t3lib_div::_GET('specialUsage');

        //getting title and folder of special usage
        $su = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'usage_ea619ffddc',
            'tx_jhedamextender_usage',
            'uid = ' . $specialUsage );
        $suTitle = array_values($GLOBALS['TYPO3_DB']->sql_fetch_assoc($su));
        $suTitle = $suTitle['0'];

	//getting mainFolder path from ext_conf_template
	$extconf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
	$mainFolder = $extconf['mainFolder'];

	//creating path to special usage folder
	$folderSpecialUsage = $mainFolder.$suTitle;

        //putting together where clause for getting category title and generating file list items
	$where = ''; //reset variable
	$where = ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0';
	$where .= ' AND tx_dam_mm_cat.uid_foreign = ' . $selectCategory;
	$where .= ' AND ((tx_dam.tx_jhedamextender_usage LIKE \'%' . $specialUsage . '%\')';
	//$where .= ' OR (tx_dam.tx_jhedamextender_usage NOT LIKE \'%' . $specialUsage .'%\' AND tx_dam.file_path LIKE \''. $folderSpecialUsage .'%\'))';

	//Getting category title
	$resCat = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
            'tx_dam_cat.title as catTitle, tx_dam.*',
            'tx_dam',
            'tx_dam_mm_cat',
            'tx_dam_cat',
            $where );
	$category = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($resCat);
	$catTitle = $util->replaceUmlauts($category['catTitle']);

        //putting together where clause for retrieving the related files
	$where = ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0';
	$where .= ' AND tx_dam_mm_cat.uid_foreign = ' . $selectCategory;
	$where .= ' AND tx_dam.tx_jhedamextender_usage LIKE \'%' . $specialUsage . '%\'';

        $orderBy = 'tx_dam.tx_jhedamextender_order';

	$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
            'tx_dam.*',
            'tx_dam',
            'tx_dam_mm_cat',
            'tx_dam_cat',
            $where,
            '',
            $orderBy
        );
	 
	//Getting the list for every directory which is not empty
	$items = array();
	while ($currentRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $items[] = $currentRow;
	}

	//creating zip file for data
        $zip = new ZipArchive();
	$filename = strtolower(str_replace(' ', '_', $suTitle)) . '_' . strtolower(str_replace(' ', '_', $catTitle)) . '_' . date('d-m-Y_Hi', time()). '.zip';
	$path = 'typo3temp/temp/' . $filename;

	if ($zip->open($path, ZIPARCHIVE::CREATE) !== TRUE) {
            exit("cannot open <$path>\n");
	}
			 
	foreach($items as $file) {

            if($file['tx_jhedamextender_path']){
                $specialPath = $file['tx_jhedamextender_path'] . '/';
            }
            $zip->addFile($file['file_path'] . $file['file_name'], $specialPath . $file['tx_jhedamextender_order'] . '_' . $file['file_name']);
            $specialPath = '';
	}

	$countFiles = $zip->numFiles;
	$zip->close();
			 
	return $path;
        }
    }
	 
    $output = t3lib_div::makeInstance('ajax_downloadSpecialUsage');
    echo $output->main();
?>
