<?php

namespace Drupal\avatars\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\file\FileInterface;

/**
 * Provides an interface for Avatar cache entities.
 */
interface AvatarCacheInterface extends ContentEntityInterface {

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

}
