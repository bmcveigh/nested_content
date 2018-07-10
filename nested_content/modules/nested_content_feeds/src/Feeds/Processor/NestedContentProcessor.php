<?php

namespace Drupal\nested_content_feeds\Feeds\Processor;

use Drupal\feeds\Feeds\Processor\EntityProcessorBase;

/**
 * Defines a "Nested Content" processor.
 *
 * Creates nested content from feed items.
 *
 * @FeedsProcessor(
 *   id = "entity:nested_content",
 *   title = @Translation("Nested Content"),
 *   description = @Translation("Creates nested content entities from feed items."),
 *   entity_type = "nested_content",
 *   arguments = {"@entity_type.manager", "@entity.query", "@entity_type.bundle.info"},
 *   form = {
 *     "configuration" = "Drupal\feeds\Feeds\Processor\Form\DefaultEntityProcessorForm",
 *     "option" = "Drupal\feeds\Feeds\Processor\Form\EntityProcessorOptionForm",
 *   },
 * )
 */
class NestedContentProcessor extends EntityProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function entityLabel() {
    return $this->t('Nested Content');
  }

  /**
   * {@inheritdoc}
   */
  public function entityLabelPlural() {
    return $this->t('Nested Contents');
  }

}
