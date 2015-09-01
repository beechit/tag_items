<?php
namespace BeechIt\TagItems\Hooks;
/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 31-08-2015
 * All code (c) Beech Applications B.V. all rights reserved
 */
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SuggestReceiver
 */
class SuggestReceiver extends \TYPO3\CMS\Backend\Form\Element\SuggestDefaultReceiver{

	/**
	 * Queries a table for records and completely processes them
	 *
	 * Returns a two-dimensional array of almost finished records;
	 * they only need to be put into a <li>-structure
	 *
	 * @param array $params
	 * @param integer $recursionCounter recursion counter
	 * @return mixed array of rows or FALSE if nothing found
	 */
	public function queryTable(&$params, $recursionCounter = 0) {
		$uid = (int)GeneralUtility::_GP('uid');

		$records = parent::queryTable($params, $recursionCounter);
		$table = $this->config['currentTable'];

		if ($this->checkIfTagIsNotFound($records)) {
			$text = $params['value'];
			$javaScriptCode = '
var value=' . GeneralUtility::quoteJSvalue($text) . ';

Ext.Ajax.request({
	url : \'ajax.php\' ,
	params : { ajaxID:\'TagItems::createTag\', item:value, table:\'' . htmlspecialchars($table) . '\', uid:\'' . $uid . '\' },
	success: function ( result, request ) {
		var arr = result.responseText.split(\'-\');
		setFormValueFromBrowseWin(arr[5], arr[2] +  \'_\' + arr[0], arr[1]);
		TBE_EDITOR.fieldChanged(arr[3], arr[6], arr[4], arr[5]);
	},
	failure: function ( result, request) {
		Ext.MessageBox.alert(\'Failed\', result.responseText);
	}
});
';

			$javaScriptCode = trim(str_replace('"', '\'', $javaScriptCode));
			$link = implode(' ', explode(chr(10), $javaScriptCode));

			$records['tx_tagitems_domain_model_tag_' . strlen($text)] = array (
				'text' => '<div onclick="' . $link . '">
							<span class="suggest-path">
								<a>' .
					sprintf($GLOBALS['LANG']->sL('LLL:EXT:tag_items/Resources/Private/Language/locallang_db.xlf:tag_suggest'), htmlspecialchars($text)) .
					'</a>
							</span></div>',
				'table' => 'tx_tagitems_domain_model_tag',
				'class' => 'suggest-noresults',
				'style' => 'background-color:#E9F1FE !important;background-image:url(' . $this->getDummyIconPath() . ');',
			);
		}

		return $records;
	}

	/**
	 * Check if current tag is found.
	 *
	 * @param array $tags returned tags
	 * @return boolean
	 */
	protected function checkIfTagIsNotFound(array $tags) {
		if (count($tags) === 0) {
			return TRUE;
		}

		foreach ($tags as $tag) {
			if ($tag['label'] === $this->params['value']) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * @return string
	 */
	private function getDummyIconPath() {
		$icon = IconUtility::getIcon('tx_tagitems_domain_model_tag');
		return IconUtility::skinImg('', $icon, '', 1);
	}
}