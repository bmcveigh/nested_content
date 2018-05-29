<?php

namespace Drupal\nested_content;

use Drupal;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;
use Drupal\nested_content\Form\NestedContentEntityCollectionForm;

/**
 * Defines a class to build a listing of Nested Content entities.
 *
 * @ingroup nested_content
 */
class NestedContentEntityListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Name');
    $header['weight'] = $this->t('Weight');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\nested_content\Entity\NestedContentEntity */
    $row['data']['name']['data'] = Link::createFromRoute(
      $entity->label(),
      'entity.nested_content.edit_form',
      ['nested_content' => $entity->id()]
    );
    $row['data']['weight']['data'] = 0;
    $row['data']['weight']['class'][] = 'nested-content-weight';

    $parent_row = parent::buildRow($entity);
    $row['data']['operations'] = $parent_row['operations'];

    $row['class'][] = 'draggable';
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $this->getStorage()->getQuery()->sort('weight', 'ASC');
    $entities = $this->load();
    $form = new NestedContentEntityCollectionForm($build['table'], $entities);
    $form = Drupal
      ::formBuilder()
      ->getForm($form);

    return $form;
  }

}
