<?php

########################################################################
# Extension Manager/Repository config file for ext "jhe_dam_extender".
#
# Auto generated 28-06-2010 15:13
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'DAM Extender',
	'description' => 'Provides a new editable select field to any DAM media file that gives the opportunity to select a flag for special usage of that file apart from the categorization issue.',
	'category' => 'plugin',
	'author' => 'Jari-Hermann Ernst',
	'author_email' => 'jari-hermann.ernst@bad-gmbh.de',
	'shy' => '',
	'dependencies' => 'dam',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.0.0',
	'constraints' => array(
		'depends' => array(
			'dam' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:106:{s:9:"ChangeLog";s:4:"5c1e";s:10:"README.txt";s:4:"9fa9";s:21:"ext_conf_template.txt";s:4:"fb50";s:12:"ext_icon.gif";s:4:"2397";s:17:"ext_localconf.php";s:4:"db2b";s:14:"ext_tables.php";s:4:"510d";s:14:"ext_tables.sql";s:4:"7b04";s:32:"icon_tx_jhedamextender_usage.gif";s:4:"d0a4";s:13:"locallang.xml";s:4:"ecd4";s:16:"locallang_db.xml";s:4:"39fe";s:7:"tca.php";s:4:"b473";s:40:"ajax/class.ajax_downloadSpecialUsage.php";s:4:"d23a";s:54:"ajax/class.ajax_getDocumentsByDirectoryAndCategory.php";s:4:"59a8";s:19:"doc/wizard_form.dat";s:4:"5163";s:20:"doc/wizard_form.html";s:4:"9582";s:14:"pi1/ce_wiz.gif";s:4:"cf99";s:35:"pi1/class.tx_jhedamextender_pi1.php";s:4:"2882";s:43:"pi1/class.tx_jhedamextender_pi1_wizicon.php";s:4:"84e1";s:13:"pi1/clear.gif";s:4:"cc11";s:19:"pi1/flexform_ds.xml";s:4:"ba63";s:17:"pi1/locallang.xml";s:4:"9076";s:20:"pi1/gfx/download.gif";s:4:"33d6";s:24:"pi1/gfx/dummy250x250.gif";s:4:"8e37";s:21:"pi1/gfx/icons/3ds.gif";s:4:"122b";s:25:"pi1/gfx/icons/CREDITS.txt";s:4:"9acc";s:20:"pi1/gfx/icons/ai.gif";s:4:"2cb7";s:21:"pi1/gfx/icons/ani.gif";s:4:"3d5f";s:20:"pi1/gfx/icons/au.gif";s:4:"0199";s:21:"pi1/gfx/icons/avi.gif";s:4:"27bd";s:21:"pi1/gfx/icons/bmp.gif";s:4:"a7a6";s:21:"pi1/gfx/icons/cdr.gif";s:4:"6fc9";s:21:"pi1/gfx/icons/css.gif";s:4:"4786";s:21:"pi1/gfx/icons/csv.gif";s:4:"e413";s:25:"pi1/gfx/icons/default.gif";s:4:"ec6e";s:21:"pi1/gfx/icons/doc.gif";s:4:"8c62";s:21:"pi1/gfx/icons/dtd.gif";s:4:"48e2";s:21:"pi1/gfx/icons/eps.gif";s:4:"4262";s:21:"pi1/gfx/icons/exe.gif";s:4:"e703";s:21:"pi1/gfx/icons/fh3.gif";s:4:"b429";s:23:"pi1/gfx/icons/flash.gif";s:4:"0ab7";s:24:"pi1/gfx/icons/folder.gif";s:4:"f3ad";s:21:"pi1/gfx/icons/gif.gif";s:4:"1559";s:21:"pi1/gfx/icons/htm.gif";s:4:"54de";s:22:"pi1/gfx/icons/html.gif";s:4:"3cea";s:23:"pi1/gfx/icons/html1.gif";s:4:"fdc5";s:23:"pi1/gfx/icons/html2.gif";s:4:"ae09";s:23:"pi1/gfx/icons/html3.gif";s:4:"23ae";s:21:"pi1/gfx/icons/ico.gif";s:4:"6756";s:21:"pi1/gfx/icons/inc.gif";s:4:"57e4";s:22:"pi1/gfx/icons/java.gif";s:4:"52de";s:21:"pi1/gfx/icons/jpg.gif";s:4:"23ac";s:20:"pi1/gfx/icons/js.gif";s:4:"7a5a";s:21:"pi1/gfx/icons/max.gif";s:4:"d6f4";s:21:"pi1/gfx/icons/mid.gif";s:4:"0d95";s:21:"pi1/gfx/icons/mov.gif";s:4:"d5e6";s:21:"pi1/gfx/icons/mp3.gif";s:4:"b37e";s:22:"pi1/gfx/icons/mpeg.gif";s:4:"15b5";s:21:"pi1/gfx/icons/mpg.gif";s:4:"15b5";s:21:"pi1/gfx/icons/pcd.gif";s:4:"e9f3";s:21:"pi1/gfx/icons/pcx.gif";s:4:"7d29";s:21:"pi1/gfx/icons/pdf.gif";s:4:"5c5f";s:22:"pi1/gfx/icons/php3.gif";s:4:"e58b";s:21:"pi1/gfx/icons/png.gif";s:4:"fffc";s:21:"pi1/gfx/icons/ppt.gif";s:4:"8740";s:20:"pi1/gfx/icons/ps.gif";s:4:"0b48";s:21:"pi1/gfx/icons/psd.gif";s:4:"4448";s:21:"pi1/gfx/icons/rtf.gif";s:4:"f660";s:22:"pi1/gfx/icons/sgml.gif";s:4:"13e6";s:21:"pi1/gfx/icons/swf.gif";s:4:"d44f";s:21:"pi1/gfx/icons/sxc.gif";s:4:"a29e";s:21:"pi1/gfx/icons/sxw.gif";s:4:"2bc9";s:21:"pi1/gfx/icons/t3d.gif";s:4:"9a71";s:21:"pi1/gfx/icons/t3x.gif";s:4:"558e";s:21:"pi1/gfx/icons/tga.gif";s:4:"20fc";s:21:"pi1/gfx/icons/tif.gif";s:4:"533b";s:22:"pi1/gfx/icons/tmpl.gif";s:4:"5114";s:21:"pi1/gfx/icons/ttf.gif";s:4:"9f93";s:21:"pi1/gfx/icons/txt.gif";s:4:"d7f9";s:21:"pi1/gfx/icons/wav.gif";s:4:"6931";s:21:"pi1/gfx/icons/wrl.gif";s:4:"132d";s:21:"pi1/gfx/icons/xls.gif";s:4:"4a22";s:21:"pi1/gfx/icons/xml.gif";s:4:"2e7b";s:21:"pi1/gfx/icons/xsl.gif";s:4:"2a99";s:21:"pi1/gfx/icons/zip.gif";s:4:"5de4";s:20:"pi1/static/setup.txt";s:4:"14c9";s:14:"pi2/ce_wiz.gif";s:4:"cf99";s:35:"pi2/class.tx_jhedamextender_pi2.php";s:4:"46e3";s:43:"pi2/class.tx_jhedamextender_pi2_wizicon.php";s:4:"c29f";s:13:"pi2/clear.gif";s:4:"cc11";s:19:"pi2/flexform_ds.xml";s:4:"0327";s:17:"pi2/locallang.xml";s:4:"e575";s:14:"pi3/ce_wiz.gif";s:4:"cf99";s:35:"pi3/class.tx_jhedamextender_pi3.php";s:4:"efbf";s:43:"pi3/class.tx_jhedamextender_pi3_wizicon.php";s:4:"2400";s:13:"pi3/clear.gif";s:4:"cc11";s:19:"pi3/flexform_ds.xml";s:4:"0327";s:17:"pi3/locallang.xml";s:4:"4532";s:14:"pi4/ce_wiz.gif";s:4:"cf99";s:35:"pi4/class.tx_jhedamextender_pi4.php";s:4:"8d89";s:43:"pi4/class.tx_jhedamextender_pi4_wizicon.php";s:4:"e8b0";s:13:"pi4/clear.gif";s:4:"cc11";s:19:"pi4/flexform_ds.xml";s:4:"0327";s:17:"pi4/locallang.xml";s:4:"f5c5";s:16:"res/css/main.css";s:4:"2d18";s:37:"static/jhe_dam_extender/constants.txt";s:4:"d41d";s:33:"static/jhe_dam_extender/setup.txt";s:4:"d41d";}',
);

?>