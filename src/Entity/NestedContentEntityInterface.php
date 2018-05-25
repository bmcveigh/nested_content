<?php

namespace Drupal\nested_content\Entity;

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Nested Content entities.
 *
 * @ingroup nested_content
 */
interface NestedContentEntityInterface extends RevisionableInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Nested Content name.
   *
   * @return string
   *   Name of the Nested Content.
   */
  public function getName();

  /**
   * Sets the Nested Content name.
   *
   * @param string $name
   *   The Nested Content name.
   *
   * @return \Drupal\nested_content\Entity\NestedContentEntityInterface
   *   The called Nested Content entity.
   */
  public function setName($name);

  /**
   * Gets the Nested Content creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Nested Content.
   */
  public function getCreatedTime();

  /**
   * Sets the Nested Content creation timestamp.
   *
   * @param int $timestamp
   *   The Nested Content creation timestamp.
   *
   * @return \Drupal\nested_content\Entity\NestedContentEntityInterface
   *   The called Nested Content entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Nested Content published status indicator.
   *
   * Unpublished Nested Content are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Nested Content is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Nested Content.
   *
   * @param bool $published
   *   TRUE to set this Nested Content to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\nested_content\Entity\NestedContentEntityInterface
   *   The called Nested Content entity.
   */
  public function setPublished($published);

  /**
   * Gets the Nested Content revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Nested Content revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\nested_content\Entity\NestedContentEntityInterface
   *   The called Nested Content entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Nested Content revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Nested Content revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\nested_content\Entity\NestedContentEntityInterface
   *   The called Nested Content entity.
   */
  public function setRevisionUserId($uid);

}
