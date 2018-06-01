<?php

namespace Drupal\nested_content\Form;

use Drupal;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\nested_content\Entity\NestedContentEntity;
use Drupal\nested_content\NestedContentEntityStorage;

/**
 * Class NestedContentEntityCollectionForm.
 */
class NestedContentEntityCollectionForm extends FormBase {

  /**
   * A render array representing the tabledrag
   * table items.
   *
   * @var array
   */
  private $renderedTable;

  /**
   * @var \Drupal\nested_content\Entity\NestedContentEntity[]
   */
  private $entities;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Database
   */
  protected $db;

  /**
   * NestedContentEntityCollectionForm constructor.
   *
   * @param array $renderedTable
   */
  public function __construct($renderedTable) {
    $this->db = Database::getConnection();
    $this->renderedTable = $renderedTable;
    $this->loadEntities();
  }

  protected function loadEntities() {
    if (empty($this->entities)) {
      // Get the nested content entities so we can display them
      // in the tabledrag table.
      $query = $this->db->select('nested_content_field_data', 'ncfd');
      $query->fields('ncfd', ['id', 'weight']);
      $query->join('nested_content_hierarchy', 'nch', 'ncfd.id = nch.id');
      $query->fields('nch', ['id', 'parent']);
      $query->orderBy('ncfd.weight', 'ASC');
      $result = $query->execute()->fetchAll();

      $ids = [];

      $weight = 0;
      foreach ($result as $i => $item) {
        $count_query = $this->db->select('nested_content_hierarchy', 'nch');
        $count_query->fields('nch');
        $count_query->countQuery();
        $count_query->condition('nch.parent', $item->id);

        $count_result = $count_query->execute()->fetchField();

        $weight += $count_result ? $i : $i + 1;

        do {
          if (!isset($ids[$weight])) {
            $ids[$weight] = $item->id;
            break;
          }
          $weight++;
        } while (isset($ids[$weight]));
        $ids[$weight] = $item->id;
      }
      ksort($ids);

      $this->entities = NestedContentEntity::loadMultiple($ids);

      $entity_type_manager = Drupal::entityTypeManager();
      $storage = $entity_type_manager->getStorage('nested_content');

      // Get the depth that each entity should be displayed.
      foreach ($this->entities as $entity) {
        $parents = [];
        if ($storage instanceof NestedContentEntityStorage) {
          $parents = $storage->loadAllParents($entity->id());
        }

        $num_parents = count($parents);
        $depth = $num_parents ? $num_parents - 1 : 0;
        $entity->setDepth($depth);
      }
    }
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

      // Get the depth of the entity so we can show indentation
      // as well as ensuring the table is sorted in the correct
      // order.
      $depth = $entity->getDepth();

      // Indentation for the tabledrag table.
      $indentation = [
        '#theme' => 'indentation',
        '#size' => $depth,
      ];

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
      $bundle_label = $entity->getBundleLabel();

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

    $form['#attached']['library'][] = 'nested_content/nested_content.tabledrag';

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
