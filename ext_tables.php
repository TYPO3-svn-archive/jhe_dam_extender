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
?>