<?php

namespace Drupal\nested_content\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Nested Content entities.
 */
class NestedContentEntityViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    // Additional information for Views integration, such as table joins, can be
    // put here.

    return $data;
  }

}
