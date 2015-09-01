<?php
namespace BeechIt\TagItems\Hooks;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 31-08-2015
 * All code (c) Beech Applications B.V. all rights reserved
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SuggestReceiverCall
 */
class SuggestReceiverCall {

	const TAG = 'tx_tagitems_domain_model_tag';
	const LLPATH = 'LLL:EXT:tag_items/Resources/Private/Language/locallang_db.xlf:tag_suggest_';

	/**
	 * Create a tag
	 *
	 * @param array $params
	 * @param \TYPO3\CMS\Core\Http\AjaxRequestHandler $ajaxObj
	 * @return void
	 * @throws \Exception
	 */
	public function createTag(array $params, \TYPO3\CMS\Core\Http\AjaxRequestHandler $ajaxObj) {
		$request = GeneralUtility::_POST();

		try {
			// Check if a tag is submitted
			if (!isset($request['item']) || empty($request['item'])) {
				throw new \Exception('error_no-tag');
			}

			$itemUid = $request['uid'];
			if ((int)$itemUid === 0 && (strlen($itemUid) == 16 && !GeneralUtility::isFirstPartOfStr($itemUid, 'NEW'))) {
				throw new \Exception('error_no-uid');
			}

			$table = $request['table'];
			if (empty($table)) {
				throw new \Exception('error_no-table');
			}

			// Get tag uid
			$newTagId = $this->getTagUid($request);

			$ajaxObj->setContentFormat('javascript');
			$ajaxObj->setContent('');
			$response = array(
				$newTagId,
				$request['item'],
				self::TAG,
				$table,
				'tags',
				'data[' . htmlspecialchars($table) . '][' . $itemUid . '][tags]',
				$itemUid
			);
			$ajaxObj->setJavascriptCallbackWrap(implode('-', $response));
		} catch (\Exception $e) {
			$errorMsg = $GLOBALS['LANG']->sL(self::LLPATH . $e->getMessage());
			$ajaxObj->setError($errorMsg);
		}
	}

	/**
	 * Get the uid of the tag, either bei inserting as new or get existing
	 *
	 * @param array $request ajax request
	 * @return integer
	 * @throws \Exception
	 */
	protected function getTagUid(array $request) {
		// Get configuration from EM
		$extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tag_items']);
		$targetPid = !empty($extConfig['tagPid']) ? $extConfig['tagPid'] : 1;

		if ($targetPid === 0) {
			throw new \Exception('error_no-pid-defined');
		}

		$record = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow(
			'*',
			self::TAG,
			'deleted=0 AND pid=' . (int)$targetPid .
			' AND name=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($request['item'], self::TAG)
		);
		if (isset($record['uid'])) {
			$tagUid = $record['uid'];
		} else {
			$tcemainData = array(
				self::TAG => array(
					'NEW' => array(
						'pid' => (int)$targetPid,
						'name' => $request['item']
					)
				)
			);

			/** @var \TYPO3\CMS\Core\DataHandling\DataHandler $tce */
			$tce = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
			$tce->start($tcemainData, array());
			$tce->process_datamap();

			$tagUid = $tce->substNEWwithIDs['NEW'];
		}

		if ($tagUid == 0) {
			throw new \Exception('error_no-tag-created');
		}

		return $tagUid;
	}

}