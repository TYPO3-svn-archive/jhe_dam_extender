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
 * Plugin 'DAM file list' for the 'jhe_dam_extender' extension.
 *
 * @author	Jari-Hermann Ernst <jari-hermann.ernst@bad-gmbh.de>
 * @package	TYPO3
 * @subpackage	tx_jhedamextender
 */
class tx_jhedamextender_pi4 extends tslib_pibase {
	var $prefixId      = 'tx_jhedamextender_pi4';		// Same as class name
	var $scriptRelPath = 'pi4/class.tx_jhedamextender_pi4.php';	// Path to this script relative to the extension dir.
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
		$util = new util();

		//integration of an main css file for styling the html output
		$css = '<link rel="stylesheet" type="text/css" href="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/css/main.css?' . time() .'" />';
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey . '_css'] = $css;

		//getting all configration data from plugin flexform
		$this->pi_initPIFlexForm();
		$this->conf['mediaFolder'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'mediaFolder');
		$this->conf['selectedCategory'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'catSelector');
		$this->conf['specialCaseSelector'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'specialCaseSelector');
		
		//Initializing params from ext_conf_template
		$this->extconf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
		$this->conf['mainFolder'] =$this->extconf['mainFolder'];

		//initialize content output
		$content = '';

		if(!$this->conf['selectedCategory'] && !$this->conf['specialCaseSelector']){
			$content .= $util->translate('err_no_category_no_special_usage');
		} else {
			//creating menue from dam directories
			$content .= $this->createNavPerDocumenttypes($this->conf);
			
			//creating output target for ajaxloader and jquery result
			$content .= '<div id="doctype_ajaxloader" class="hidden" style="text-align: center; margin: 5px;"><img src="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/img/ajaxloader.gif" /></div>';
			$content .= '<div id="docsByType"></div>';
		}

		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * Retrieves directory names from the filesystem
	 *
	 * @param	string		$folder: Folder of the filesystem given the function
	 * @return	array		$arrayFolders: array with all existing folders
	 */
	public function getFolderNamesFromFilesystem($folder){
		$arrFolders = t3lib_div::get_dirs($folder);

		foreach ($arrFolders as $value) {
			if(t3lib_div::get_dirs($folder.$value . '/') != NULL){
				$newFolders = t3lib_div::get_dirs($folder.$value . '/');
				$folders='';
				foreach ($newFolders as $singleFolder){
					$singleFolder = $value .'/' . $singleFolder;
					$folders[] = $singleFolder;
				}
				$arrFolders = array_merge($arrFolders, $folders);
			}
		}
		sort($arrFolders);

		return $arrFolders;
	}

	/**
	 * Retrieves all directories with documents for special usage only
	 *
	 * @param	array		$conf: configuration data
	 * @return	array		$result: array of all directories with documents for special usage only
	 */
	public function getSpecialUsageItemDirs($conf) {
		$this->conf = $conf;

		$dirs = $this->getFolderNamesFromFilesystem($this->conf['mainFolder']);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_jhedamextender_usage',
			'tx_jhedamextender_usage.deleted = 0 AND tx_jhedamextender_usage.hidden = 0 AND tx_jhedamextender_usage.pid = ' . $this->conf['mediaFolder']
		);

		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			foreach($dirs as $folder){
				$specialFolderName = str_replace(' ', '_', $row['usage_ea619ffddc']);
				$compare = strncmp($folder, $specialFolderName, strlen($specialFolderName));
				if($compare == '0'){
					$result[] = $folder;
				}
			}
		}

		return $result;
	}

	public function getDbDataForFile($file){
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'tx_dam_cat.uid as catId, tx_dam.*',
			'tx_dam',
			'tx_dam_mm_cat',
			'tx_dam_cat',
			' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0 AND tx_dam.file_name LIKE \'' . $file . '\''
		);
		$result = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		$dbDataForFile = $result['uid'] . ',' . $result['catId'] . ',' . $result['tx_jhedamextender_usage'];

		return $dbDataForFile;
	}
	
	/**
	 * Counts all files in a given directory for a given category or special usage case
	 *
	 * @param	string		$dir: name of directory
	 * @return	string		$cat: id of category
	 */
	public function getNumberOfFilesPerDirectoryAndSelection($dir, $cat, $specialUsage) {
		$noOfFiles = 0;
		$result = '';
		$existingFiles = FALSE;

		$files = t3lib_div::getFilesInDir(
			$this->conf['mainFolder'].$dir,
			'',
			0,
			1
		);

		if(count($files)) {
			foreach($files as $file){
				$filesDbDataArr[] = $this->getDbDataForFile($file);
			}

			foreach($filesDbDataArr as $fileDbData){
				$fileRecordDataArr = explode(',', $fileDbData);
				$fileRecordId = $fileRecordDataArr[0];
				$fileRecordCat = $fileRecordDataArr[1];
				$fileRecordSpecialUsage = $fileRecordDataArr[2];
				
				if($fileRecordId && $fileRecordCat === $cat){
					$affectedFileRecordCat[] = $fileRecordId;
				}
				if($fileRecordId && $fileRecordSpecialUsage === $specialUsage){
					$affectedFileRecordSpecialUsage[] = $fileRecordId;
				}
			}
		}
		
		if($cat && !$specialUsage){
			$counter = count($affectedFileRecordCat);
			if($counter > 0){
				$existingFiles = TRUE;
			}
		}
		if($specialUsage && !$cat){
			$counter = count($affectedFileRecordSpecialUsage);
			if($counter > 0){
				$existingFiles = TRUE;
			}
		}

		return $existingFiles;
	}

	/**
	 * Checks if an correct styles txt-file exists in the link folder
	 *
	 * @return	boolean		$countLinkFiles: file(s) exists or not
	 */
	public function checkForTxtFilesInLinkFolder(){
		$util = new util();
		$linkFolder = $this->extconf['linkFolder'];
		$countLinkFiles = 0;

		$filesInLinkFolder = t3lib_div::getFilesInDir(
			$this->conf['mainFolder'].$linkFolder,
			'',
			0,
			1
		);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_dam_cat.title as catTitle',
			'tx_dam_cat',
			'uid=' . $this->conf['selectedCategory']
		);
		$catTitle = $util->catToString(implode($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)));

		if(count($filesInLinkFolder)) {
			foreach($filesInLinkFolder as $file){
				$fileType = substr($file, -3, 3);
				if($fileType == 'txt'){
					$fileNameBegin = substr($file, 0, 6);
					if($fileNameBegin == 'links_'){
						$fileCatPart = substr($file, 6, -4);
						if($fileCatPart == $catTitle){
							$countLinkFiles = 1;
						}
					}
				}
			}
		}

		return $countLinkFiles;
	}

	/**
	* Creates an list of all documents types in which data for a given category is stored
	*
	* @param	object		$conf: configuration data
	* @return	string		html output
	*/
	public function createNavPerDocumenttypes($conf){
		$this->conf = $conf;
		$linkFolder = $this->extconf['linkFolder'];
		$util = new util();

		$dirsFromFileSystem = $this->getFolderNamesFromFilesystem($this->conf['mainFolder']);
		$specialUsageItemDirs = $this->getSpecialUsageItemDirs($this->conf);
		$this->conf['directories'] = array_diff($dirsFromFileSystem, $specialUsageItemDirs);

		if($this->conf['selectedCategory']){
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'title',
				'tx_dam_cat',
				'uid=' . $this->conf['selectedCategory']
			);
			list($catTitle) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
		} else if($this->conf['specialCaseSelector']){
			//getting title and folder of special usage
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'usage_ea619ffddc',
				'tx_jhedamextender_usage',
				'uid = ' . $this->conf['specialCaseSelector']
			);
			list($catTitle) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
		}

		foreach($this->conf['directories'] as $dir){
			$noOfFiles = $this->getNumberOfFilesPerDirectoryAndSelection($dir,$this->conf['selectedCategory'], $this->conf['specialCaseSelector']);
			$countLinkFiles = $this->checkForTxtFilesInLinkFolder();

			if($dir == $linkFolder){
				if($countLinkFiles == 1){
					$docType .= '<li'. $this->pi_classParam('navDokType'). ' id="' . strtolower($dir) . '">' . $util->translate($dir) . '</li>';
				}
			} else {
				if($noOfFiles > 0){
					$docType .= '<li'. $this->pi_classParam('navDokType'). ' id="' . strtolower($dir) . '">' . $util->translate($dir) . '</li>';
				}
			}
		}

		$this->addJqueryLibrary();
		
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = '
			<script type="text/javascript" src="typo3conf/ext/jhe_dam_extender/res/js/jquery.pajinate.js?' . time() .'"></script>
			<script type="text/javascript">
				$(document).ready(function() {

					var maxitems = 25;

					//AJAX Request per eID
					$(".tx-jhedamextender-pi4-navDokType").bind("click", function() {
						$("#docsByType").hide();
						$("#doctype_ajaxloader").show();
						$.ajax({
							url: "?eID=getDocumentsByDirectoryAndCategory",
							data: "&docType=" + this.id + "&specialUsage=' . $this->conf['specialCaseSelector'] . '&catId=' . $this->conf['selectedCategory'] . '",
							dataType: "json",
							success: function(result) {
								$("#doctype_ajaxloader").hide();
								$("#docsByType").show();
								$("#docsByType").html("" + result.content + "");

								if(maxitems < result.counter){
									$("#docsByType").append("<div class=\"page_navigation\"></div>");

									$("#docsByType").pajinate({
										items_per_page : maxitems,
										nav_panel_id: \'.page_navigation\',
										nav_label_first: \'Anfang\',
										nav_label_last: \'Ende\',
										nav_label_prev: \'<<\',
										nav_label_next: \'>>\',
										show_first_last: true,
										num_page_links_to_display: \'7\'
									});
								}
							}
						});
					});
				});
			</script>
		';

		if(!$docType) {
			$content = '<h3>' . $util->translate('headerdoctypes') . ' ' . $catTitle . '</h3><div>' . $util->translate('err_no_doctypes'). '</div>';
		} else {
			$content = '<h3>' . $util->translate('headerdoctypes') . ' ' . $catTitle . '</h3><div><ul>'. $docType . '</ul></div>';
		}

		return $content;
	}
	
	public function addJqueryLibrary(){
		// checks if t3jquery is loaded
		if (t3lib_extMgm::isLoaded('t3jquery')) {
			require_once(t3lib_extMgm::extPath('t3jquery').'class.tx_t3jquery.php');
		}
		// if t3jquery is loaded and the custom Library had been created
		if (T3JQUERY === true) {
			tx_t3jquery::addJqJS();
		} else {
			//if none of the previous is true, you need to include your own library
			//just as an example in this way
			$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .= '<script language="JavaScript" src="' . t3lib_extMgm::extRelPath($this->extKey) . 'res/js/jquery-1.9.1.min.js"></script>';
		}
	}
	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi4/class.tx_jhedamextender_pi4.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi4/class.tx_jhedamextender_pi4.php']);
}
?>