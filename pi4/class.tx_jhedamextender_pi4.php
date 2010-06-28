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
 *   53: class tx_jhedamextender_pi4 extends tslib_pibase
 *   66:     function main($content, $conf)
 *  104:     public function getFolderNamesFromFilesystem($folder)
 *
 *              SECTION: [Describe function...]
 *  217:     function getSpecialUsageItemDirs($conf)
 *  247:     function getNumberOfFilesPerDirectory($conf)
 *  286:     function createNavPerDocumenttypes($conf)
 *  332:     function translateDirectoryType($type)
 *
 * TOTAL FUNCTIONS: 6
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');

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
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		//get GET variables
		$this->conf['selectedCategory'] = t3lib_div::_GET('damcat');

		//get data from be flexform
		$this->pi_initPIFlexForm();
		$this->conf['mediaFolder'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'mediaFolder');

		//Initializing params from ext_conf_template
		$this->extconf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
		$this->conf['mainFolder'] =$this->extconf['mainFolder'];

		//integration of an main css file for styling the html output
		$css = '<link rel="stylesheet" type="text/css" href="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/css/main.css" />';
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey . '_css'] = $css;
		#var_dump($GLOBALS['TSFE']->additionalHeaderData[$this->extKey]);

		//get language support
		$GLOBALS['TSFE']->readLLfile('EXT:jhe_dam_extender/pi4/locallang.xml');
		
		//clear html output
		$output = '';

		//creating menue from dam directories
		$output .= $this->createNavPerDocumenttypes($this->conf);

		//creating output target for jquery result
		$output .= '<div id="docsByType"></div>';

		return $output;
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
	 * @param	object		$conf: configuration data
	 * @return	array		$result: array of all directories with documents for special usage only
	 */
	function getSpecialUsageItemDirs($conf) {

		$this->conf = $conf;

		$dirs = $this->getFolderNamesFromFilesystem($this->conf['mainFolder']);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_jhedamextender_usage',
			'tx_jhedamextender_usage.deleted = 0 AND tx_jhedamextender_usage.hidden = 0 AND tx_jhedamextender_usage.pid = ' . $this->conf['mediaFolder']
		);

		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			foreach($dirs as $folder){
				$compare = strncmp($folder, $row['usage_ea619ffddc'], strlen($row['usage_ea619ffddc']));
				if($compare == '0'){
					$result[] = $folder;
				}
			}
		}

		return $result;
	}

	/**
	 * Counts all files in a given directory
	 *
	 * @param	object		$conf: configuration data
	 * @return	array		$array: with counting results per document type ad directory
	 */
	function getNumberOfFilesPerDirectory($conf) {

		foreach($this->conf['directories'] as $dir){
			$files = t3lib_div::getFilesInDir(
				$this->conf['mainFolder'].$dir,
				'',
				0,
				1
			);

			$array[$dir] = 0;

			foreach($files as $file){
				$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
					'tx_dam_cat.uid as catId, tx_dam.*',
					'tx_dam',
					'tx_dam_mm_cat',
					'tx_dam_cat',
					' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0 AND tx_dam.file_name =\'' . $file . '\''
				);
				$resultMM = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				if($resultMM['catId'] == $this->conf['selectedCategory']) {
					$array[$dir] = $array[$dir]+1;
				}
			}
		}

		return $array;
	}

	/**
	 * Creates an list of all documents types in which data for a given category is stored
	 *
	 * @param	object		$conf: configuration data
	 * @return	string		html output
	 */
	function createNavPerDocumenttypes($conf){
		$this->conf = $conf;

		$dirsFromFileSystem = $this->getFolderNamesFromFilesystem($this->conf['mainFolder']);
		$specialUsageItemDirs = $this->getSpecialUsageItemDirs($this->conf);
		$this->conf['directories'] = array_diff($dirsFromFileSystem, $specialUsageItemDirs);

		$noOfFiles = $this->getNumberOfFilesPerDirectory($this->conf);

		foreach($this->conf['directories'] as $type){
			if($noOfFiles[$type]) {
				$docType .= '<li'. $this->pi_classParam('navDokType'). ' id="' . strtolower($type) . '">' . $this->translate($type) . '</li>';
			}
		}

		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = '
			<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
			<script type="text/javascript">

				$(document).ready(function() {
					// AJAX Request per eID
					$(".tx-jhedamextender-pi4-navDokType").bind("click", function() {
						$.ajax({
					    	url: "?eID=getDocumentsByDirectoryAndCategory",
					    	data: "&docType=" + this.id + "&catId=' . $this->conf['selectedCategory'] . '",
					    	success: function(result) {
					    		$("#docsByType").html("" + result + "")
							}
						});
					});

				});

			</script>
		';

		return '<h3>' . $this->translate('headerdoctypes') . '</h3><div><ul>'. $docType . '</ul></div>';
	}

	/**
	 * Translates labels and output
	 *
	 * @param	string		$type: string to be translated
	 * @return	string		translated string
	 */
	function translate($type){
		return $this->pi_getLL('' . strtolower($type) . '');
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi4/class.tx_jhedamextender_pi4.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi4/class.tx_jhedamextender_pi4.php']);
}

?>