<?php

namespace Drupal\avatars;

use Drupal\Core\Entity\EntityInterface;

/**
 * Drupal entity hooks.
 */
class AvatarKitEntityHooks implements AvatarKitEntityHooksInterface {

  /**
   * The entity field handler.
   *
   * @var \Drupal\avatars\AvatarKitEntityFieldHandler|null
   * @see \Drupal\avatars\AvatarKitEntityHooks::entityFieldHandler()
   */
  protected $entityFieldHandler;

  /**
   * {@inheritdoc}
   */
  public function update(EntityInterface $entity): void {
    /** @var \Drupal\avatars\AvatarKitLocalCacheInterface $localCache */
    $localCache = \Drupal::service('avatars.local_cache');
    $localCache->invalidateCaches($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function storageLoad(array $entities): void {
    // Store loaded entities to prevent recursion.
    $static = &\drupal_static(static::class . ':' . __FUNCTION__, []);

    foreach ($entities as $entity) {
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $entity_key = $entity->getEntityTypeId() . ':' . $entity->id();
      if (in_array($entity_key, $static)) {
        continue;
      }
      $static[$entity_key] = $entity_key;
      $this->entityFieldHandler()->checkUpdates($entity);
      unset($static[$entity_key]);
    }
  }

  /**
   * Get the entity field handler service.
   *
   * @return \Drupal\avatars\AvatarKitEntityFieldHandlerInterface
   *   The entity field handler service.
   */
  protected function entityFieldHandler(): AvatarKitEntityFieldHandlerInterface {
    if (!$this->entityFieldHandler) {
      $this->entityFieldHandler = \Drupal::service('avatars.entity.field_handler');
    }
    return $this->entityFieldHandler;
  }

}
