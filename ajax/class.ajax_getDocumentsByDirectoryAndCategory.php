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

class ajax_getDocumentsByDirectoryAndCategory extends tslib_pibase {

	var $extKey        = 'jhe_dam_extender';

	/**
	 * Main Methode
	 *
	 * @return	string
	 */
	public function main() {

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

        //language support
		$this->lang = $this->getLanguageSupport();

        //GET-Params
        $type = t3lib_div::_GET('docType');
		$catId = t3lib_div::_GET('catId');

		//get data fro mext_local_conf
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
			$where
		);

		// Make list table rows for specific dosument types
		$items=array();
		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{

			$itemFilePath = strtolower(substr($this->internal['currentRow']['file_path'], strlen($mainFolder), -1));

			if($itemFilePath == $type){
				$items[]=$this->makeListItem($this->extconf);
			}
		}

		//generate HTML output (with headers)
		$output .= '<h4>' . $this->translate($type) . '</h4>
			<div' . $this->pi_classParam('listrow') . '>' .
				'<div' . $this->pi_classParam('listrowTitle') . '>' . $this->translate('bezeichnung') . '</div>' .
				'<div' . $this->pi_classParam('listrowSize') . '>' . $this->translate('groesse') . '</div>' .
				'<div' . $this->pi_classParam('listrowDate') . '>' . $this->translate('datum') . '</div>' .
				'<div' . $this->pi_classParam('listrowImage') . '>' . $this->translate('vorschau') . '</div>' .
				'<div' . $this->pi_classParam('listrowType') . '>' . $this->translate('typ') . '</div>' .
				'<div' . $this->pi_classParam('listrowLink') . '>' . $this->translate('download') . '</div>' .
			'</div>
			<hr />
			<div'.$this->pi_classParam('list').'>
				'.implode(chr(10),$items).'
			</div>';

		return $output;
	}

	/**
	 * Implodes a single row from a database to a single line
	 *
	 * @param	array		$extconf: extension configuration variables
	 * @return	Imploded		$output: HTML per column
	 */
	function makeListItem($extconf)	{

		$this->extconf = $extconf;

		//language support
		$this->lang = $this->getLanguageSupport();

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
				'altText' => '' . $this->translate('nothumbavailable') . ''
			);
		}

		//get icon for document type
		$typeIcon = array(
			'file' => 'typo3conf/ext/jhe_dam_extender/pi1/gfx/icons/' . $this->getFieldContent('file_type') . '.gif'
		);

		//get download icon
		$downloadIcon = array(
			'file' => 'typo3conf/ext/jhe_dam_extender/pi1/gfx/download.gif',
			'altText' => '' . $this->translate('downloadlink') . ''
		);

		//generates HTML output
		$output .= '
			<div' . $this->pi_classParam('listrow') . '>' .
				'<div' . $this->pi_classParam('listrowTitle') . '>' . $this->getFieldContent('title') . '</div>' .
				'<div' . $this->pi_classParam('listrowSize') . '>' . $this->getFieldContent('file_size') . ' ' . $this->translate('byte') . '</div>' .
				'<div' . $this->pi_classParam('listrowDate') . '>' . date('d.m.Y', $this->getFieldContent('crdate')) . '</div>' .
				'<div' . $this->pi_classParam('listrowImage') . '>' . $this->cObj->IMAGE($preview) . '</div>' .
				'<div' . $this->pi_classParam('listrowType') . '>' . $this->cObj->IMAGE($typeIcon) . '</div>' .
				'<div' . $this->pi_classParam('listrowLink') . '><a href="' . $this->getFieldContent('file_path') . $this->getFieldContent('file_name') . '" title="' . $this->getFieldContent('title') . '" target="_blank">' . $this->cObj->IMAGE($downloadIcon) . ' ' . $this->translate('downloadlink') . '</a></div>' .
			'</div>
			<hr />
			';

		return $output;
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

	/**
	 * Translates labels and output
	 *
	 * @param	string		$type: string to be translated
	 * @return	string		translated string
	 */
	function translate($type){
		return $this->lang->sL('LLL:EXT:jhe_dam_extender/pi4/locallang.xml:'. strtolower($type) .'', 1);
	}

	/**
	 * provides language support for ajax functions
	 *
	 * @return	object		$LANG: Language object
	 */
	function getLanguageSupport() {
		require_once(PATH_typo3.'sysext/lang/lang.php');
		$LANG = t3lib_div::makeInstance('language');
		$LANG->lang = 'de';
		$LANG->charSet = 'utf-8';
        return $LANG;
	}

}

$output = t3lib_div::makeInstance('ajax_getDocumentsByDirectoryAndCategory');
echo $output->main();
?>