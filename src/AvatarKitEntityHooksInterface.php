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

}