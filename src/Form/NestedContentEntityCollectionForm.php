<?php

namespace Drupal\nested_content\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;

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

    foreach ($table['#rows'] as $i => $row) {
      $indentation = [
        '#theme' => 'indentation',
        //          '#size' => $entity->depth,
        '#size' => 1,
      ];
      $link = $table['#rows'][$i]['data']['name']['data'];
      if ($link instanceof Link) {
        $title = [
          'indentation' => $indentation,
          'title' => $link->toRenderable(),
        ];
        $table['#rows'][$i]['data']['name'] = \Drupal::service('renderer')->render($title);
        $table['#rows'][$i]['class'][] = 'nested-content-parent';
      }
    }

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
  }

}
