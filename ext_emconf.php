<?php

########################################################################
# Extension Manager/Repository config file for ext "jhe_dam_extender".
#
# Auto generated 20-09-2010 13:16
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'B·A·D DAM Extender',
	'description' => 'Provides a new editable select field to any DAM media file that gives the opportunity to select a flag for special usage of that file apart from the categorization issue.',
	'category' => 'plugin',
	'author' => 'Jari-Hermann Ernst',
	'author_email' => 'jari-hermann.ernst@bad-gmbh.de',
	'shy' => '',
	'dependencies' => 'dam',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 1,
	'lockType' => '',
	'author_company' => 'B·A·D GmbH',
	'version' => '0.2.3',
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
	'_md5_values_when_last_written' => 'a:107:{s:9:"ChangeLog";s:4:"5c1e";s:10:"README.txt";s:4:"9fa9";s:21:"ext_conf_template.txt";s:4:"1c0f";s:12:"ext_icon.gif";s:4:"2397";s:17:"ext_localconf.php";s:4:"db2b";s:14:"ext_tables.php";s:4:"472c";s:14:"ext_tables.sql";s:4:"56e3";s:32:"icon_tx_jhedamextender_usage.gif";s:4:"d0a4";s:13:"locallang.xml";s:4:"0d6f";s:16:"locallang_db.xml";s:4:"39fe";s:7:"tca.php";s:4:"1ae8";s:40:"ajax/class.ajax_downloadSpecialUsage.php";s:4:"8686";s:54:"ajax/class.ajax_getDocumentsByDirectoryAndCategory.php";s:4:"8bb6";s:19:"doc/wizard_form.dat";s:4:"5163";s:20:"doc/wizard_form.html";s:4:"9582";s:14:"pi1/ce_wiz.gif";s:4:"cf99";s:35:"pi1/class.tx_jhedamextender_pi1.php";s:4:"7c50";s:43:"pi1/class.tx_jhedamextender_pi1_wizicon.php";s:4:"97c8";s:13:"pi1/clear.gif";s:4:"cc11";s:19:"pi1/flexform_ds.xml";s:4:"ba63";s:20:"pi1/static/setup.txt";s:4:"14c9";s:14:"pi2/ce_wiz.gif";s:4:"cf99";s:35:"pi2/class.tx_jhedamextender_pi2.php";s:4:"611e";s:43:"pi2/class.tx_jhedamextender_pi2_wizicon.php";s:4:"c29f";s:13:"pi2/clear.gif";s:4:"cc11";s:19:"pi2/flexform_ds.xml";s:4:"8fa6";s:14:"pi3/ce_wiz.gif";s:4:"cf99";s:35:"pi3/class.tx_jhedamextender_pi3.php";s:4:"ea9a";s:43:"pi3/class.tx_jhedamextender_pi3_wizicon.php";s:4:"066a";s:13:"pi3/clear.gif";s:4:"cc11";s:19:"pi3/flexform_ds.xml";s:4:"8db9";s:14:"pi4/ce_wiz.gif";s:4:"cf99";s:35:"pi4/class.tx_jhedamextender_pi4.php";s:4:"45ef";s:43:"pi4/class.tx_jhedamextender_pi4_wizicon.php";s:4:"e8b0";s:13:"pi4/clear.gif";s:4:"cc11";s:19:"pi4/flexform_ds.xml";s:4:"0327";s:16:"res/css/main.css";s:4:"ab3b";s:22:"res/img/ajaxloader.gif";s:4:"c608";s:20:"res/img/download.gif";s:4:"33d6";s:24:"res/img/dummy250x250.gif";s:4:"8e37";s:15:"res/img/new.gif";s:4:"6d9b";s:17:"res/img/world.gif";s:4:"c8c3";s:17:"res/img/world.png";s:4:"c80b";s:21:"res/img/icons/3ds.gif";s:4:"122b";s:25:"res/img/icons/CREDITS.txt";s:4:"9acc";s:20:"res/img/icons/ai.gif";s:4:"2cb7";s:21:"res/img/icons/ani.gif";s:4:"3d5f";s:20:"res/img/icons/au.gif";s:4:"0199";s:21:"res/img/icons/avi.gif";s:4:"27bd";s:21:"res/img/icons/bmp.gif";s:4:"a7a6";s:21:"res/img/icons/cdr.gif";s:4:"6fc9";s:21:"res/img/icons/css.gif";s:4:"4786";s:21:"res/img/icons/csv.gif";s:4:"e413";s:25:"res/img/icons/default.gif";s:4:"ec6e";s:21:"res/img/icons/doc.gif";s:4:"8c62";s:21:"res/img/icons/dtd.gif";s:4:"48e2";s:21:"res/img/icons/eps.gif";s:4:"4262";s:21:"res/img/icons/exe.gif";s:4:"e703";s:21:"res/img/icons/fh3.gif";s:4:"b429";s:23:"res/img/icons/flash.gif";s:4:"0ab7";s:24:"res/img/icons/folder.gif";s:4:"f3ad";s:21:"res/img/icons/gif.gif";s:4:"1559";s:21:"res/img/icons/htm.gif";s:4:"54de";s:22:"res/img/icons/html.gif";s:4:"3cea";s:23:"res/img/icons/html1.gif";s:4:"fdc5";s:23:"res/img/icons/html2.gif";s:4:"ae09";s:23:"res/img/icons/html3.gif";s:4:"23ae";s:21:"res/img/icons/ico.gif";s:4:"6756";s:21:"res/img/icons/inc.gif";s:4:"57e4";s:22:"res/img/icons/java.gif";s:4:"52de";s:21:"res/img/icons/jpg.gif";s:4:"23ac";s:20:"res/img/icons/js.gif";s:4:"7a5a";s:21:"res/img/icons/max.gif";s:4:"d6f4";s:21:"res/img/icons/mid.gif";s:4:"0d95";s:21:"res/img/icons/mov.gif";s:4:"d5e6";s:21:"res/img/icons/mp3.gif";s:4:"b37e";s:22:"res/img/icons/mpeg.gif";s:4:"15b5";s:21:"res/img/icons/mpg.gif";s:4:"15b5";s:21:"res/img/icons/pcd.gif";s:4:"e9f3";s:21:"res/img/icons/pcx.gif";s:4:"7d29";s:21:"res/img/icons/pdf.gif";s:4:"5c5f";s:22:"res/img/icons/php3.gif";s:4:"e58b";s:21:"res/img/icons/png.gif";s:4:"fffc";s:21:"res/img/icons/ppt.gif";s:4:"8740";s:20:"res/img/icons/ps.gif";s:4:"0b48";s:21:"res/img/icons/psd.gif";s:4:"4448";s:21:"res/img/icons/rtf.gif";s:4:"f660";s:22:"res/img/icons/sgml.gif";s:4:"13e6";s:21:"res/img/icons/swf.gif";s:4:"d44f";s:21:"res/img/icons/sxc.gif";s:4:"a29e";s:21:"res/img/icons/sxw.gif";s:4:"2bc9";s:21:"res/img/icons/t3d.gif";s:4:"9a71";s:21:"res/img/icons/t3x.gif";s:4:"558e";s:21:"res/img/icons/tga.gif";s:4:"20fc";s:21:"res/img/icons/tif.gif";s:4:"533b";s:22:"res/img/icons/tmpl.gif";s:4:"5114";s:21:"res/img/icons/ttf.gif";s:4:"9f93";s:21:"res/img/icons/txt.gif";s:4:"d7f9";s:21:"res/img/icons/wav.gif";s:4:"6931";s:21:"res/img/icons/wrl.gif";s:4:"132d";s:21:"res/img/icons/xls.gif";s:4:"4a22";s:21:"res/img/icons/xml.gif";s:4:"2e7b";s:21:"res/img/icons/xsl.gif";s:4:"2a99";s:21:"res/img/icons/zip.gif";s:4:"5de4";s:37:"static/jhe_dam_extender/constants.txt";s:4:"d41d";s:33:"static/jhe_dam_extender/setup.txt";s:4:"d41d";s:13:"util/util.php";s:4:"2ace";}',
);

?>