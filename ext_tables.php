<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

// Add 'Page contains plugin' icon
$TCA['pages']['columns']['module']['config']['items'][] = ['Tags', 'tags', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'ext_icon.png'];
\TYPO3\CMS\Backend\Sprite\SpriteManager::addTcaTypeIcon('pages', 'contains-tags', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'ext_icon.png');

/* ===========================================================================
 	Ajax call to save tags
=========================================================================== */
if (TYPO3_MODE == 'BE') {
	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['TagItems::createTag'] = [
		'callbackMethod' => 'BeechIt\\TagItems\\Hooks\\SuggestReceiverCall->createTag',
		'csrfTokenCheck' => FALSE
	];
}

// Show tags in page module
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['cms']['db_layout']['addTables']['tx_tagitems_domain_model_tag'][0] = [
	'fList' => 'name, created_at',
	'icon' => TRUE
];