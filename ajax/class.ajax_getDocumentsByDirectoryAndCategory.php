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
require_once(PATH_tslib.'class.tslib_content.php');
require_once(t3lib_extMgm::extPath("jhe_dam_extender") . 'util/util.php');

class ajax_getDocumentsByDirectoryAndCategory extends tslib_pibase {

	var $extKey = 'jhe_dam_extender';

	/**
	* Main Methode
	*
	* @return	string
	*/
	public function main() {

		$util = new util();

		//Generate TSFE object to use in ajax class
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');
		tslib_eidtools::connectDB();

		$TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');
		$id = isset($HTTP_GET_VARS['id']) ? $HTTP_GET_VARS['id'] : 0;

		$GLOBALS['TSFE'] = new $TSFEclassName($TYPO3_CONF_VARS, $id, '0', 1, '', '', '', '');
		$GLOBALS['TSFE']->connectToMySQL();
		$GLOBALS['TSFE']->fe_user = tslib_eidtools::initFeUser();
		$GLOBALS['TSFE']->fetch_the_id();
		$GLOBALS['TSFE']->getPageAndRootline();
		$GLOBALS['TSFE']->initTemplate();
		$GLOBALS['TSFE']->tmpl->getFileName_backPath = PATH_site;
		$GLOBALS['TSFE']->forceTemplateParsing = 1;
		$GLOBALS['TSFE']->getConfigArray();
		$GLOBALS['TSFE']->set_no_cache();
		$this->cObj = t3lib_div::makeInstance('tslib_cObj');

		//GET-Params
		$type = t3lib_div::_GET('docType');
		$catId = t3lib_div::_GET('catId');

		//get data from ext_local_conf
		$this->extconf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
		$mainFolder = $this->extconf['mainFolder'];

		//Prepare where clause for select statement
		$where = ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0';
		$where .= ' AND tx_dam_mm_cat.uid_foreign = ' . $catId;

		//generate SQL request
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'tx_dam_cat.title as catTitle, tx_dam.*',
			'tx_dam',
			'tx_dam_mm_cat',
			'tx_dam_cat',
			$where,
			'',
			'tx_dam.title'
		);

		//Make list rows for specific document types
		$items=array();        
		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$itemFilePath = strtolower(substr($this->internal['currentRow']['file_path'], strlen($mainFolder), -1));
			if($itemFilePath == $type){
				$items[]=$this->makeListItem($this->extconf);
			}
		}
