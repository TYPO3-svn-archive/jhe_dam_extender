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
    'tx_jhedamextender_path' => array (
        'exclude' => 0,
        'label' => 'LLL:EXT:jhe_dam_extender/locallang_db.xml:tx_dam.tx_jhedamextender_path',
        'config' => array (
            'type' => 'input',
            'size' => '30',
        )
    ),
    'tx_jhedamextender_lowlevel_selection' => array (
        'exclude' => 0,
        'label' => 'LLL:EXT:jhe_dam_extender/locallang_db.xml:tx_dam.tx_jhedamextender_lowlevel_selection',
        'config' => array (
            'type' => 'select',
            'items' => array (
                array('LLL:EXT:jhe_dam_extender/locallang_db.xml:tx_dam.tx_jhedamextender_lowlevel_selection.I.0', ''),
                array('LLL:EXT:jhe_dam_extender/locallang_db.xml:tx_dam.tx_jhedamextender_lowlevel_selection.I.1', 'Kunde'),
                array('LLL:EXT:jhe_dam_extender/locallang_db.xml:tx_dam.tx_jhedamextender_lowlevel_selection.I.2', 'Vertrieb'),
                array('LLL:EXT:jhe_dam_extender/locallang_db.xml:tx_dam.tx_jhedamextender_lowlevel_selection.I.3', 'Vertrieb und Zentrum'),
            ),
            'size' => 1,
            'maxitems' => 1,
        )
    ),
    'tx_jhedamextender_order' => array (
        'exclude' => 0,
        'label' => 'LLL:EXT:jhe_dam_extender/locallang_db.xml:tx_dam.tx_jhedamextender_order',
        'config' => array (
            'type'     => 'input',
            'size'     => '4',
            'max'      => '4',
            'eval'     => 'int',
            'checkbox' => '0',
            'range'    => array (
                'upper' => '1000000',
                'lower' => '1'
            ),
            'default' => 0
        )
    ),
);


t3lib_div::loadTCA('tx_dam');
t3lib_extMgm::addTCAcolumns('tx_dam',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('tx_dam','--div--;Fachthemen;;;;1-1-1, tx_jhedamextender_usage, tx_jhedamextender_path, tx_jhedamextender_lowlevel_selection, tx_jhedamextender_order');

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

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi2', 'FILE:EXT:' . $_EXTKEY . '/pi2/flexform_ds.xml');

t3lib_extMgm::addPlugin(array(
    'LLL:EXT:jhe_dam_extender/locallang_db.xml:tt_content.list_type_pi2',
    $_EXTKEY . '_pi2',
    t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');


if (TYPO3_MODE == 'BE') {
    $TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_jhedamextender_pi2_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi2/class.tx_jhedamextender_pi2_wizicon.php';
}


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi3']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi3']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi3', 'FILE:EXT:' . $_EXTKEY . '/pi3/flexform_ds.xml');


t3lib_extMgm::addPlugin(array(
    'LLL:EXT:jhe_dam_extender/locallang_db.xml:tt_content.list_type_pi3',
    $_EXTKEY . '_pi3',
    t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');


if (TYPO3_MODE == 'BE') {
    $TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_jhedamextender_pi3_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi3/class.tx_jhedamextender_pi3_wizicon.php';
}

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi4']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi4']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi4', 'FILE:EXT:' . $_EXTKEY . '/pi4/flexform_ds.xml');


t3lib_extMgm::addPlugin(array(
    'LLL:EXT:jhe_dam_extender/locallang_db.xml:tt_content.list_type_pi4',
    $_EXTKEY . '_pi4',
    t3lib_extMgm::extRelPath($_EXTKEY) . 'ext_icon.gif'
),'list_type');


if (TYPO3_MODE == 'BE') {
    $TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_jhedamextender_pi4_wizicon'] = t3lib_extMgm::extPath($_EXTKEY).'pi4/class.tx_jhedamextender_pi4_wizicon.php';
}

t3lib_extMgm::addStaticFile($_EXTKEY,'static/dam_extender/', 'DAM Extender');
?>