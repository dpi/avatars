<?php

namespace Drupal\avatars;

use Drupal\Core\Entity\EntityInterface;
use Drupal\avatars\Entity\AvatarCacheInterface;

/**
 * Downloads and caches avatars into entities.
 */
interface AvatarKitEntityHandlerInterface {

  /**
   * Find the first valid avatar for an entity.
   *
   * Downloads and caches avatars for a user until first success.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Get the first avatar for this entity.
   *
   * @return \Drupal\avatars\Entity\AvatarCacheInterface|null
   *   An avatar cache entity, or NULL if no avatars could be generated for this
   *   entity.
   */
  public function findFirst(EntityInterface $entity): ?AvatarCacheInterface;

  /**
   * Iterates through all avatar services for a user.
   *
   * Downloads and caches the avatar locally, then produces an avatar cache
   * for each.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Get the avatars for this entity.
   *
   * @return \Generator|\Drupal\avatars\Entity\AvatarCacheInterface[]
   *   A generator where keys are service plugin ID's and values are avatar
   *   cache entities.
   */
  public function findAll(EntityInterface $entity): \Generator;

  /**
   * Determine whether the service is in read only mode.
   *
   * @return bool
   *   Whether the service is in read only mode.
   */
  public function isReadOnly(): bool;

  /**
   * Set whether the service is in read only mode.
   *
   * @param bool $readOnly
   *   Whether the service is in read only mode.
   */
  public function setReadOnly(bool $readOnly): void;

}
