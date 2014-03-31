<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010-14 Jari-Hermann Ernst <jari-hermann.ernst@bad-gmbh.de>
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

class util extends tslib_pibase {

	/**
	 * Translates labels and output
	 *
	 * @param	string		$type: string to be translated
	 * @return	string		translated string
	 */
	public function translate($type){
		$this->lang = $this->getLanguageSupport();

		if(!$this->lang->sL('LLL:EXT:jhe_dam_extender/locallang.xml:'. strtolower($type) .'', 1)) {
			return 'TRANSLATE: ' . $type;
		} else {
			return $this->lang->sL('LLL:EXT:jhe_dam_extender/locallang.xml:'. strtolower($type) .'', 1);
		}
	}

	/**
	 * provides language support for ajax functions
	 *
	 * @return	object		$LANG: Language object
	 */
	public function getLanguageSupport() {
		require_once(PATH_typo3.'sysext/lang/lang.php');
		$LANG = t3lib_div::makeInstance('language');
		$LANG->lang = 'de';
		$LANG->charSet = 'utf-8';

		return $LANG;
	}

	/**
	 * Calculates seconds form given number of day
	 *
	 * @param	int		$days: Number of days given from localconf.php for the period of time a record is marked as new
	 * @return	int		number of seconds for calculating with timestamps
	 */
	public function daysToSeconds($days) {
		return 60*60*24*$days;
	}

	/**
	 * Replaces empty spaces in names/description with a '_'
	 *
	 * @param	string		$value: name
	 * @return	string		$newValue: transformed name
	 */
	public function replaceEmptySpaces ($value) {
		$newValue = str_replace(' ', '_', $value);

		return $newValue;
	}

	/**
	 * Replaces german umlauts in filenames
	 *
	 * @param sting  $string: filename string from somewhere
	 * @return string  $string: new better readable filename
	 */
	public function replaceUmlauts ($value) {
		$input = array("ä", "ö", "ü", "Ä", "Ö", "Ü", "ß");
		$output = array("ae", "oe", "ue", "Ae", "Oe", "Ue", "ss");

		return str_replace($input, $output, $value);
	}

	/**
	 * Transforms an category name in an script side readable one
	 *
	 * @param    string          $value: category name
	 * @return	string		$newValue: transformed category name
	 */
	public function catToString($value){
		$util = new util();
		$newValue = strtolower($util->replaceEmptySpaces($util->replaceUmlauts($value)));

		return $newValue;
	}
}
?>