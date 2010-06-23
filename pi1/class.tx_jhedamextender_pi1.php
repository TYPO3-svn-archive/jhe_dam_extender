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
 *   56: class tx_jhedamextender_pi1 extends tslib_pibase
 *   69:     function main($content, $conf)
 *   99:     function listView($content, $conf)
 *  201:     function makelist($res, $folder)
 *  238:     function makeListItem()
 *  389:     function dlButtonView($content, $conf)
 *  453:     function getFieldContent($fN)
 *  470:     function getFieldHeader($fN)
 *  485:     function getFieldHeader_sortLink($fN)
 *  495:     public function getFolderNamesFromFilesystem($folder)
 *  521:     public function getCategoryHeader($catId, $mediaFolder)
 *
 * TOTAL FUNCTIONS: 10
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
	function main($content, $conf)	{

		$this->pi_initPIFlexForm();
		$viewMode = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'viewMode');

		switch($viewMode)	{
			case 'dlButton':
				
				$selectedCategory = t3lib_div::_GET('damcat');
				$this->pi_initPIFlexForm();
				$mediaFolder = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'mediaFolder');
				$specialUsage = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'specialUsage');
				$folderSpecialUsage = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'folderSpecialUsage');
				
				
				$noOfChilds = $this->countChilds($selectedCategory, $mediaFolder);
								
				list($t) = explode(':',$this->cObj->currentRecord);
				$this->internal['currentTable']=$t;
				$this->internal['currentRow']=$this->cObj->data;
				
				$noOfFiles = $this->getNumberofFilesPerCategory($selectedCategory, $mediaFolder, $specialUsage, $folderSpecialUsage);
				
				if ($noOfChilds == 0 && $noOfFiles != 0) {
					return $this->pi_wrapInBaseClass($this->dlButtonView($content, $conf));
				}
				
			break;
			case 'list':
			default:
				if (strstr($this->cObj->currentRecord,'tt_content'))	{
					$conf['pidList'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'mediaFolder');
					$conf['recursive'] = $this->cObj->data['recursive'];
				}
				return $this->pi_wrapInBaseClass($this->listView($content, $conf));
			break;
		}
	}

	/**
	 * Shows a list of database entries
	 *
	 * @param	string		$content: content of the PlugIn
	 * @param	array		$conf: PlugIn Configuration
	 * @return	HTML		list of table entries
	 */
	function listView($content, $conf) {
		$this->conf = $conf;		// Setting the TypoScript passed to this function in $this->conf
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();		// Loading the LOCAL_LANG values

		//Getting data from flexform
		$this->pi_initPIFlexForm();
		$this->conf['mediaFolder'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'mediaFolder');
		#$this->conf['selectCategory'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'selectCategory');
		$this->conf['specialUsage'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'specialUsage');
		$this->conf['folderSpecialUsage'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'folderSpecialUsage');
		$this->conf['viewMode'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'viewMode');

		$this->conf['selectCategory'] = t3lib_div::_GET('damcat');

		$lConf = $this->conf['listView.'];	// Local settings for the listView function

		$fullTable = '';	// Clear var;



		$fullTable .= '<h3>Kategorie: ' . $this->getCategoryHeader($this->conf['selectCategory'], $this->conf['mediaFolder']) . '</h3>';

		//Initializing global query params
		$this->extconf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
		$mainFolder =$this->extconf['mainFolder'];
		$dirsFromFileSystem = $this->getFolderNamesFromFilesystem($mainFolder);

		list($this->internal['orderBy'],$this->internal['descFlag']) = explode(':',$this->piVars['sort']);
		$this->internal['results_at_a_time']=t3lib_div::intInRange($lConf['results_at_a_time'],0,1000,50);		// Number of results to show in a listing.
		$this->internal['maxPages']=t3lib_div::intInRange($lConf['maxPages'],0,1000,2);;		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
		$this->internal['searchFieldList']='title';
		$this->internal['groupBy'] = 'file_path';
		$this->internal['orderBy'] = '';
		$this->internal['orderByList']='title';
		$this->internal['currentTable'] = 'tx_dam';

		$this->internal['where'] = ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0';
		$this->internal['where'] .= ' AND tx_dam_mm_cat.uid_foreign = ' . $this->conf['selectCategory'];
		$this->internal['where'] .= ' AND ((tx_dam.tx_jhedamextender_usage = ' . $this->conf['specialUsage'] . ')';
		$this->internal['where'] .= ' OR (tx_dam.tx_jhedamextender_usage != ' . $this->conf['specialUsage'] .' AND tx_dam.file_path LIKE \''. $this->conf['folderSpecialUsage'] .'%\'))';

		//Count all results
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'COUNT(\'tx_dam.*\')',
			$this->internal['currentTable'],
			'tx_dam_mm_cat',
			'tx_dam_cat',
			$this->internal['where']
		);
		list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		//SQL-Query based on existing dirs in filesystem
		foreach ($dirsFromFileSystem as $dir){
			$filePath = $mainFolder . $dir . '/';
			$this->internal['where'] = '';
			$this->internal['where'] = ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0';
			$this->internal['where'] .= ' AND tx_dam_mm_cat.uid_foreign = ' . $this->conf['selectCategory'] . ' AND tx_dam.file_path = \'' . $filePath . '\'';
			$this->internal['where'] .= ' AND ((tx_dam.tx_jhedamextender_usage = ' . $this->conf['specialUsage'] . ')';
			$this->internal['where'] .= ' OR (tx_dam.tx_jhedamextender_usage != ' . $this->conf['specialUsage'] .' AND tx_dam.file_path LIKE \''. $this->conf['folderSpecialUsage'] .'%\'))';

			//Count results per directory
			$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
				'COUNT(\'tx_dam.*\')',
				$this->internal['currentTable'],
				'tx_dam_mm_cat',
				'tx_dam_cat',
				$this->internal['where']
			);
			list($this->internal['res_count_dir']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

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
			if($this->internal['res_count_dir'] != 0) {
				$fullTable.=$this->makelist($res, $dir);
			}
		}

		// Adds the result browser:
		$fullTable.=$this->pi_list_browseresults();

		// Returns the content from the plugin.
		return $fullTable;
	}

	/**
	 * Creates a list from a database query
	 *
	 * @param	ressource		$res: A database result ressource
	 * @param	[type]		$folder: String with section identifier
	 * @return	A		HTML list if result items
	 */
	function makelist($res, $folder)	{
		$items=array();
			// Make list table rows
		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$items[]=$this->makeListItem();
		}

		//Generate Header for each section
		$out .= '<h4>' . $folder . '</h4>
			<div' . $this->pi_classParam('listrow') . '>' .
				'<div' . $this->pi_classParam('listrowTitle') . '>Bezeichnung</div>' .
				'<div' . $this->pi_classParam('listrowSize') . '>Gr&ouml;&szlig;e</div>' .
				'<div' . $this->pi_classParam('listrowDate') . '>Datum</div>' .
				'<div' . $this->pi_classParam('listrowImage') . '>Vorschau</div>' .
				#'<div' . $this->pi_classParam('listrowUsage') . '>Verwendung</div>' .
				'<div' . $this->pi_classParam('listrowCat') . '>Kategorie</div>' .
				'<div' . $this->pi_classParam('listrowType') . '>Typ</div>' .
				'<div' . $this->pi_classParam('listrowLink') . '>Download-Link</div>' .
			'</div>
			<hr />
			<div'.$this->pi_classParam('list').'>
			'.implode(chr(10),$items).'
			</div>';

		//CSS styling @todo: Put together in TS or a single css file
		$GLOBALS['TSFE']->additionalCSS[] = '.' . $this->pi_getClassName('list') . ' {
				padding: 3px 5px;
			}';

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

			$targetImgWidth = '50';

			switch($scape) {
				case 'square':
					$imgWidthCalc = $targetImgWidth;
					break;
				case 'landscape':
					$imgWidthCalc = $targetImgWidth;
					break;
				case 'portrait':
					$imgWidthCalc = intval($targetImgWidth * ($imgWidth / $imgHeight));
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
				'altText' => 'Keine Vorschau m&ouml;glich!'
			);
		}

		$typeIcon = array(
			'file' => 'typo3conf/ext/jhe_dam_extender/pi1/gfx/icons/' . $this->getFieldContent('file_type') . '.gif'
		);

		$downloadIcon = array(
			'file' => 'typo3conf/ext/jhe_dam_extender/pi1/gfx/download.gif',
			'altText' => 'Download starten...'
		);

		$folder = substr($this->getFieldContent('file_path'),26, -1);

		$out .= '
			<div' . $this->pi_classParam('listrow') . '>' .
				'<div' . $this->pi_classParam('listrowTitle') . '>' . $this->getFieldContent('title') . '</div>' .
				'<div' . $this->pi_classParam('listrowSize') . '>' . $this->getFieldContent('file_size') . ' Byte</div>' .
				'<div' . $this->pi_classParam('listrowDate') . '>' . date('d.m.Y', $this->getFieldContent('crdate')) . '</div>' .
				'<div' . $this->pi_classParam('listrowImage') . '>' . $this->cObj->IMAGE($preview) . '</div>' .
				#'<div' . $this->pi_classParam('listrowUsage') . '>' . $this->getFieldContent('tx_jhedamextender_usage') . '</div>' .
				'<div' . $this->pi_classParam('listrowCat') . '>' . $this->getFieldContent('catTitle') . '</div>' .
				'<div' . $this->pi_classParam('listrowType') . '>' . $this->cObj->IMAGE($typeIcon) . '</div>' .
				'<div' . $this->pi_classParam('listrowLink') . '><a href="' . $this->getFieldContent('file_path') . $this->getFieldContent('file_name') . '" title="' . $this->getFieldContent('title') . '" target="_blank">' . $this->cObj->IMAGE($downloadIcon) . ' Download</a></div>' .
			'</div>
			<hr />
			';

		$GLOBALS['TSFE']->additionalCSS[] = '.' . $this->pi_getClassName('listrow') . ' {
				width: 100%;
				height: auto;
				clear: both;
				padding: 3px 5px;
			}';

		$GLOBALS['TSFE']->additionalCSS[] = '.' . $this->pi_getClassName('listrowTitle') . ' {
				width: 250px;
				margin-right: 5px;
				float: left;
			}';

		$GLOBALS['TSFE']->additionalCSS[] = '.' . $this->pi_getClassName('listrowSize') . ' {
				width: 100px;
				margin-right: 5px;
				float: left;
			}';

		$GLOBALS['TSFE']->additionalCSS[] = '.' . $this->pi_getClassName('listrowDate') . ' {
				width: 100px;
				margin-right: 5px;
				float: left;
			}';

		$GLOBALS['TSFE']->additionalCSS[] = '.' . $this->pi_getClassName('listrowImage') . ' {
				width: 80px;
				margin-right: 5px;
				float: left;
				text-align: center;
			}';

		$GLOBALS['TSFE']->additionalCSS[] = '.' . $this->pi_getClassName('listrowImage') . ' img {
				border: 1px solid gray;
				margin: 0 0 5px 0;
			}';

		$GLOBALS['TSFE']->additionalCSS[] = '.' . $this->pi_getClassName('listrowUsage') . ' {
				width: 50px;
				margin-right: 5px;
				float: left;
			}';

		$GLOBALS['TSFE']->additionalCSS[] = '.' . $this->pi_getClassName('listrowCat') . ' {
				width: 80px;
				margin-right: 5px;
				float: left;
			}';

		$GLOBALS['TSFE']->additionalCSS[] = '.' . $this->pi_getClassName('listrowType') . ' {
				width: 50px;
				margin-right: 5px;
				float: left;
			}';

		$GLOBALS['TSFE']->additionalCSS[] = 'hr {
				margin: 0 0 5px 0;
				clear: both;
			}';

		$GLOBALS['TSFE']->additionalCSS[] = '.' . $this->pi_getClassName('listrowLink') . ' {
				width: 100px;
				float: left;
			}';

		return $out;
	}
	/**
	 * Display a single item from the database
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	HTML		of a single database entry
	 */
	function dlButtonView($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();

		$this->pi_initPIFlexForm();
		$this->conf['mediaFolder'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'mediaFolder');
		#$this->conf['selectCategory'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'selectCategory');
		$this->conf['selectCategory'] = t3lib_div::_GET('damcat');
		$this->conf['specialUsage'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'specialUsage');
		$this->conf['folderSpecialUsage'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'folderSpecialUsage');
		$this->conf['viewMode'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'viewMode');

		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = '
			<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
			<script type="text/javascript">

				$(document).ready(function() {
					// AJAX Request per eID
					$("#dlButton").bind("click", function() {
						$.ajax({
					    	url: "?eID=downloadSpecialUsage",
					    	data:
					    		"mediaFolder=' . $this->conf['mediaFolder'] .
					    		'&selectCategory=' . $this->conf['selectCategory'] .
					    		'&specialUsage=' . $this->conf['specialUsage'] .
					    		'&folderSpecialUsage=' . $this->conf['folderSpecialUsage'] . '",
					    	success: function(result) {
					    		result = result.split("|");
					    		$("#dlButtonResult").html("Es wurden " + result[0] + " Dateien zusammengefuehrt. Bitte nutzen Sie den folgenden Link zum herunterladen der Produktmappe:<br /><a href=\"" + result[1] + "\">" + result[2] + "</a>");
							}
						});
					});

				});

			</script>
		';



		$content = '<div'.$this->pi_classParam('dlButtonView').'>
						<input type="button" name="dlButton" id="dlButton" value="Download">
					</div>
					<div id="dlButtonResult"></div>';

		$GLOBALS['TSFE']->additionalCSS[] = '.' . $this->pi_getClassName('dlButtonView') . ' {
				padding: 3px 5px;
				text-align: center;
			}';

		$GLOBALS['TSFE']->additionalCSS[] = '.' . $this->pi_getClassName('dlButtonView') . ' input {
				height: 100px;
				width: 120px
			}';

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
	 * Returns the label for a fieldname from local language array
	 *
	 * @param	[type]		$fN: ...
	 * @return	[type]		...
	 */
	function getFieldHeader($fN)	{
		switch($fN) {

			default:
				return $this->pi_getLL('listFieldHeader_'.$fN,'['.$fN.']');
			break;
		}
	}

	/**
	 * Returns a sorting link for a column header
	 *
	 * @param	string		$fN: Fieldname
	 * @return	The		fieldlabel wrapped in link that contains sorting vars
	 */
	function getFieldHeader_sortLink($fN)	{
		return $this->pi_linkTP_keepPIvars($this->getFieldHeader($fN),array('sort'=>$fN.':'.($this->internal['descFlag']?0:1)));
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
	 * [Describe function...]
	 *
	 * @param	[type]		$catId: ...
	 * @param	[type]		$mediaFolder: ...
	 * @return	[type]		...
	 */
	public function getCategoryHeader($catId, $mediaFolder) {

		$this->internal['currentTable'] = 'tx_dam_cat';
		$this->internal['where'] = 'deleted = 0 AND hidden = 0 AND pid = ' . $mediaFolder . ' AND uid = ' . $catId . '';

		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'title',
			$this->internal['currentTable'],
			$this->internal['where']
		);
		list($result) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		return $result;
	}
	
	function countChilds($catId, $mediaFolder) {

		$this->internal['currentTable'] = 'tx_dam_cat';
		$this->internal['where'] = 'tx_dam_cat.deleted = 0 AND tx_dam_cat.hidden = 0 AND tx_dam_cat.pid = ' . $mediaFolder . ' AND tx_dam_cat.parent_id = ' . $catId . '';

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'COUNT(tx_dam_cat.title)',
			$this->internal['currentTable'],
			$this->internal['where']
		);

		list($result) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);

		return $result;
	}
	
	function getNumberofFilesPerCategory($catId, $mediaFolder, $specialUsage, $folderSpecialUsage) {
		
		list($this->internal['orderBy'],$this->internal['descFlag']) = explode(':',$this->piVars['sort']);
		$this->internal['results_at_a_time']=t3lib_div::intInRange($lConf['results_at_a_time'],0,1000,50);		// Number of results to show in a listing.
		$this->internal['maxPages']=t3lib_div::intInRange($lConf['maxPages'],0,1000,2);;		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.
		$this->internal['searchFieldList']='title';
		$this->internal['groupBy'] = 'file_path';
		$this->internal['orderBy'] = '';
		$this->internal['orderByList']='title';
		$this->internal['currentTable'] = 'tx_dam';

		$this->internal['where'] = ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0 AND tx_dam.pid = ' . $mediaFolder . '';
		$this->internal['where'] .= ' AND tx_dam_mm_cat.uid_foreign = ' . $catId;
		$this->internal['where'] .= ' AND ((tx_dam.tx_jhedamextender_usage = ' . $specialUsage . ')';
		$this->internal['where'] .= ' OR (tx_dam.tx_jhedamextender_usage != ' . $specialUsage .' AND tx_dam.file_path LIKE \''. $folderSpecialUsage .'%\'))';

		//Count all results
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'COUNT(\'tx_dam.*\')',
			$this->internal['currentTable'],
			'tx_dam_mm_cat',
			'tx_dam_cat',
			$this->internal['where']
		);
		list($result) = $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
		
		return $result;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi1/class.tx_jhedamextender_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi1/class.tx_jhedamextender_pi1.php']);
}

?>