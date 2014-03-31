<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_jhedamextender_usage'] = array (
	'ctrl' => $TCA['tx_jhedamextender_usage']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l10n_parent,l10n_diffsource,hidden,usage_ea619ffddc'
	),
	'feInterface' => $TCA['tx_jhedamextender_usage']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l10n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config' => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table' => 'tx_jhedamextender_usage',
				'foreign_table_where' => 'AND tx_jhedamextender_usage.pid=###CURRENT_PID### AND tx_jhedamextender_usage.sys_language_uid IN (-1,0)',
			)
		),
		'l10n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'usage_ea619ffddc' => array (
			'exclude' => 0,
			'label' => 'LLL:EXT:jhe_dam_extender/locallang_db.xml:tx_jhedamextender_usage.usage_ea619ffddc',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, usage_ea619ffddc')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);

$TCA['tx_jhedamextender_doctype'] = array (
	'ctrl' => $TCA['tx_jhedamextender_doctype']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden,name,desciption'
	),
	'feInterface' => $TCA['tx_jhedamextender_doctype']['feInterface'],
	'columns' => array (
		't3ver_label' => array (
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array (
				'type' => 'check',
				'default' => '0'
			)
		),
		'name' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:jhe_qm_pages/locallang_db.xml:tx_jhedamextender_doctype.name',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'eval' => 'required,trim',
			)
		),
		'desciption' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:jhe_qm_pages/locallang_db.xml:tx_jhedamextender_doctype.desciption',
			'config' => array (
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'hidden;;1;;1-1-1, name, desciption')
	),
	'palettes' => array (
		'1' => array('showitem' => '')
	)
);
?>