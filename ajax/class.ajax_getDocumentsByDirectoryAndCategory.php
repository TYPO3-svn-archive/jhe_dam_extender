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
#require_once(PATH_tslib.'class.tslib_eidtools.php');
#require_once(PATH_t3lib.'class.t3lib_div.php');

class ajax_getDocumentsByDirectoryAndCategory extends tslib_pibase {

	/**
	 * Main Methode
	 *
	 * @return	string
	 */
	public function main() {
		
		tslib_eidtools::connectDB();
		$feUserObject = tslib_eidtools::initFeUser();
		
		$conf = t3lib_div::_GET('conf');
		$type = t3lib_div::_GET('docType');
		
		#var_dump($conf);

		return $type;
	}
}

$output = t3lib_div::makeInstance('ajax_getDocumentsByDirectoryAndCategory');
echo $output->main();
?>