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
    function main($content, $conf) {
        $this->conf = $conf;
	$this->pi_setPiVarDefaults();
	$this->pi_loadLL();

        //get GET variables
	$this->conf['selectedCategory'] = t3lib_div::_GET('damcat');

	if(!$this->conf['selectedCategory']){
            $content = $this->pi_getLL('err_no_cat');
	} else {
            //get data from be flexform
            $this->pi_initPIFlexForm();
            $this->conf['mediaFolder'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'mediaFolder');

            //Initializing params from ext_conf_template
            $this->extconf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
            $this->conf['mainFolder'] =$this->extconf['mainFolder'];

            //integration of an main css file for styling the html output
            $css = '<link rel="stylesheet" type="text/css" href="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/css/main.css" />';
            $GLOBALS['TSFE']->additionalHeaderData[$this->extKey . '_css'] = $css;

            //clear html output
            $content = '';

            //creating menue from dam directories
            $content .= $this->createNavPerDocumenttypes($this->conf);

            //creating output target for ajaxloader and jquery result
            $content .= '<div id="doctype_ajaxloader" class="hidden" style="text-align: center; margin: 5px;"><img src="../res/img/ajaxloader.gif" /></div>';
            $content .= '<div id="docsByType"></div>';
	}

        return $content;
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
    * Counts all files in a given directory for a given category
    *
    * @param	string		$dir: name of directory
    * @return	string		$cat: id of category
    */
    function getNumberOfFilesPerDirectoryAndCategory($dir, $cat) {
        $noOfFiles = 0;
	$result = '';

        $files = t3lib_div::getFilesInDir(
            $this->conf['mainFolder'].$dir,
            '',
            0,
            1
        );

        if(count($files)) {
            foreach($files as $file){
                $res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
                    'tx_dam_cat.uid as catId, tx_dam.*',
                    'tx_dam',
                    'tx_dam_mm_cat',
                    'tx_dam_cat',
                    ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0 AND tx_dam.file_name LIKE \'' . $file . '\''
                );
                $resultMM = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                $uid[] = $resultMM['uid'];
            }

            foreach($uid as $record) {
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                    'uid_foreign',
                    'tx_dam_mm_cat',
                    'uid_local=' . $record
                );

                while($resultRecord = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)) {
                    $result .= $resultRecord['0'] . '|';
		};
            }
            $result = substr($result, 0, -1);
            $arrayRes[$record] = explode('|', $result);
        }

        if(in_array($cat, $arrayRes[$record])) {
            $noOfFiles = '1';
        }

        return $noOfFiles;
    }

    /**
    * Checks if an correct styles txt-file exists in the link folder
    *
    * @return	boolean		$countLinkFiles: file(s) exists or not
    */
    function checkForTxtFilesInLinkFolder(){
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
    function createNavPerDocumenttypes($conf){
        $this->conf = $conf;
        $linkFolder = $this->extconf['linkFolder'];
        $util = new util();

        $dirsFromFileSystem = $this->getFolderNamesFromFilesystem($this->conf['mainFolder']);
	$specialUsageItemDirs = $this->getSpecialUsageItemDirs($this->conf);
	$this->conf['directories'] = array_diff($dirsFromFileSystem, $specialUsageItemDirs);

        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'title',
            'tx_dam_cat',
            'uid=' . $this->conf['selectedCategory']
	);
	list($catTitle) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

        foreach($this->conf['directories'] as $dir){
            $noOfFiles = $this->getNumberOfFilesPerDirectoryAndCategory($dir,$this->conf['selectedCategory']);
            $countLinkFiles = $this->checkForTxtFilesInLinkFolder();
                    
            if($noOfFiles) {
                if($dir == $linkFolder && $countLinkFiles == 0){
                    $docType .= '';
                } else {
                    $docType .= '<li'. $this->pi_classParam('navDokType'). ' id="' . strtolower($dir) . '">' . $util->translate($dir) . '</li>';
                }
            }
        }

	$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = '
            <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
            <script type="text/javascript">
                $(document).ready(function() {
                    // AJAX Request per eID
                    $(".tx-jhedamextender-pi4-navDokType").bind("click", function() {
                        $("#docsByType").hide();
			$("#doctype_ajaxloader").show();
			$.ajax({
                            url: "?eID=getDocumentsByDirectoryAndCategory",
                            data: "&docType=" + this.id + "&catId=' . $this->conf['selectedCategory'] . '",
                            success: function(result) {
                                $("#doctype_ajaxloader").hide();
				$("#docsByType").show();
				$("#docsByType").html("" + result + "")
                            }
			});
                    });
                });
            </script>
	';

	if(!$docType) {
            $content = $catTitle . ': ' . $util->translate('err_no_doctypes');
	} else {
            $content = '<h3>' . $util->translate('headerdoctypes') . ' ' . $catTitle . '</h3><div><ul>'. $docType . '</ul></div>';
	}

	return $content;
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi4/class.tx_jhedamextender_pi4.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi4/class.tx_jhedamextender_pi4.php']);
}
?>