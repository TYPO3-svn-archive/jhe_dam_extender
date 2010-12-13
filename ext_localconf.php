<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_jhedamextender_usage=1
');

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_jhedamextender_pi1.php', '_pi1', 'list_type', 1);

t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
	tt_content.shortcut.20.0.conf.tx_jhedamextender_usage = < plugin.'.t3lib_extMgm::getCN($_EXTKEY).'_pi1
	tt_content.shortcut.20.0.conf.tx_jhedamextender_usage.CMD = singleView
',43);

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi2/class.tx_jhedamextender_pi2.php', '_pi2', 'list_type', 1);

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi3/class.tx_jhedamextender_pi3.php', '_pi3', 'list_type', 1);

t3lib_extMgm::addPItoST43($_EXTKEY, 'pi4/class.tx_jhedamextender_pi4.php', '_pi4', 'list_type', 1);

$TYPO3_CONF_VARS['FE']['eID_include']['downloadSpecialUsage'] = 'EXT:jhe_dam_extender/ajax/class.ajax_downloadSpecialUsage.php';
$TYPO3_CONF_VARS['FE']['eID_include']['getDocumentsByDirectoryAndCategory'] = 'EXT:jhe_dam_extender/ajax/class.ajax_getDocumentsByDirectoryAndCategory.php';
$TYPO3_CONF_VARS['FE']['eID_include']['fancybox'] = 'EXT:jhe_dam_extender/ajax/class.ajax_fancybox.php';
?>