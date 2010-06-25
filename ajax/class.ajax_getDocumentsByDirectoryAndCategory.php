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
#require_once(PATH_tslib.'class.tslib_eidtools.php');
#require_once(PATH_t3lib.'class.t3lib_div.php');

class ajax_getDocumentsByDirectoryAndCategory extends tslib_pibase {

	var $extKey        = 'jhe_dam_extender';
	
	/**
	 * Main Methode
	 *
	 * @return	string
	 */
	public function main() {
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
 
        $type = t3lib_div::_GET('docType');
		$catId = t3lib_div::_GET('catId');
				
		$where = ' AND tx_dam.deleted = 0 AND tx_dam.hidden = 0';
		$where .= ' AND tx_dam_mm_cat.uid_foreign = ' . $catId;
			
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'tx_dam_cat.title as catTitle, tx_dam.*',
			'tx_dam',
			'tx_dam_mm_cat',
			'tx_dam_cat',
			$where
		);
				
		$items=array();
			// Make list table rows
		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
			$items[]=$this->makeListItem();
		}
				
		$out .= '<h4>' . $type . '</h4>
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
	
		return $out;
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
}

$output = t3lib_div::makeInstance('ajax_getDocumentsByDirectoryAndCategory');
echo $output->main();
?>