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
 * Plugin 'DAM special usage menue' for the 'jhe_dam_extender' extension.
 *
 * @author	Jari-Hermann Ernst <jari-hermann.ernst@bad-gmbh.de>
 * @package	TYPO3
 * @subpackage	tx_jhedamextender
 */
class tx_jhedamextender_pi3 extends tslib_pibase {
    var $prefixId      = 'tx_jhedamextender_pi3';		// Same as class name
    var $scriptRelPath = 'pi3/class.tx_jhedamextender_pi3.php';	// Path to this script relative to the extension dir.
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

        //get dam category
        if(!t3lib_div::_GET('damcat')){
            $this->pi_initPIFlexForm();
            $this->conf['selectedCategory'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'catSelector');
        } else {
            $this->conf['selectedCategory'] = t3lib_div::_GET('damcat');
        }

        if(!$this->conf['selectedCategory']) {
            $content = '';
	} else {
            //$this->pi_initPIFlexForm();
            $this->conf['mediaFolder'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'mediaFolder');
            $this->conf['targetPage'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'targetPage');

            $content='
                <div' . $this->pi_classParam('specialUsageList') . '>' . $this->getSpecialUsageItems($this->conf) . $this->pi_getLL('err_no_specialusage') .'</div>
            ';
	}

	return $this->pi_wrapInBaseClass($content);
    }

    /**
    * Selects all items from a given category that belong to the special usage
    *
    * @param	array		$conf: PlugIn Configuration
    * @return	string		$content: ul content
    */
    function getSpecialUsageItems($conf) {
        $util = new util();
        $this->conf = $conf;
        $this->pi_loadLL();

        //Select all given special usage items from db
	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'uid,usage_ea619ffddc',
            'tx_jhedamextender_usage',
            'tx_jhedamextender_usage.deleted = 0 AND tx_jhedamextender_usage.hidden = 0 AND tx_jhedamextender_usage.pid = ' . $this->conf['mediaFolder']
	);

        //Collect every result from $res to a multi dimensional array
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $resArray[] = $row;
        }

        //loop through every array from $resArray to count the number of records which belong to the given category and to the special usage item
        foreach($resArray as $i) {
            $this->conf['title'] = $i['usage_ea619ffddc'];
            $this->conf['specialUsageId'] = $i['uid'];

            $res_mm = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
                'COUNT(\'tx_dam.*\')',
		'tx_dam',
		'tx_dam_mm_cat',
		'tx_dam_cat',
		' AND tx_dam.tx_jhedamextender_usage = ' . $i['uid'] . ' AND tx_dam_cat.uid = ' . $this->conf['selectedCategory']
            );

            //count results
            $counter = array_values($GLOBALS['TYPO3_DB']->sql_fetch_assoc($res_mm));

            //if any results given give back a list item
            if($counter['0']) {
                $result .= '<li>' . $this->makeLink($this->conf) . '</li>';
            }
        }

	if(!$result){
            $content = $util->translate('err_no_specialusage');
	} else {
            $content = '<ul>' .$result . '</ul>';
	}

	return $content;

    }

    /**
    * Generates the links
    *
    * @param	array		$conf: PlugIn Configuration
    * @return	string		$result: Link to page and category data
    */
    function makeLink($conf) {
        $this->conf = $conf;

	$params = array(
            'specialUsage' => $this->conf['specialUsageId'],
            'damcat' => $this->conf['selectedCategory'],
            'no_cache' => 1
	);
	$pid = $this->conf['targetPage'];
	$target = '_self';

	$result = $this->pi_linkToPage(
            $this->conf['title'],
            $pid,
            $target,
            $params
	);

	return $result;
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi3/class.tx_jhedamextender_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/jhe_dam_extender/pi3/class.tx_jhedamextender_pi3.php']);
}
?>