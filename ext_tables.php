<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$tempColumns = array (
	'tx_jhedamextender_usage' => array (		
		'exclude' => 0,		
		'label' => 'LLL:EXT:jhe_dam_extender/locallang_db.xml:tx_dam.tx_jhedamextender_usage',		
		'config' => array (
			'type' => 'select',	
			'items' => array (
				array('',0),
			),
			'foreign_table' => 'tx_jhedamextender_usage',	
			'foreign_table_where' => 'AND tx_jhedamextender_usage.pid=###CURRENT_PID### ORDER BY tx_jhedamextender_usage.uid',	
			'size' => 1,	
			'minitems' => 0,
			'maxitems' => 1,	
			'wizards' => array(
				'_PADDING'  => 2,
				'_VERTICAL' => 1,
				'add' => array(
					'type'   => 'script',
					'title'  => 'Create new record',
					'icon'   => 'add.gif',
					'params' => array(
						'table'    => 'tx_jhedamextender_usage',
						'pid'      => '###CURRENT_PID###',
						'setValue' => 'prepend'
					),
					'script' => 'wizard_add.php',
				),
				'list' => array(
					'type'   => 'script',
					'title'  => 'List',
					'icon'   => 'list.gif',
					'params' => array(
						'table' => 'tx_jhedamextender_usage',
						'pid'   => '###CURRENT_PID###',
					),
					'script' => 'wizard_list.php',
				),
			),
		)
	),
);


t3lib_div::loadTCA('tx_dam');
t3lib_extMgm::addTCAcolumns('tx_dam',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('tx_dam','tx_jhedamextender_usage;;;;1-1-1');

$TCA['tx_jhedamextender_usage'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:jhe_dam_extender/locallang_db.xml:tx_jhedamextender_usage',		
		'label'     => 'usage_ea619ffddc',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l10n_parent',	
		'transOrigDiffSourceField' => 'l10n_diffsource',	
		'default_sortby' => 'ORDER BY crdate',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_jhedamextender_usage.gif',
	),
);


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi1', 'FILE:EXT:' . $_EXTKEY . '/pi1/flexform_ds.xml');

t3lib_extMgm::addPlugin(array(
	'LLL:EXT:jhe_dam_extender/locallang_db.xml:tt_content.list_type_pi1',
	$_EXTKEY . '_pi1',
	t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');

t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/','DAM special usage');


if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_jhedamextender_pi1_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi1/class.tx_jhedamextender_pi1_wizicon.php';
}

t3lib_extMgm::addStaticFile($_EXTKEY,'static/dam_extender/', 'DAM Extender');
?>