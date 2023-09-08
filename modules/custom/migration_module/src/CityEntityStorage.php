<?php

namespace Drupal\migration_module;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
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
class CityEntityStorage extends SqlContentEntityStorage implements CityEntityStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function revisionIds(CityEntityInterface $entity) {
    return $this->database->query(
      'SELECT vid FROM {city_entity_revision} WHERE id=:id ORDER BY vid',
      [':id' => $entity->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function userRevisionIds(AccountInterface $account) {
    return $this->database->query(
      'SELECT vid FROM {city_entity_field_revision} WHERE uid = :uid ORDER BY vid',
      [':uid' => $account->id()]
    )->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function countDefaultLanguageRevisions(CityEntityInterface $entity) {
    return $this->database->query('SELECT COUNT(*) FROM {city_entity_field_revision} WHERE id = :id AND default_langcode = 1', [':id' => $entity->id()])
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function clearRevisionsLanguage(LanguageInterface $language) {
    return $this->database->update('city_entity_revision')
      ->fields(['langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED])
      ->condition('langcode', $language->getId())
      ->execute();
  }

}
