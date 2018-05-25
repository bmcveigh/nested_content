<?php

namespace Drupal\nested_content\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class NestedContentEntityTypeForm.
 */
class NestedContentEntityTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $nested_content_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $nested_content_type->label(),
      '#description' => $this->t("Label for the Nested Content type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $nested_content_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\nested_content\Entity\NestedContentEntityType::load',
      ],
      '#disabled' => !$nested_content_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $nested_content_type = $this->entity;
    $status = $nested_content_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Nested Content type.', [
          '%label' => $nested_content_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Nested Content type.', [
          '%label' => $nested_content_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($nested_content_type->toUrl('collection'));
  }

}
