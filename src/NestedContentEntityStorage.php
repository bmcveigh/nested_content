<?php

namespace Drupal\nested_content;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\nested_content\Entity\NestedContentEntityInterface;

/**
 * Defines the storage handler class for Nested Content entities.
 *
 * This extends the base storage class, adding required special handling for
 * Nested Content entities.
 *
 * @ingroup nested_content
 */
class NestedContentEntityStorage extends SqlContentEntityStorage implements NestedContentEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(NestedContentEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {nested_content_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {nested_content_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(NestedContentEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {nested_content_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('nested_content_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
