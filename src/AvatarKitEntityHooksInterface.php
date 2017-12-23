<?php

namespace Drupal\avatars;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface for Avatar Kit Drupal entity hooks.
 */
interface AvatarKitEntityHooksInterface {

  /**
   * Implements hook_entity_update().
   *
   * @see hook_entity_update()
   */
  public function update(EntityInterface $entity): void;

  /**
   * Implements hook_entity_storage_load().
   *
   * @see \hook_entity_storage_load()
   */
  public function storageLoad(array $entities): void;

}
