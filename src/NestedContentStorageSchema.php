<?php

namespace Drupal\nested_content;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;
use Drupal\Core\Field\FieldStorageDefinitionInterface;

/**
 * Defines the term schema handler.
 */
class NestedContentStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset = FALSE);

    $schema['nested_content_field_data']['fields']['weight'] = [
      'type' => 'int',
      'not null' => TRUE,
      'default' => 0,
      'description' => 'The weight of the nested_content entity.',
    ];

    $schema['nested_content_field_data']['indexes'] += [
      'nested_content__tree' => ['weight'],
    ];

    $schema['nested_content_hierarchy'] = [
      'description' => 'Stores the hierarchical relationship between nested content items.',
      'fields' => [
        'id' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => 'Primary Key: The {nested_content}.id of the nested_content.',
        ],
        'parent' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => "Primary Key: The {nested_content}.id of the nested_content's parent. 0 indicates no parent.",
        ],
        'weight' => [
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 0,
          'description' => "The weight of the nested_content entity.",
        ],
      ],
      'indexes' => [
        'parent' => ['parent'],
      ],
      'foreign keys' => [
        'nested_content' => [
          'table' => 'nested_content',
          'columns' => ['id' => 'id'],
        ],
      ],
      'primary key' => ['id', 'parent'],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSharedTableFieldSchema(FieldStorageDefinitionInterface $storage_definition, $table_name, array $column_mapping) {
    $schema = parent::getSharedTableFieldSchema($storage_definition, $table_name, $column_mapping);
    $field_name = $storage_definition->getName();

    if ($table_name == 'nested_content_field_data') {
      // Remove unneeded indexes.
      unset($schema['indexes']['nested_content_field__vid__target_id']);
      unset($schema['indexes']['nested_content_field__description__format']);

      switch ($field_name) {
        case 'weight':
          // Improves the performance of the nested_content__tree index defined
          // in getEntitySchema().
          $schema['fields'][$field_name]['not null'] = TRUE;
          break;

        case 'name':
          $this->addSharedTableFieldIndex($storage_definition, $schema, TRUE);
          break;
      }
    }

    return $schema;
  }

}
