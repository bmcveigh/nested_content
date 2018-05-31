<?php

namespace Drupal\nested_content\Form;

use Drupal;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\nested_content\NestedContentEntityStorage;

/**
 * Class NestedContentEntityCollectionForm.
 */
class NestedContentEntityCollectionForm extends FormBase {

  private $renderedTable;

  /**
   * @var \Drupal\nested_content\Entity\NestedContentEntity[]
   */
  private $entities;

  /**
   * NestedContentEntityCollectionForm constructor.
   *
   * @param $renderedTable
   * @param \Drupal\nested_content\Entity\NestedContentEntity[] $entities
   */
  public function __construct($renderedTable, array $entities) {
    $this->renderedTable = $renderedTable;
    $this->entities = $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nested_content_entity_collection_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['table'] = $this->renderedTable;

    unset($form['table']['#theme']);
    $table = &$form['table'];

    $table['#type'] = 'table';
    $table['#id'] = 'nested-content-table';
    $table['#rows'] = [];

    foreach ($this->entities as $key => $entity) {
      /** @var $entity \Drupal\Core\Entity\EntityInterface */
      $form['table'][$key]['#nested_content'] = $entity;
      $indentation = [];

      $entity_type_manager = Drupal::entityTypeManager();
      $storage = $entity_type_manager->getStorage('nested_content');
      $parents = [];
      if ($storage instanceof NestedContentEntityStorage) {
        $parents = $storage->loadAllParents($entity->id());
      }
      $depth = count($parents) - 1;

      if (!empty($parents)) {
        $indentation = [
          '#theme' => 'indentation',
          '#size' => $depth,
        ];
      }
      $form['table'][$key]['nested_content'] = [
        '#prefix' => !empty($indentation) ? \Drupal::service('renderer')
          ->render($indentation) : '',
        '#type' => 'link',
        '#title' => $entity->getName(),
        '#url' => $entity->urlInfo(),
      ];

      // Get the bundle label and display it in the tabledrag
      // since each item may be a different entity type.
      // todo: make sure this is optimized.
      $bundle_label = $entity_type_manager
        ->getStorage('nested_content_type')
        ->load($entity->bundle())
        ->label();

      $form['table'][$key]['nested_content_type'] = [
        '#type' => 'html_tag',
        '#tag' => 'span',
        '#value' => $bundle_label,
      ];

      $parent_fields = TRUE;
      $form['table'][$key]['nested_content']['id'] = [
        '#type' => 'hidden',
        '#value' => $entity->id(),
        '#attributes' => [
          'class' => ['nested-content-id'],
        ],
      ];

      // Get the parent id so we can display it properly in the tabledrag.
      $parent_id = Database::getConnection()
        ->select('nested_content_hierarchy', 'n')
        ->fields('n', ['parent'])
        ->condition('id', $entity->id())
        ->execute()
        ->fetchField();

      $form['table'][$key]['nested_content']['parent'] = [
        '#type' => 'hidden',
        // Yes, default_value on a hidden. It needs to be changeable by the
        // javascript.
        '#default_value' => $parent_id,
        '#attributes' => [
          'class' => ['nested-content-parent'],
        ],
      ];
      $form['table'][$key]['nested_content']['depth'] = [
        '#type' => 'hidden',
        // Same as above, the depth is modified by javascript, so it's a
        // default_value.
        '#default_value' => $depth,
        '#attributes' => [
          'class' => ['nested-content-depth'],
        ],
      ];

      $form['table'][$key]['weight'] = [
        '#type' => 'weight',
        '#title' => $this->t('Weight for added nested_content'),
        '#title_display' => 'invisible',
        '#default_value' => $entity->getWeight(),
        '#attributes' => ['class' => ['nested-content-weight']],
      ];

      $form['table'][$key]['#attributes']['class'] = [];
      if ($parent_fields) {
        $form['table'][$key]['#attributes']['class'][] = 'draggable';
      }
    }

    $form['table']['#header'] = [$this->t('Name')];
    $form['table']['#header'][] = $this->t('Nested Content Type');

    $form['table']['#header'][] = $this->t('Weight');
    if ($parent_fields) {
      $form['table']['#tabledrag'][] = [
        'action' => 'match',
        'relationship' => 'parent',
        'group' => 'nested-content-parent',
        'subgroup' => 'nested-content-parent',
        'source' => 'nested-content-id',
        'hidden' => FALSE,
      ];
      $form['table']['#tabledrag'][] = [
        'action' => 'depth',
        'relationship' => 'group',
        'group' => 'nested-content-depth',
        'hidden' => FALSE,
      ];
    }
    $form['table']['#tabledrag'][] = [
      'action' => 'order',
      'relationship' => 'sibling',
      'group' => 'nested-content-weight',
    ];

    $form['#tabledrag'][] = [
      'action' => 'match',
      'relationship' => 'parent',
      'group' => 'nested-content-parent',
      'subgroup' => 'nested-content-parent',
      'source' => 'nested-content-table',
      'hidden' => FALSE,
    ];
    $table['#tabledrag'][] = [
      'action' => 'order',
      'relationship' => 'sibling',
      'group' => 'nested-content-weight',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $table_data = $values['table'];

    foreach ($table_data as $row) {
      $content = $row['nested_content'];

      $id = @$content['id'];
      $parent = @$content['parent'];
      $weight = @$row['weight'];
      $weight = is_numeric($weight) ? $weight : 0;

      $db = Database::getConnection();
      $select = $db->select('nested_content_hierarchy', 'nch');
      $select->fields('nch', ['id']);
      $select->condition('id', $id);
      $results = $select->execute()->fetchAll();
      $parent_id = empty($parent) ? 0 : $parent;

      if (empty($results)) {
        $db->insert('nested_content_hierarchy')
          ->fields([
            'id' => $id,
            'parent' => $parent_id,
          ])
          ->execute();
      }
      else {
        $db->update('nested_content_hierarchy')
          ->fields([
            'parent' => $parent_id,
          ])
          ->condition('id', $id)
          ->execute();
      }

      $db->update('nested_content_field_data')
        ->fields([
          'weight' => $weight,
        ])
        ->condition('id', $id)
        ->execute();
    }
  }

}
