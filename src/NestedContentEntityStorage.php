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
   * Array of loaded parents keyed by child nested_content ID.
   *
   * @var array
   */
  protected $parents = [];

  /**
   * Array of all loaded nested_content ancestry keyed by ancestor nested_content ID.
   *
   * @var array
   */
  protected $parentsAll = [];

  /**
   * {@inheritdoc}
   */
  public function loadParents($id) {
    if (!isset($this->parents[$id])) {
      $parents = [];
      $query = $this->database->select('nested_content_field_data', 'n');
      $query->join('nested_content_hierarchy', 'h', 'h.parent = n.id');
      $query->addField('n', 'id');
      $query->condition('h.id', $id);
      $query->orderBy('n.weight');
      $query->orderBy('n.name');
      if ($ids = $query->execute()->fetchCol()) {
        $parents = $this->loadMultiple($ids);
      }
      $this->parents[$id] = $parents;
    }
    return $this->parents[$id];
  }

  /**
   * {@inheritdoc}
   */
  public function loadAllParents($id) {
    if (!isset($this->parentsAll[$id])) {
      $parents = [];
      if ($term = $this->load($id)) {
        $parents[$term->id()] = $term;
        $terms_to_search[] = $term->id();

        while ($id = array_shift($terms_to_search)) {
          if ($new_parents = $this->loadParents($id)) {
            foreach ($new_parents as $new_parent) {
              if (!isset($parents[$new_parent->id()])) {
                $parents[$new_parent->id()] = $new_parent;
                $terms_to_search[] = $new_parent->id();
              }
            }
          }
        }
      }

      $this->parentsAll[$id] = $parents;
    }
    return $this->parentsAll[$id];
  }

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
