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
                    #if ($this->countChilds($this->conf) == 0 && $this->getNumberofFilesPerCategory($this->conf) != 0) {
                    if ($this->getNumberofFilesPerCategory($this->conf) != 0) {
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

        //select all tx_dam records for the given category and special usage
        //put together where clause
        $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;

        $where = ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0';
        $where .= ' AND tx_dam_mm_cat.uid_foreign = ' . $this->conf['selectedCategory'];
        //$where .= ' AND tx_dam.tx_jhedamextender_usage LIKE \'%' . $this->conf['specialUsage'] . '%\'';
        $where .= ' AND tx_dam.tx_jhedamextender_usage  = ' . $this->conf['specialUsage'];

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
        
        $content .= $this->makelist($res);

	$this->internal['results_at_a_time']=t3lib_div::intInRange($lConf['results_at_a_time'],0,1000,50);		// Number of results to show in a listing.
	$this->internal['maxPages']=t3lib_div::intInRange($lConf['maxPages'],0,1000,2);;		// The maximum number of "pages" in the browse-box: "Page 1", "Page 2", etc.

        $whereCount = ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0';
	$whereCount .= ' AND tx_dam_mm_cat.uid_foreign = ' . $this->conf['selectedCategory'];
	$whereCount .= ' AND tx_dam.tx_jhedamextender_usage LIKE \'%' . $this->conf['specialUsage'] . '%\'';
	$where .= ' OR (tx_dam.tx_jhedamextender_usage NOT LIKE \'%' . $this->conf['specialUsage'] .'%\' AND tx_dam.file_path LIKE \''. $this->conf['folderSpecialUsage'] .'%\'))';

	//Count all results
	$resCount = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
            'COUNT(\'tx_dam.*\')',
            'tx_dam',
            'tx_dam_mm_cat',
            'tx_dam_cat',
            $where
	);
	list($this->internal['res_count']) = $GLOBALS['TYPO3_DB']->sql_fetch_row($resCount);
        
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
	function makelist($res)	{
		$util = new util();

		// Make list table rows
		$items=array();
		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$path = $this->internal['currentRow']['tx_jhedamextender_path'];
			$lowlevel_selection = $this->internal['currentRow']['tx_jhedamextender_lowlevel_selection'];

			if($lowlevel_selection){
				if($path){
					$arrFolderPath[] = $path . ' :: ' . $lowlevel_selection;
				} else {
					$arrFolderPath[] = $lowlevel_selection;
				}
			} else {
				if($path){
					$arrFolderPath[] = $path;
				} else {
					$arrFolderPath[] = '';
				}
			}
//t3lib_div::debug($arrFolderPath);
			$items[]=$this->makeListItem($arrFolderPath);
		}

		//Generate Header for each section
		$out .= '<table width="100%" border="0" cellspacing="2" cellpadding="2">
			<tr>
				<!--<th class="listrowNo" scope="col">' . $util->translate('nummer') . '</th>-->
				<th class="listrowTitle" scope="col" colspan="4">' . $util->translate('dokument') . '</th>
				<!--<th class="listrowImage" scope="col">' . $util->translate('vorschau') . '</th>-->
			</tr>
			' . implode(chr(10), $items) . '
		</table>
		';

		return $out;
	}

	/**
	 * Implodes a single row from a database to a single line
	 *
	 * @return	Imploded		column values
	 */
	function makeListItem($arrFolderPath)	{
		$util = new util();
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

			$original = array(
				'file' => $imgPath,
			);
			$originalImg = $this->cObj->IMAGE($original);
			$originalPicData = explode(' ', $originalImg);
			$originalPic = substr($originalPicData[1], 5, -1);

			$preview['file'] = $imgPath;
			$preview['altText'] = $this->getFieldContent('title');
			$preview['titleText'] = $this->getFieldContent('title');
			$preview['imageLinkWrap'] = 1;
			$preview['imageLinkWrap.']['enable'] = 1;
			$preview['file.']['width'] = $imgWidthCalc;

			if(t3lib_extMgm::isLoaded('pmkshadowbox')) {  // use Lightbox
				$preview['imageLinkWrap.']['typolink.']['title']      = $this->getFieldContent('title');
				$preview['imageLinkWrap.']['typolink.']['parameter']  = $originalPic;
				$preview['imageLinkWrap.']['typolink.']['ATagParams'] = ' rel="shadowbox" target="_blank"';
			} else { // use simple 'on Click enlarge' mechanism
				$preview['imageLinkWrap.']['title'] = $this->getFieldContent('title');
				$preview['imageLinkWrap.']['bodyTag'] = '<body>';
				$preview['imageLinkWrap.']['wrap'] ='<a href="javascript:close();"> | </a>';
				$preview['imageLinkWrap.']['JSwindow'] = 1;
				if ($preview['imageLinkWrap.']['JSwindow.']['expand'] == '') {
					$preview['imageLinkWrap.']['JSwindow.']['expand'] = '5,5';
				}
				$preview['imageLinkWrap.']['JSwindow.']['newWindow'] = 1;
			}

			$previewImg = $this->cObj->IMAGE($preview);
		} else {
			$preview = array(
				'file' => 'typo3conf/ext/jhe_dam_extender/res/img/dummy250x250.gif',
				'file.' => array(
					'width' => '50'
				),
				'altText' => $util->translate('nothumbavailable')
			);
			$previewImg = $this->cObj->IMAGE($preview);
		}

		$typeIcon = array(
			'file' => 'typo3conf/ext/jhe_dam_extender/res/img/icons/' . $this->getFieldContent('file_type') . '.gif'
		);

		$downloadIcon = array(
			'file' => 'typo3conf/ext/jhe_dam_extender/res/img/downloadNew.gif',
			'altText' => $util->translate('startdownload')
		);

		//get creation date
		if($this->getFieldContent('date_cr')+$util->daysToSeconds($this->extconf['newPeriod']) > time()) {
			//get new icon
			$newIcon = array(
				'file' => 'fileadmin/templates/img/new2.png',
				'altText' => '' . $util->translate('isnew') . ''
			);
		} else {
			$newIcon = '';
		}

		$countedValues = array_count_values($arrFolderPath);

		$path = $this->getFieldContent('tx_jhedamextender_path');
		$lowlevel_selection = $this->getFieldContent('tx_jhedamextender_lowlevel_selection');

		if($lowlevel_selection){
			if($path){
				$folderPath = $path . ' :: ' . $lowlevel_selection;
			} else {
				$folderPath = $lowlevel_selection;
			}
		} else {
			if($path){
				$folderPath = $path;
			} else {
				$folderPath = '';
			}
		}

		if($countedValues['' . $folderPath . ''] == 1){
			$folder = '
				<tr>
					<td class="listrowPath" colspan="6"><strong>' . $folderPath . '</strong></td>
				</tr>';
		}

		$content .= '
			' . $folder . '
				<tr class="tr_upper">
					<td class="listrowTitle" colspan="3"><strong>' . $this->getFieldContent('title') . '</strong><br /><small style="color: #f55b0a;">' . $this->mapFileTypeFromPath($this->getFieldContent('file_path')) . '</small></td>
					<td class="listrowNew">' .$this->cObj->IMAGE($newIcon) . '</td>
				</tr>
				<tr class="tr_lower">
					<td class="listrowType">' . $this->cObj->IMAGE($typeIcon) . '</td>
					<td class="listrowDate">' . date('d.m.Y', $this->getFieldContent('date_mod')) . '</td>
					<td class="listrowSize">' . $this->getFieldContent('file_size') . ' Byte</td>
					<td class="listrowLink"><a href="' . $this->getFieldContent('file_path') . $this->getFieldContent('file_name') . '" title="' . $this->getFieldContent('title') . '" target="_blank">' . $this->cObj->IMAGE($downloadIcon) . '</a></td>
				</tr>
			';

		return $content;
	}

    function mapFileTypeFromPath($path) {

        $mapping = array(

            'fileadmin/Mediendatenbank/Broschueren/' => 'Broschüre',
            'fileadmin/Mediendatenbank/Praesentationen/' => 'Präsentation',
            'fileadmin/Mediendatenbank/Links/' => 'Link',
            'fileadmin/Mediendatenbank/Zentrale_Aktionen/' => 'nur in Zentrale Aktion',
            'fileadmin/Mediendatenbank/Produktblaetter/' => 'Produktblatt',
            'fileadmin/Mediendatenbank/Produktinformationen/' => 'Produktinformation',
            'fileadmin/Mediendatenbank/Flyer/' => 'Flyer',
            'fileadmin/Mediendatenbank/Produktmappe/' => 'nur in Produktmappe',
            'fileadmin/Mediendatenbank/Formblaetter/' => 'Formblatt',
            'fileadmin/Mediendatenbank/Plakate/' => 'Plakat',

        );


        return $mapping[$path];
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
	   $util = new util();

	   $this->addJqueryLibrary();
	   
        $GLOBALS['TSFE']->additionalHeaderData[$this->extKey] = '
            
            <script type="text/javascript">
                $(document).ready(function() {
                    // AJAX Request per eID
                    $(".dlButton").bind("click", function() {
					$(".dlButton").hide();
                    $("#dl_ajaxloader").show();
                    $.ajax({
                        url: "?eID=downloadSpecialUsage",
			data:
                            "mediaFolder=' . $this->conf['mediaFolder'] .
                            '&selectCategory=' . $this->conf['selectedCategory'] .
                            '&specialUsage=' . $this->conf['specialUsage'] . '",
			success: function(result) {
							$(".dlButton").show();
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
     $btTitle = $btTitle['0'] . ' ' . $util->translate('lbl_dl_button');

	$content = '
            <div class="dlButton">
                ' . $btTitle . '
            </div>
            <div id="dl_ajaxloader" class="hidden" style="text-align: center; margin: 5px 5px 10px 5px;">
                <img src="typo3conf/ext/jhe_dam_extender/res/img/ajaxloader.gif" />
            </div>
            <div><small>Mit dem Download-Button können Sie sich die komplette Produktmappe als zip-Datei herunterladen.<br />Dabei werden automatisch alle aktuellen Dokumente zusammengeführt.</small></div>
        ';

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
    * Selects the category name to be displayed
    *
    * @param	array		$conf: The PlugIn configuration
    * @return	string		$result: Name of the category
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
    * Counts childs of a given category
    *
    * @param	array		$conf: The PlugIn configuration
    * @return	string		$result: number of child categories
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
    * Counts the number of files for a given category independent form the folder structure
    *
    * @param	array		$conf: The PlugIn configuration
    * @return	string		$result: Number of files
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

    function collectDocumentPaths($array, $path){
#t3lib_div::debug($path);

        $array[] = $path;

        return $array;

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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi1/class.tx_jhedamextender_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi1/class.tx_jhedamextender_pi1.php']);
}

?>