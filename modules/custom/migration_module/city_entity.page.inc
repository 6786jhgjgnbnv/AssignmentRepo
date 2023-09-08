<?php

/**
 * @file
 * Contains city_entity.page.inc.
 *
 * Page callback for Cities List entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Cities List templates.
 *
 * Default template: city_entity.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_city_entity(array &$variables) {
  // Fetch CityEntity Entity Object.
  $city_entity = $variables['elements']['#city_entity'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