//t3lib_utility_Debug::debug(count($items));

		$linkFolder = strtolower($this->extconf['linkFolder']);

		//generate non-table html output for unordered lsit trhat can be paginated
		if($linkFolder != $type){
			$output = '
				<h4>' . $util->translate($type) . '</h4>
				<ul class="content">' . implode(chr(10),$items) . '</ul>
			';
			
			$jsonReturnArr = array(
			'content' => $output,
			'counter' => count($items)
		);
		} else {
			
			$returnArr = $this->getLinks($this->extconf);
			
			$output = '
				<h4>' . $util->translate($type) . '</h4>
				<ul class="content">' . $returnArr['content'] . '</ul>
			';
			
			$jsonReturnArr = array(
				'content' => $output,
				'counter' => $returnArr['counter']
			);
		}

		return t3lib_div::array2json($jsonReturnArr);
	}

	/**
	* Implodes a single row from a database to a single line
	*
	* @param	array		$extconf: extension configuration variables
	* @return	Imploded	$output: HTML per column
	*/
	function makeListItem($extconf)	{
		$util = new util();
		$this->extconf = $extconf;
	
		//generates an array with all possible file types from which thumbnails can be generated
		$workingTypes = explode(',',$this->extconf['graphicFileTypes']);

		//generates preview thumbnails
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
				'altText' => '' . $util->translate('nothumbavailable') . ''
			);
			$previewImg = $this->cObj->IMAGE($preview);

		}

			//get icon for document type
			$typeIcon = array(
				'file' => 'typo3conf/ext/jhe_dam_extender/res/img/icons/' . $this->getFieldContent('file_type') . '.gif'
			);

		//get download icon
		$downloadIcon = array(
			'file' => 'typo3conf/ext/jhe_dam_extender/res/img/downloadNew.gif',
			'altText' => '' . $util->translate('downloadlink') . '',
			'wrap' => '<strong>|</strong>'
		);

		//Convert filesize to kb
		$file_size = round($this->getFieldContent('file_size') / 1024);

		//get creation date
		if($this->getFieldContent('date_mod')+$util->daysToSeconds($this->extconf['newPeriod']) > time()) {
			//get new icon
			$newIcon = array(
				'file' => 'fileadmin/templates/img/new2.png',
				'altText' => '' . $util->translate('isnew') . ''
			);
			$newIcon = '<div class="specialTopicAjaxListNewIcon">' . $this->cObj->IMAGE($newIcon) . '</div>';
		} else {
			$newIcon = '<div class="specialTopicAjaxListNewIcon"></div>';
		}

		//generates HTML output
		$output .= '
			<li>
				' . $newIcon . '
				<div class="specialTopicAjaxListDownload"><a href="' . $this->getFieldContent('file_path') . $this->getFieldContent('file_name') . '" title="' . $this->getFieldContent('title') . '" target="_blank">' . $this->cObj->IMAGE($downloadIcon) . '</a></div>
				<div class="specialTopicAjaxListTitle">' . $this->getFieldContent('title') . '</div>
				<div class="specialTopicAjaxListDetails">
					<span class="specialTopicAjaxListDetailsType">' . $this->cObj->IMAGE($typeIcon) . '</span>
					<span class="specialTopicAjaxListDetailsDate">' . date('d.m.Y', $this->getFieldContent('date_mod')) . '</span>
					<span class="specialTopicAjaxListDetailsSize">' . $file_size . ' ' . $util->translate('kbyte') . '</span>
				</div>
			</li>
		';
		
		return $output;
	}


	/**
	* Gets link data from a predefined txt-file
	*
	* @param	array		$extconf: extension configuration variables
	* @return	Imploded	$output: HTML per column
	*/
	function getLinks($extconf) {
		$this->extconf = $extconf;
		$util = new util();

		$filePath = $this->extconf['mainFolder'] . $this->extconf['linkFolder'];
		$currentCategory = t3lib_div::_GET('catId');

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'tx_dam_cat.title as catTitle',
			'tx_dam_cat',
			'uid=' . $currentCategory
		);
		$catTitle = $util->catToString(implode($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)));
		$currentFile = $filePath . '/links_' . $catTitle . '.txt';

		//get link icon
		$linkIcon = array(
			'file' => 'typo3conf/ext/jhe_dam_extender/res/img/link.gif',
			'altText' => '' . $util->translate('openslink') . ''
		);

		$newIcon = '<div class="specialTopicAjaxListNewIcon"></div>';
		
        if(file_exists($currentFile)){
			$handle = file($currentFile);
			
			foreach ($handle as $link) {
				$linkData = explode('|', $link);
				$output .= '
					<li>
						' . $newIcon . '
						<div class="specialTopicAjaxListDownload"><a href=\'' . $linkData[0] . '\' target=\'_blank\' alt=\'' . $linkData[1] . '\' title=\'' . $linkData[1] . '\'>' . $this->cObj->IMAGE($linkIcon). '</a></div>
						<div class="specialTopicAjaxListTitle">' . $linkData[1] . '</div>
						<div class="specialTopicAjaxListDetails">'. $linkData[2] .'</div>
					</li>
				';
			}
			
			$return = array(
				'content' => $output,
				'counter' => count($handle)
			);
		} else {
			$return = array(
				'content' => 'Fehler aufgetreten!',
				'counter' => '0'
			);
		}
		
		return $return;
	}

	/**
	* Returns the content of a given field
	*
	* @param	string		$fN: name of table field
	* @return	string		Value of the field
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
}

$output = t3lib_div::makeInstance('ajax_getDocumentsByDirectoryAndCategory');
echo $output->main();
?>