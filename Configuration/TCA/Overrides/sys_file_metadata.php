<?php
defined('TYPO3_MODE') or die();

$extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tag_items']);
$targetPid = !empty($extConfig['tagPid']) ? (int)$extConfig['tagPid'] : 1;

$additionalColumns = [
	'tags' => [
		'exclude' => 1,
		'l10n_mode' => 'mergeIfNotBlank',
		'label' => 'LLL:EXT:tag_items/Resources/Private/Language/locallang_db.xlf:tags',
		'config' => array(
			'type' => 'group',
			'internal_type' => 'db',
			'allowed' => 'tx_tagitems_domain_model_tag',
			'MM' => 'tx_tagitems_domain_model_tag_mm',
			'MM_match_fields' => [
				'tablenames' => 'sys_file_metadata',
				'fieldname' => 'tags',
			],
			'MM_opposite_field' => 'items',
			'foreign_table' => 'tx_tagitems_domain_model_tag',
			'size' => 10,
			'autoSizeMax' => 20,
			'minitems' => 0,
			'maxitems' => 20,
			'wizards' => array(
				'_PADDING' => 2,
				'_VERTICAL' => 1,
				'suggest' => array(
					'type' => 'suggest',
					'default' => array(
						'currentTable' => 'sys_file_metadata',
						'receiverClass' => 'BeechIt\\TagItems\\Hooks\\SuggestReceiver'
					),
				),
//				'list' => array(
//					'type' => 'script',
//					'title' => 'list',
//					'icon' => 'list.gif',
//					'params' => array(
//						'table' => 'tx_tagitems_domain_model_tag',
//						'pid' => $targetPid,
//					),
//					'script' => 'wizard_list.php',
//				)
			),
		),
	]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file_metadata', $additionalColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'sys_file_metadata',
	'tags',
	'',
	'after:fe_groups'
);
