<?php
namespace BeechIt\TagItems\ViewHelpers\File;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 01-09-2015
 * All code (c) Beech Applications B.V. all rights reserved
 */
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Extbase\Domain\Model\AbstractFileFolder;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * Class TagsViewHelper
 */
class TagsViewHelper extends AbstractViewHelper implements CompilableInterface {

	/**
	 * Get array of tags
	 *
	 * @param FileInterface|AbstractFileFolder $file
	 * @param string $field
	 * @return string
	 */
	public function render($file, $field = 'tags') {
		if (!is_object($file)) {
			return ['no-file-given'];
		}
		if (is_callable(array($file, 'getOriginalResource'))) {
			// Get the original file from the extbase object
			$file = $file->getOriginalResource();
		}
		if (is_callable(array($file, 'getOriginalFile'))) {
			// Get the original file from the file reference
			$file = $file->getOriginalFile();
		}
		return static::renderStatic(
			array(
				'file' => $file,
				'field' => $field
			),
			$this->buildRenderChildrenClosure(),
			$this->renderingContext
		);
	}

	/**
	 * @param array $arguments
	 * @param \Closure $renderChildrenClosure
	 * @param RenderingContextInterface $renderingContext
	 * @return string
	 */
	static public function renderStatic(array $arguments, \Closure $renderChildrenClosure, RenderingContextInterface $renderingContext) {
		/** @var File $file */
		$file = $arguments['file'];
		$field = $arguments['field'];
		$tags = [];
		self::getDatabaseConnection()->store_lastBuiltQuery = 1;
		$resource = self::getDatabaseConnection()->exec_SELECT_mm_query(
			'tx_tagitems_domain_model_tag.name',
			'tx_tagitems_domain_model_tag',
			'tx_tagitems_domain_model_tag_mm',
			'sys_file_metadata',
			'AND sys_file_metadata.file = ' . (int)$file->getUid() . ' AND tx_tagitems_domain_model_tag_mm.tablenames = \'sys_file_metadata\' AND tx_tagitems_domain_model_tag_mm.fieldname = \'' . htmlspecialchars($field) . '\''
		);
//DebuggerUtility::var_dump(self::getDatabaseConnection()->debug_lastBuiltQuery);
		if ($resource) {
			while ($record = self::getDatabaseConnection()->sql_fetch_assoc($resource)) {
				$tags[] = $record['name'];
			}
			self::getDatabaseConnection()->sql_free_result($resource);
		}

		return $tags;
	}

	/**
	 * @return DatabaseConnection
	 */
	static protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}
}