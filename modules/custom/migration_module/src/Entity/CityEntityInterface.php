<?php

namespace Drupal\migration_module\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Cities List entities.
 *
 * @ingroup migration_module
 */
interface CityEntityInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityPublishedInterface, EntityOwnerInterface {

  /**
   * Add get/set methods for your configuration properties here.
   */

  /**
   * Gets the Cities List name.
   *
   * @return string
   *   Name of the Cities List.
   */
  public function getName();

  /**
   * Sets the Cities List name.
   *
   * @param string $name
   *   The Cities List name.
   *
   * @return \Drupal\migration_module\Entity\CityEntityInterface
   *   The called Cities List entity.
   */
  public function setName($name);

  /**
   * Gets the Cities List creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Cities List.
   */
  public function getCreatedTime();

  /**
   * Sets the Cities List creation timestamp.
   *
   * @param int $timestamp
   *   The Cities List creation timestamp.
   *
   * @return \Drupal\migration_module\Entity\CityEntityInterface
   *   The called Cities List entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the Cities List revision creation timestamp.
   *
   * @return int
   *   The UNIX timestamp of when this revision was created.
   */
  public function getRevisionCreationTime();

  /**
   * Sets the Cities List revision creation timestamp.
   *
   * @param int $timestamp
   *   The UNIX timestamp of when this revision was created.
   *
   * @return \Drupal\migration_module\Entity\CityEntityInterface
   *   The called Cities List entity.
   */
  public function setRevisionCreationTime($timestamp);

  /**
   * Gets the Cities List revision author.
   *
   * @return \Drupal\user\UserInterface
   *   The user entity for the revision author.
   */
  public function getRevisionUser();

  /**
   * Sets the Cities List revision author.
   *
   * @param int $uid
   *   The user ID of the revision author.
   *
   * @return \Drupal\migration_module\Entity\CityEntityInterface
   *   The called Cities List entity.
   */
  public function setRevisionUserId($uid);

}
