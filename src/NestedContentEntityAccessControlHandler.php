<?php

namespace Drupal\nested_content;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Nested Content entity.
 *
 * @see \Drupal\nested_content\Entity\NestedContentEntity.
 */
class NestedContentEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\nested_content\Entity\NestedContentEntityInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished nested content entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published nested content entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit nested content entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete nested content entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add nested content entities');
  }

}
