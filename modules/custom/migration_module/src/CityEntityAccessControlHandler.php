<?php

namespace Drupal\migration_module;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Cities List entity.
 *
 * @see \Drupal\migration_module\Entity\CityEntity.
 */
class CityEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\migration_module\Entity\CityEntityInterface $entity */

    switch ($operation) {

      case 'view':

        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished cities list entities');
        }


        return AccessResult::allowedIfHasPermission($account, 'view published cities list entities');

      case 'update':

        return AccessResult::allowedIfHasPermission($account, 'edit cities list entities');

      case 'delete':

        return AccessResult::allowedIfHasPermission($account, 'delete cities list entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add cities list entities');
  }


}
