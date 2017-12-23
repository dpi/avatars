<?php

namespace Drupal\avatars;

use Drupal\Core\Entity\EntityInterface;

/**
 * Drupal entity hooks.
 */
class AvatarKitEntityHooks implements AvatarKitEntityHooksInterface {

  /**
   * {@inheritdoc}
   */
  public function update(EntityInterface $entity): void {
    /** @var \Drupal\avatars\AvatarKitLocalCacheInterface $localCache */
    $localCache = \Drupal::service('avatars.local_cache');
    $localCache->invalidateCaches($entity);
  }

}