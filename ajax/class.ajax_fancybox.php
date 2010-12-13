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
require_once(PATH_tslib.'class.tslib_eidtools.php');
require_once(PATH_t3lib.'class.t3lib_div.php');
require_once(t3lib_extMgm::extPath("jhe_dam_extender") . 'util/util.php');

class ajax_fancybox extends tslib_pibase {

    var $extKey = 'jhe_dam_extender';
		 
    /**
    * Main Methode
    *
    * @return string
    */
    public function main() {
        $util = new util();
        tslib_eidtools::connectDB();
        $feUserObject = tslib_eidtools::initFeUser();

        //retrieving GET data
        $docId = t3lib_div::_GET('docId');

        

	
			 
	return $docId;
        }
    }

    function getSrcPathFromImgObj($path){


        $fancypic = array(
            'file' => $path
        );

        // Preconfigure the typolink
        $this->local_cObj = t3lib_div::makeInstance("tslib_cObj");
        $this->local_cObj->setCurrentVal($GLOBALS["TSFE"]->id);
        $this->typolink_conf = $this->conf["typolink."];
        #$this->typolink_conf["parameter."]["current"] = 1;
        $this->typolink_conf["additionalParams"] =
        $this->cObj->stdWrap($this->typolink_conf["additionalParams"],
        $this->typolink_conf["additionalParams."]);
        unset($this->typolink_conf["additionalParams."]);

        $i = strpos($this->cObj->IMAGE($fancypic), 'src');
        $string = substr($this->cObj->IMAGE($fancypic), $i);
        $i = strpos($string, "\"");
        $string = substr($string, $i);
        $string = substr($string, 1);
        $j = strpos($string, '"');

        $pathToGeneratedPic = substr($string, 0, $j);

        return $pathToGeneratedPic;
    }

    $output = t3lib_div::makeInstance('ajax_fancybox');
    echo $output->main();
?>
