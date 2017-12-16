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

}
