<?php

namespace Drupal\avatars;

use Drupal\avatars\Entity\AvatarCacheInterface;
use Drupal\Core\Entity\FieldableEntityInterface;

/**
 * Handles pushing avatar caches into entities.
 */
interface AvatarKitEntityFieldHandlerInterface {

  /**
   * Copies the avatar in a cache entity to an entity.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   A entity.
   * @param \Drupal\avatars\Entity\AvatarCacheInterface $avatar_cache
   *   An avatar cache entity.
   */
  public function copyCacheToEntity(FieldableEntityInterface $entity, AvatarCacheInterface $avatar_cache): void;

  /**
   * Checks if there are updates to the first avatar for an entity.
   *
   * If an entity doesn't have an entity yet, it may get one. If it is not time
   * to check for updates, then we will not check.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   Check this avatar if it needs a new avatar.
   */
  public function checkUpdates(FieldableEntityInterface $entity): void;

}
