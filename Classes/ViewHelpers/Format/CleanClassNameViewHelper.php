<?php
namespace BeechIt\TagItems\ViewHelpers\Format;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 02-09-2015
 * All code (c) Beech Applications B.V. all rights reserved
 */
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * Class CleanClassNameViewHelper
 */
class CleanClassNameViewHelper extends AbstractViewHelper implements CompilableInterface {
	/**
	 * Clean string so it can be used as css class name
	 *
	 * @param string $value
	 * @param string $separator
	 * @return string
	 */
	public function render($value, $separator = '-') {
		return self::renderStatic(
			array(
				'value' => $value,
				'separator' => $separator,
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
		$value = $arguments['value'];
		if (empty($value)) {
			$value = $renderChildrenClosure();
		}
		$separator = $arguments['separator'];

		// Removes accents
		$value = @iconv('UTF-8', 'us-ascii//TRANSLIT', $value);

		// Remove all characters that are not the separator, letters, numbers, or whitespace
		$value = preg_replace('![^' . preg_quote($separator, '!') . '\pL\pN\s]+!u', '', mb_strtolower($value));

		// Replace all separator characters and whitespace by a single separator
		$value = preg_replace('![' . preg_quote($separator, '!') . '\s]+!u', $separator, $value);

		// Trim separators from the beginning and end
		return trim($value, ' ' . $separator);
	}
}