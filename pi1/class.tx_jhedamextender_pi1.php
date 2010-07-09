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
 *   57: class tx_jhedamextender_pi1 extends tslib_pibase
 *   70:     public function main($content, $conf)
 *  124:     function listView($conf)
 *  204:     function makelist($res, $folder)
 *  243:     function makeListItem()
 *  327:     function dlButtonView($conf)
 *  379:     function getFieldContent($fN)
 *  397:     public function getFolderNamesFromFilesystem($conf)
 *  426:     public function getCategoryHeader($conf)
 *  448:     function countChilds($conf)
 *  473:     function getNumberofFilesPerCategory($conf)
 *  500:     function translate($type)
 *
 * TOTAL FUNCTIONS: 11
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_tslib.'class.tslib_pibase.php');


/**
 * Plugin 'DAM special usage' for the 'jhe_dam_extender' extension.
 *
 * @author	Jari-Hermann Ernst <jari-hermann.ernst@bad-gmbh.de>
 * @package	TYPO3
 * @subpackage	tx_jhedamextender
 */
class tx_jhedamextender_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_jhedamextender_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_jhedamextender_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'jhe_dam_extender';	// The extension key.
	var $pi_checkCHash = true;

	/**
	 * Main method of your PlugIn
	 *
	 * @param	string		$content: The content of the PlugIn
	 * @param	array		$conf: The PlugIn Configuration
	 * @return	The		content that should be displayed on the website
	 */
	public function main($content, $conf)	{
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		//retrieving GET data
		$this->conf['selectedCategory'] = t3lib_div::_GET('damcat');
		$this->conf['specialUsage'] = t3lib_div::_GET('specialUsage');

		//retrieving data from be flexform
		$this->pi_initPIFlexForm();
		$this->conf['viewMode'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'viewMode');
		$this->conf['mediaFolder'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'mediaFolder');

		//getting title and folder of special usage
		$su = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'usage_ea619ffddc',
			'tx_jhedamextender_usage',
			'uid = ' . $this->conf['specialUsage']
		);
		$suTitle = array_values($GLOBALS['TYPO3_DB']->sql_fetch_assoc($su));
		$this->conf['suTitle'] = $suTitle['0'];

		//getting mainFolder path from ext_conf_template
		$extconf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
		$this->conf['mainFolder'] =$extconf['mainFolder'];

		//creating path to special usage folder
		$this->conf['folderSpecialUsage'] = $this->conf['mainFolder'].$this->conf['suTitle'];

		if(!$this->conf['selectedCategory'] || !$this->conf['specialUsage']) {
			return $this->pi_getLL('err_no_cat');
		} else {
			switch($this->conf['viewMode'])	{
				case 'dlButton':
					if ($this->countChilds($this->conf) == 0 && $this->getNumberofFilesPerCategory($this->conf) != 0) {
						return $this->pi_wrapInBaseClass($this->dlButtonView($this->conf));
					}
				break;
				case 'list':
				default:
					return $this->pi_wrapInBaseClass($this->listView($this->conf));
				break;
			}
		}
	}

	/**
	 * Shows a list of database entries
	 *
	 * @param	string		$content: content of the PlugIn
	 * @param	array		$conf: PlugIn Configuration
	 * @return	HTML		list of table entries
	 */
	function listView($conf) {
		$this->conf = $conf;		// Setting the TypoScript passed to this function in $this->conf
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();		// Loading the LOCAL_LANG values

		//integration of an main css file for styling the html output
		$css = '<link rel="stylesheet" type="text/css" href="' . t3lib_extMgm::siteRelPath($this->extKey) . 'res/css/main.css" />';
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey . '_css'] = $css;

		$content = '';	// Clear var;
		$content .= '<h3>' .$this->conf['suTitle']. ' ' . $this->getCategoryHeader($this->conf) . '</h3>';

		$dirsFromFileSystem = $this->getFolderNamesFromFilesystem($this->conf);

		$this->internal['results_at_a_time']=t3lib_div::intInRange($lConf['results_at_a_time'],0,1000,50);		// Number of results to show in a listing.
		$this->internal['maxPages']=t3lib_div::intInRange($lConf['maxPages'],0,1000,2);;		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.

		$where = ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0';
		$where .= ' AND tx_dam_mm_cat.uid_foreign = ' . $this->conf['selectedCategory'];
		$where .= ' AND ((tx_dam.tx_jhedamextender_usage LIKE \'%' . $this->conf['specialUsage'] . '%\')';
		$where .= ' OR (tx_dam.tx_jhedamextender_usage NOT LIKE \'%' . $this->conf['specialUsage'] .'%\' AND tx_dam.file_path LIKE \''. $this->conf['folderSpecialUsage'] .'%\'))';

		//Count all results
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'COUNT(\'tx_dam.*\')',
			'tx_dam',
			'tx_dam_mm_cat',
			'tx_dam_cat',
			$where
		);
		list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		//SQL-Query based on existing dirs in filesystem
		foreach ($dirsFromFileSystem as $dir){
			$filePath = $this->conf['mainFolder'] . $dir . '/';
			$where = '';
			$where = ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0';
			$where .= ' AND tx_dam_mm_cat.uid_foreign = ' . $this->conf['selectedCategory'] . ' AND tx_dam.file_path = \'' . $filePath . '\'';
			$where .= ' AND ((tx_dam.tx_jhedamextender_usage LIKE \'%' . $this->conf['specialUsage'] . '%\')';
			$where .= ' OR (tx_dam.tx_jhedamextender_usage NOT LIKE \'%' . $this->conf['specialUsage'] .'%\' AND tx_dam.file_path LIKE \''. $this->conf['folderSpecialUsage'] .'%\'))';

			//Count results per directory
			$resDir = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
				'COUNT(\'tx_dam.*\')',
				'tx_dam',
				'tx_dam_mm_cat',
				'tx_dam_cat',
				$where
			);
			list($this->internal['res_count_dir']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($resDir);

			// Make listing query, pass query to SQL database:
			$resList = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
				'tx_dam_cat.title as catTitle, tx_dam.*',
				'tx_dam',
				'tx_dam_mm_cat',
				'tx_dam_cat',
				$where
			);

			//Getting the list for every directory which is not empty
			if($this->internal['res_count_dir'] != 0) {
				$content.=$this->makelist($resList, $dir);
			}
		}

		// Adds the result browser:
		$content.=$this->pi_list_browseresults();

		// Returns the content from the plugin.
		return $content;
	}

	/**
	 * Creates a list from a database query
	 *
	 * @param	ressource		$res: A database result ressource
	 * @param	[type]		$folder: String with section identifier
	 * @return	A		HTML list if result items
	 */
	function makelist($res, $folder)	{
		// Make list table rows
		$items=array();
		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$items[]=$this->makeListItem();
		}

		if(substr_count($folder, '/') > 0){
			$arrFolder = explode('/', $folder);
			foreach($arrFolder as $val) {
				$newFolder .= $this->translate($val) . ' ';
			}
		} else {
			$newFolder = $this->translate($folder);
		}

		//Generate Header for each section
		$out .= '<h4>' . $newFolder . '</h4>
			<div' . $this->pi_classParam('listrow') . '>' .
				'<div' . $this->pi_classParam('listrowTitle') . '>' . $this->translate('bezeichnung') . '</div>' .
				'<div' . $this->pi_classParam('listrowSize') . '>' . $this->translate('groesse') . '</div>' .
				'<div' . $this->pi_classParam('listrowDate') . '>' . $this->translate('datum') . '</div>' .
				'<div' . $this->pi_classParam('listrowImage') . '>' . $this->translate('vorschau') . '</div>' .
				'<div' . $this->pi_classParam('listrowType') . '>' . $this->translate('typ') . '</div>' .
				'<div' . $this->pi_classParam('listrowLink') . '>' . $this->translate('downloadlink') . '</div>' .
			'</div>
			<hr />
			<div'.$this->pi_classParam('list').'>
			'.implode(chr(10),$items).'
			</div>';

		return $out;
	}

	/**
	 * Implodes a single row from a database to a single line
	 *
	 * @return	Imploded		column values
	 */
	function makeListItem()	{

		$this->extconf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
		$workingTypes = explode(',',$this->extconf['graphicFileTypes']);

		if(in_array($this->getFieldContent('file_type'), $workingTypes)) {
			$imgPath = $this->getFieldContent('file_path') . $this->getFieldContent('file_name');

			$imgData = getimagesize($imgPath);
			$imgWidth = $imgData[0];
			$imgHeight = $imgData[1];

			$scape = '';
			if($imgWidth == $imgHeight){
				$scape = 'square';
			} else if ($imgWidth > $imgHeight) {
				$scape = 'landscape';
			} else if ($imgWidth < $imgHeight) {
				$scape = 'portrait';
			}

			$thumbImgWidth = $this->extconf['thumbImageWidth'];

			switch($scape) {
				case 'square':
					$imgWidthCalc = $thumbImgWidth;
					break;
				case 'landscape':
					$imgWidthCalc = $thumbImgWidth;
					break;
				case 'portrait':
					$imgWidthCalc = intval($thumbImgWidth * ($imgWidth / $imgHeight));
					break;
			}

			$preview = array(
				'file' => $imgPath,
				'file.' => array(
					'width' => $imgWidthCalc
				),
				'altText' => $this->getFieldContent('title')
			);
		} else {
			$preview = array(
				'file' => 'typo3conf/ext/jhe_dam_extender/pi1/gfx/dummy250x250.gif',
				'file.' => array(
					'width' => '50'
				),
				'altText' => $this->translate('nothumbavailable')
			);
		}

		$typeIcon = array(
			'file' => 'typo3conf/ext/jhe_dam_extender/pi1/gfx/icons/' . $this->getFieldContent('file_type') . '.gif'
		);

		$downloadIcon = array(
			'file' => 'typo3conf/ext/jhe_dam_extender/pi1/gfx/download.gif',
			'altText' => $this->translate('startdownload')
		);

		
		//get creation date
		if($this->getFieldContent('date_cr')+$this->daysToSeconds($this->extconf['newPeriod']) > time()) {
			//get new icon
			$newIcon = array(
				'file' => 'typo3conf/ext/jhe_dam_extender/pi1/gfx/new.gif',
				'altText' => '' . $this->translate('isnew') . ''
			);
		} else {
			$newIcon = '';
		}
		
		$folder = substr($this->getFieldContent('file_path'),26, -1);

		$content .= '
			<div' . $this->pi_classParam('listrow') . '>' .
				'<div' . $this->pi_classParam('listrowTitle') . '>' . $this->getFieldContent('title') . ' ' . $this->cObj->IMAGE($newIcon) . '</div>' .
				'<div' . $this->pi_classParam('listrowSize') . '>' . $this->getFieldContent('file_size') . ' Byte</div>' .
				'<div' . $this->pi_classParam('listrowDate') . '>' . date('d.m.Y', $this->getFieldContent('crdate')) . '</div>' .
				'<div' . $this->pi_classParam('listrowImage') . '>' . $this->cObj->IMAGE($preview) . '</div>' .
				'<div' . $this->pi_classParam('listrowType') . '>' . $this->cObj->IMAGE($typeIcon) . '</div>' .
				'<div' . $this->pi_classParam('listrowLink') . '><a href="' . $this->getFieldContent('file_path') . $this->getFieldContent('file_name') . '" title="' . $this->getFieldContent('title') . '" target="_blank">' . $this->cObj->IMAGE($downloadIcon) . ' Download</a></div>' .
			'</div>
			<hr />
			';

		return $content;
	}
	/**
	 * Display a single item from the database
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	HTML		of a single database entry
	 */
	function dlButtonView($conf) {
		$this->conf = $conf;

		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = '
			<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
			<script type="text/javascript">

				$(document).ready(function() {
					// AJAX Request per eID
					$("#dlButton").bind("click", function() {
						$("#dl_ajaxloader").show();
						$.ajax({
					    	url: "?eID=downloadSpecialUsage",
					    	data:
					    		"mediaFolder=' . $this->conf['mediaFolder'] .
					    		'&selectCategory=' . $this->conf['selectedCategory'] .
					    		'&specialUsage=' . $this->conf['specialUsage'] . '",
					    	success: function(result) {
					    		$("#dl_ajaxloader").hide();
					    		$("<form action=\'"+result+"\' method=\'post\'></form>").appendTo("body").submit().remove();
							}
						});
					});

				});

			</script>
		';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'usage_ea619ffddc',
			'tx_jhedamextender_usage',
			'uid = ' . $this->conf['specialUsage']
		);
		$btTitle = array_values($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res));
		$btTitle = $btTitle['0'] . ' ' . $this->pi_getLL('lbl_dl_button');

		$content = '<div'.$this->pi_classParam('dlButtonView').'>
						<input type="button" name="dlButton" id="dlButton" value="' . $btTitle . '">
					</div>
					<div id="dl_ajaxloader" class="hidden" style="text-align: center; margin: 5px;">
						<img src="typo3conf/ext/jhe_dam_extender/res/img/ajaxloader.gif" />
					</div>';

		return $content;
	}
	/**
	 * Returns the content of a given field
	 *
	 * @param	string		$fN: name of table field
	 * @return	Value		of the field
	 */
	function getFieldContent($fN)	{
		switch($fN) {
			case 'uid':
				return $this->pi_list_linkSingle($this->internal['currentRow'][$fN],$this->internal['currentRow']['uid'],1);	// The "1" means that the display of single items is CACHED! Set to zero to disable caching.
			break;

			default:
				return $this->internal['currentRow'][$fN];
			break;
		}
	}

	/**
	 * Retrieves directory names from the filesystem
	 *
	 * @param	string		$folder: Folder of the filesystem given the function
	 * @return	array		$arrayFolders: array with all existing folders
	 */
	public function getFolderNamesFromFilesystem($conf){
		$this->conf = $conf;

		$arrFolders = t3lib_div::get_dirs($this->conf['mainFolder']);

		foreach ($arrFolders as $value) {

			if(t3lib_div::get_dirs($this->conf['mainFolder'].$value . '/') != NULL){
				$newFolders = t3lib_div::get_dirs($this->conf['mainFolder'].$value . '/');
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
	 * [Describe function...]
	 *
	 * @param	[type]		$catId: ...
	 * @param	[type]		$mediaFolder: ...
	 * @return	[type]		...
	 */
	public function getCategoryHeader($conf) {
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

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$catId: ...
	 * @param	[type]		$mediaFolder: ...
	 * @return	[type]		...
	 */
	function countChilds($conf) {
		$this->conf = $conf;

		$where = 'tx_dam_cat.deleted = 0 AND tx_dam_cat.hidden = 0 AND tx_dam_cat.pid = ' . $this->conf['mediaFolder'] . ' AND tx_dam_cat.parent_id = ' . $this->conf['selectedCategory'];

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(tx_dam_cat.title)',
			'tx_dam_cat',
			$where
		);

		list($result) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		return $result;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$catId: ...
	 * @param	[type]		$mediaFolder: ...
	 * @param	[type]		$specialUsage: ...
	 * @param	[type]		$folderSpecialUsage: ...
	 * @return	[type]		...
	 */
	function getNumberofFilesPerCategory($conf) {
		$this->conf = $conf;

		$where = ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0 AND tx_dam.pid = ' . $this->conf['mediaFolder'] . '';
		$where .= ' AND tx_dam_mm_cat.uid_foreign = ' . $this->conf['selectedCategory'];
		$where .= ' AND ((tx_dam.tx_jhedamextender_usage = ' . $this->conf['specialUsage'] . ')';
		$where .= ' OR (tx_dam.tx_jhedamextender_usage != ' . $this->conf['specialUsage'] .' AND tx_dam.file_path LIKE \''. $this->conf['folderSpecialUsage'] .'%\'))';

		//Count all results
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'COUNT(\'tx_dam.*\')',
			'tx_dam',
			'tx_dam_mm_cat',
			'tx_dam_cat',
			$where
		);
		list($result) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		return $result;
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
	
	function daysToSeconds($days) {
		return 60*60*24*$days;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi1/class.tx_jhedamextender_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi1/class.tx_jhedamextender_pi1.php']);
}

?>