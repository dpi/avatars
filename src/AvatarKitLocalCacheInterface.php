<?php

namespace Drupal\avatars;

use Drupal\avatars\Entity\AvatarCacheInterface;
use Drupal\Core\Entity\EntityInterface;

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
   * Creates an avatar cache entity by linking a pre-existing file entity.
   *
   * @param string $service_id
   *   An avatar service ID.
   * @param string $uri
   *   The URL of an file stored locally.
   * @param \Drupal\avatars\EntityAvatarIdentifierInterface $identifier
   *   An entity avatar identifier.
   *
   * @return \Drupal\avatars\Entity\AvatarCacheInterface|null
   *   An avatar cache entity, or NULL if no pre-existing file entity exists
   *   with provided URI.
   */
  public function cacheLocalFileEntity(string $service_id, string $uri, EntityAvatarIdentifierInterface $identifier): ?AvatarCacheInterface;

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
   *   An avatar cache entity, or NULL if the avatar failed to download.
   */
  public function cacheRemote(string $service_id, string $uri, EntityAvatarIdentifierInterface $identifier): ?AvatarCacheInterface;

  /**
   * Creates an empty avatar cache entity.
   *
   * Used if a valid cache entity could not be created.
   *
   * @param string $service_id
   *   An avatar service ID.
   * @param string $uri
   *   The URL of an avatar to download.
   * @param \Drupal\avatars\EntityAvatarIdentifierInterface $identifier
   *   An entity avatar identifier.
   *
   * @return \Drupal\avatars\Entity\AvatarCacheInterface
   *   An avatar cache entity.
   */
  public function cacheEmpty(string $service_id, string $uri, EntityAvatarIdentifierInterface $identifier) : AvatarCacheInterface;

  /**
   * Determines if caches for an entity need to be invalidated.
   *
   * Used when an entity is modified.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to invalidate.
   */
  public function invalidateCaches(EntityInterface $entity): void;

}
