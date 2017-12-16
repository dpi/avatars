<?php

namespace Drupal\avatars;

use Drupal\avatars\Entity\AvatarCacheInterface;

/**
 * Cache remote files locally into file entities.
 */
interface AvatarKitLocalCacheInterface {

  /**
   * Determines if a avatar cache exists.
   *
   * @param string $service_id
   *   An avatar service ID.
   * @param \Drupal\avatars\EntityAvatarIdentifierInterface $identifier
   *   An entity avatar identifier.
   *
   * @return \Drupal\avatars\Entity\AvatarCacheInterface|null
   *   An avatar cache entity, or NULL if it does not exist given parameters.
   */
  public function getLocalCache(string $service_id, EntityAvatarIdentifierInterface $identifier): ?AvatarCacheInterface;

  /**
   * Download and save the file to an avatar cache entity.
   *
   * @param string $service_id
   *   An avatar service ID.
   * @param string $uri
   *   The URL of an avatar to download.
   * @param \Drupal\avatars\EntityAvatarIdentifierInterface $identifier
   *   An entity avatar identifier.
   *
   * @return \Drupal\avatars\Entity\AvatarCacheInterface|null
   *   An avatar cache entity. If an avatar could not be downloaded, an empty
   *   file is cached.
   */
  public function localCache(string $service_id, string $uri, EntityAvatarIdentifierInterface $identifier): ?AvatarCacheInterface;

}
