<?php

namespace Drupal\nested_content\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Nested Content type entity.
 *
 * @ConfigEntityType(
 *   id = "nested_content_type",
 *   label = @Translation("Nested Content type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\nested_content\NestedContentEntityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\nested_content\Form\NestedContentEntityTypeForm",
 *       "edit" = "Drupal\nested_content\Form\NestedContentEntityTypeForm",
 *       "delete" = "Drupal\nested_content\Form\NestedContentEntityTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\nested_content\NestedContentEntityTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "nested_content_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "nested_content",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/nested_content_type/{nested_content_type}",
 *     "add-form" = "/admin/structure/nested_content_type/add",
 *     "edit-form" = "/admin/structure/nested_content_type/{nested_content_type}/edit",
 *     "delete-form" = "/admin/structure/nested_content_type/{nested_content_type}/delete",
 *     "collection" = "/admin/structure/nested_content_type"
 *   }
 * )
 */
class NestedContentEntityType extends ConfigEntityBundleBase implements NestedContentEntityTypeInterface {

  /**
   * The Nested Content type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Nested Content type label.
   *
   * @var string
   */
  protected $label;

}
