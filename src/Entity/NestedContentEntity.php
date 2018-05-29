<?php

namespace Drupal\nested_content\Entity;

use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\RevisionableContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Nested Content entity.
 *
 * @ingroup nested_content
 *
 * @ContentEntityType(
 *   id = "nested_content",
 *   label = @Translation("Nested Content"),
 *   bundle_label = @Translation("Nested Content type"),
 *   handlers = {
 *     "storage" = "Drupal\nested_content\NestedContentEntityStorage",
 *     "storage_schema" = "Drupal\nested_content\NestedContentStorageSchema",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\nested_content\NestedContentEntityListBuilder",
 *     "views_data" = "Drupal\nested_content\Entity\NestedContentEntityViewsData",
 *     "translation" = "Drupal\nested_content\NestedContentEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\nested_content\Form\NestedContentEntityForm",
 *       "add" = "Drupal\nested_content\Form\NestedContentEntityForm",
 *       "edit" = "Drupal\nested_content\Form\NestedContentEntityForm",
 *       "delete" = "Drupal\nested_content\Form\NestedContentEntityDeleteForm",
 *     },
 *     "access" = "Drupal\nested_content\NestedContentEntityAccessControlHandler",
 *     "route_provider" = {
 *       "html" = "Drupal\nested_content\NestedContentEntityHtmlRouteProvider",
 *     },
 *   },
 *   base_table = "nested_content",
 *   data_table = "nested_content_field_data",
 *   revision_table = "nested_content_revision",
 *   revision_data_table = "nested_content_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer nested content entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "status" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/content/nested_content/{nested_content}",
 *     "add-page" = "/admin/content/nested_content/add",
 *     "add-form" = "/admin/content/nested_content/add/{nested_content_type}",
 *     "edit-form" = "/admin/content/nested_content/{nested_content}/edit",
 *     "delete-form" = "/admin/content/nested_content/{nested_content}/delete",
 *     "version-history" = "/admin/content/nested_content/{nested_content}/revisions",
 *     "revision" = "/admin/content/nested_content/{nested_content}/revisions/{nested_content_revision}/view",
 *     "revision_revert" = "/admin/content/nested_content/{nested_content}/revisions/{nested_content_revision}/revert",
 *     "revision_delete" = "/admin/content/nested_content/{nested_content}/revisions/{nested_content_revision}/delete",
 *     "translation_revert" = "/admin/content/nested_content/{nested_content}/revisions/{nested_content_revision}/revert/{langcode}",
 *     "collection" = "/admin/content/nested_content",
 *   },
 *   bundle_entity_type = "nested_content_type",
 *   field_ui_base_route = "entity.nested_content_type.edit_form"
 * )
 */
class NestedContentEntity extends RevisionableContentEntityBase implements NestedContentEntityInterface {

  use EntityChangedTrait;

  private $weight;

  /**
   * NestedContentEntity constructor.
   */
  public function __construct() {
    if ($id = $this->id()) {
      $this->weight = Database::getConnection()
        ->select('nested_content_field_data', 'ncfd')
        ->fields('ncfd', ['weight'])
        ->condition('id', $id)
        ->execute()
        ->fetchField();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly, make the nested_content owner the
    // revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isPublished() {
    return (bool) $this->getEntityKey('status');
  }

  /**
   * {@inheritdoc}
   */
  public function setPublished($published) {
    $this->set('status', $published ? TRUE : FALSE);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Nested Content entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Nested Content entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Publishing status'))
      ->setDescription(t('A boolean indicating whether the Nested Content is published.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
