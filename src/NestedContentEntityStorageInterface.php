<?php

namespace Drupal\nested_content;

use Drupal\Core\Entity\ContentEntityStorageInterface;
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
interface NestedContentEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Nested Content revision IDs for a specific Nested Content.
   *
   * @param \Drupal\nested_content\Entity\NestedContentEntityInterface $entity
   *   The Nested Content entity.
   *
   * @return int[]
   *   Nested Content revision IDs (in ascending order).
   */
  public function revisionIds(NestedContentEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Nested Content author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Nested Content revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\nested_content\Entity\NestedContentEntityInterface $entity
   *   The Nested Content entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(NestedContentEntityInterface $entity);

  /**
   * Unsets the language for all Nested Content with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
