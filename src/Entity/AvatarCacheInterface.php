<?php

namespace Drupal\avatars\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\file\FileInterface;

/**
 * Provides an interface for Avatar cache entities.
 */
interface AvatarCacheInterface extends ContentEntityInterface {

  /**
   * Get the avatar service plugin entity.
   */
  public function getAvatarService(): ?AvatarKitServiceInterface;

  /**
   * Get the avatar service plugin ID.
   *
   * @return string
   *   An avatar service plugin ID.
   */
  public function getAvatarServiceId(): string;

  /**
   * Get the avatar file.
   *
   * @return \Drupal\file\FileInterface|null
   *   The file entity, or NULL if it does not exist.
   */
  public function getAvatar(): ?FileInterface;

  /**
   * Set the avatar file.
   *
   * @param \Drupal\file\FileInterface|null $entity
   *   A file entity, or NULL.
   *
   * @return $this
   *   Return this object for chaining.
   */
  public function setAvatar(?FileInterface $entity): self;

  /**
   * Get the identifier used to generate the avatar.
   *
   * @return string
   *   The identifier used to generate the avatar.
   */
  public function getIdentifier(): string;

  /**
   * Gets the time avatar was checked
   *
   * @return int
   *   The time avatar was checked
   */
  public function getLastCheckTime();

  /**
   * Mark the avatar as checked at current time.
   *
   * @return $this
   *   Return this object for chaining.
   */
  public function markChecked();

}
