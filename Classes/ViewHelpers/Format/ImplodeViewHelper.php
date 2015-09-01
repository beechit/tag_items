<?php
namespace BeechIt\TagItems\ViewHelpers\Format;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * A view helper which joins array elements with a glue string.
 *
 * = Examples =
 *
 * <code>
 *   class="<f:format.implode values="{0: 'className-1', 1: 'className-2'}" />"
 * </code>
 *
 * <output>
 *   class="className-1 className-2"
 * </output>
 *
 * <code>
 *   class="{f:format.implode(values:'{0:\'className-1\', 1:\'className-2\'}')}"
 * </code>
 *
 * <output>
 *   class="className-1 className-2"
 * </output>
 *
 * <code>
 *   class="<f:format.implode values="{0: 'prefix', 1: 'value1', 2: 'value2'}" glue="-" />"
 * </code>
 *
 * <output>
 *   class="prefix-value1-value2"
 * </output>
 */
class ImplodeViewHelper extends AbstractViewHelper implements CompilableInterface {

	/**
	 * Render the view helper
	 *
	 * @param array $values array of elements to join
	 * @param string $glue String used as glue between elements
	 * @param bool $excludeEmptyValues Remove empty elements from $values
	 * @param bool $cleanValueForClassName Clean value to be used as class name
	 * @return string
	 */
	public function render(array $values, $glue = ' ', $excludeEmptyValues = TRUE, $cleanValueForClassName = FALSE) {
		return self::renderStatic(
			array(
				'values' => $values,
				'glue' => $glue,
				'excludeEmptyValues' => $excludeEmptyValues,
				'cleanValueForClassName' => $cleanValueForClassName
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
		$values = (array)$arguments['values'];
		$glue = $arguments['glue'];
		$excludeEmptyValues = (bool)$arguments['excludeEmptyValues'];
		$cleanValueForClassName = (bool)$arguments['cleanValueForClassName'];
		$string = '';

		if ($excludeEmptyValues) {
			$values = array_filter($values);
		}
		if ($cleanValueForClassName) {
			foreach ($values as $key => $value) {
				$values[$key] = self::cleanValueForClassName($value);
			}
		}
		if (!empty($values)) {
			$string = implode($glue, $values);
		}
		return $string;
	}

	/**
	 * Clean value so it can be used a css classname
	 *
	 * @param string $value
	 * @param string $fallBackValue
	 * @param string $separator
	 * @return string
	 */
	static protected function cleanValueForClassName($value, $fallBackValue = '', $separator = '-') {

		// Removes accents
		$value = @iconv('UTF-8', 'us-ascii//TRANSLIT', $value ?: $fallBackValue);

		// Remove all characters that are not the separator, letters, numbers, or whitespace
		$value = preg_replace('![^' . preg_quote($separator, '!') . '\pL\pN\s]+!u', '', mb_strtolower($value));

		// Replace all separator characters and whitespace by a single separator
		$value = preg_replace('![' . preg_quote($separator, '!') . '\s]+!u', $separator, $value);

		// Trim separators from the beginning and end
		return trim($value, ' ' . $separator);
	}
}