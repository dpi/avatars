<?php

namespace Drupal\avatars;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;

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
    $bin = static::class . '::' . __FUNCTION__;
    foreach ($entities as $entity) {
      if (!$entity instanceof FieldableEntityInterface) {
        // If this entity is not fieldable then none of this type are.
        return;
      }
      if ($entity->isNew()) {
        // Don't deal with unsaved or skeleton entities.
        continue;
      }

      $values = [$entity];
      static::preventRecursion(
        $bin,
        function (FieldableEntityInterface $entity): string {
          return $entity->getEntityTypeId() . ':' . $entity->id();
        },
        function (FieldableEntityInterface $entity): void {
          $this->entityFieldHandler()->checkUpdates($entity);
        },
        $values
      );
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

  /**
   * Wrapper utility to prevent recursion.
   *
   * @param string $bin
   *   A key to prevent collisions with any other active callers of this
   *   utility.
   * @param callable $keyGen
   *   A callable which will take $args, and return a unique string key.
   * @param callable $recur
   *   A callable which will take $args, which will run if it is already not
   *   already being executed further up the calling stack. This method returns
   *   void.
   * @param array $args
   *   An arbitrary array of values to pass to $keyGen and $recur callables.
   */
  public static function preventRecursion(string $bin, callable $keyGen, callable $recur, array $args): void {
    static $static = [];

    if (!isset($static[$bin])) {
      $static[$bin] = [];
    }

    $key = $keyGen(...$args);
    if (!in_array($key, $static[$bin])) {
      $static[$bin][$key] = TRUE;
      $recur(...$args);
      unset($static[$bin][$key]);
    }
  }

}
