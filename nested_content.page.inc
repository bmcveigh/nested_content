<?php

/**
 * @file
 * Contains nested_content.page.inc.
 *
 * Page callback for Nested Content entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Nested Content templates.
 *
 * Default template: nested_content.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_nested_content(array &$variables) {
  // Fetch NestedContentEntity Entity Object.
  $nested_content = $variables['elements']['#nested_content'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
