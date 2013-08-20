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
class tx_jhedamextender_pi5 extends tslib_pibase {
    var $prefixId      = 'tx_jhedamextender_pi5';		// Same as class name
    var $scriptRelPath = 'pi5/class.tx_jhedamextender_pi5.php';	// Path to this script relative to the extension dir.
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
		$util = new util();
		
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		//get data from be flexform
		$this->pi_initPIFlexForm();
		$this->conf['selectedDoctype'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'doctypeSelector');
		$this->conf['mediaFolder'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'mediaFolder');
        
		//Initializing params from ext_conf_template
		$this->extconf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
		$this->conf['mainFolder'] =$this->extconf['mainFolder'];

		//integration of a main css file for styling the html output
		$css = '<link rel="stylesheet" type="text/css" href="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/css/main.css?' . time() .'" />';
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .= $css;
		
		$this->addJqueryLibrary();
		
		$js = '
			<script type="text/javascript" src="typo3conf/ext/jhe_dam_extender/res/js/jquery.pajinate.js?' . time() .'"></script>
			<script type="text/javascript">
                $(document).ready(function() {

					var maxitems = 25;
					var listitems = $("li.listitem").length;

					if(maxitems < listitems){
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
				});
			</script>
		';
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] .= $js;
		
		//create sql query to get all documents with the given flexform selection
		$where .= ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0';
		$where .= ' AND tx_dam_mm_cat.uid_local = tx_dam.uid';
		$where .= ' AND tx_dam.tx_jhedamextender_doctype = ' . $this->conf['selectedDoctype'];

		//generate SQL request
		$sql = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'tx_dam_cat.title as catTitle, tx_dam.*',
			'tx_dam',
			'tx_dam_mm_cat',
			'tx_dam_cat',
			$where,
			'',
			'tx_dam.title'
		);
		
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($sql)){
			
			//get icon for document type
			$typeIcon = array(
				'file' => 'typo3conf/ext/jhe_dam_extender/res/img/icons/' . $row['file_type'] . '.gif'
			);
			
			//get download icon
			$downloadIcon = array(
				'file' => 'typo3conf/ext/jhe_dam_extender/res/img/downloadNew.gif',
				'altText' => '' . $util->translate('downloadlink') . '',
				'wrap' => '<strong>|</strong>'
			);
			
			//get creation date
			if($row['date_mod']+$util->daysToSeconds($this->extconf['newPeriod']) > time()) {
				//get new icon
				$newIcon = array(
					'file' => 'fileadmin/templates/img/new2.png',
					'altText' => '' . $util->translate('isnew') . ''
				);
				$newIcon = '<div class="specialTopicAjaxListNewIcon">' . $this->cObj->IMAGE($newIcon) . '</div>';
			} else {
				$newIcon = '<div class="specialTopicAjaxListNewIcon"></div>';
			}
			
			//Convert filesize to kb
			$file_size = round($row['file_size'] / 1024);
			
			$output .= '<li class="listitem">
				' . $newIcon . '
				<div class="specialTopicAjaxListDownload"><a href="' . $row['file_path'] . $row['file_name'] . '" title="' . $row['title'] . '" target="_blank">' . $this->cObj->IMAGE($downloadIcon) . '</a></div>
				<div class="specialTopicAjaxListTitle">' . $row['title'] . '</div>
				<div class="specialTopicAjaxListDetails">Fachthema: <strong>' . $row['catTitle'] . '</strong></div>
				<div class="specialTopicAjaxListDetails">
					<span class="specialTopicAjaxListDetailsType">' . $this->cObj->IMAGE($typeIcon) . '</span>
					<span class="specialTopicAjaxListDetailsDate">' . date('d.m.Y', $row['date_mod']) . '</span>
					<span class="specialTopicAjaxListDetailsSize">' . $file_size . ' ' . $util->translate('kbyte') . '</span>
				</div>
			</li>';

		}
		
		//clear html output
		$content = '';

		//creating output target for ajaxloader and jquery result
		$content .= '<div id="doctype_ajaxloader" class="hidden" style="text-align: center; margin: 5px;"><img src="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/img/ajaxloader.gif" /></div>';
		$content .= '<div id="docsByType"><ul class="content">' . $output . '</ul></div>';

        return $this->pi_wrapInBaseClass($content);
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

	function addJqueryLibrary(){
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi5/class.tx_jhedamextender_pi5.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi5/class.tx_jhedamextender_pi5.php']);
}
?>