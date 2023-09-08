<?php

namespace Drupal\migration_module;

use Drupal\Core\Entity\ContentEntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\migration_module\Entity\CityEntityInterface;

/**
 * Defines the storage handler class for Cities List entities.
 *
 * This extends the base storage class, adding required special handling for
 * Cities List entities.
 *
 * @ingroup migration_module
 */
interface CityEntityStorageInterface extends ContentEntityStorageInterface {

  /**
   * Gets a list of Cities List revision IDs for a specific Cities List.
   *
   * @param \Drupal\migration_module\Entity\CityEntityInterface $entity
   *   The Cities List entity.
   *
   * @return int[]
   *   Cities List revision IDs (in ascending order).
   */
  public function revisionIds(CityEntityInterface $entity);

  /**
   * Gets a list of revision IDs having a given user as Cities List author.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user entity.
   *
   * @return int[]
   *   Cities List revision IDs (in ascending order).
   */
  public function userRevisionIds(AccountInterface $account);

  /**
   * Counts the number of revisions in the default language.
   *
   * @param \Drupal\migration_module\Entity\CityEntityInterface $entity
   *   The Cities List entity.
   *
   * @return int
   *   The number of revisions in the default language.
   */
  public function countDefaultLanguageRevisions(CityEntityInterface $entity);

  /**
   * Unsets the language for all Cities List with the given language.
   *
   * @param \Drupal\Core\Language\LanguageInterface $language
   *   The language object.
   */
  public function clearRevisionsLanguage(LanguageInterface $language);

}
