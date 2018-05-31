<?php

namespace Drupal\nested_content;

use Drupal;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Drupal\nested_content\Entity\NestedContentEntity;
use Drupal\nested_content\Form\NestedContentEntityCollectionForm;

/**
 * Defines a class to build a listing of Nested Content entities.
 *
 * @ingroup nested_content
 */
class NestedContentEntityListBuilder extends EntityListBuilder {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  /**
   * NestedContentEntityListBuilder constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage) {
    parent::__construct($entity_type, $storage);
    $this->db = Database::getConnection();
  }

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

    // Get the nested content entities so we can display them
    // in the tabledrag table.
    $query = $this->db->select('nested_content_field_data', 'ncfd');
    $query->fields('ncfd', ['id', 'weight']);
    $query->join('nested_content_hierarchy', 'nch', 'ncfd.id = nch.id');
    $query->fields('nch', ['id', 'parent']);
    $query->orderBy('ncfd.weight', 'ASC');
    $result = $query->execute()->fetchAll();

    $ids = [];

    foreach ($result as $i => $item) {
      $ids[] = $item->id;
    }

    $entities = NestedContentEntity::loadMultiple($ids);

    $form = new NestedContentEntityCollectionForm($build['table'], $entities);
    $form = Drupal
      ::formBuilder()
      ->getForm($form);

    return $form;
  }

}
