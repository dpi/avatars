<?php

namespace Drupal\avatars;

use Drupal\avatars\Entity\AvatarCacheInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Cache remote files locally into file entities.
 */
class AvatarKitLocalCache implements AvatarKitLocalCacheInterface {

  /**
   * Storage for 'avatars_avatar_cache' entity.
   *
   * @var \Drupal\Core\Entity\ContentEntityStorageInterface
   */
  protected $avatarCacheStorage;

  /**
   * The logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The download utility.
   *
   * @var AvatarKitDownloadUtilityInterface
   */
  protected $downloadUtility;

  /**
   * Creates a new AvatarKitLocalCache instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   * @param AvatarKitDownloadUtilityInterface $downloadUtility
   *   The download utility.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerInterface $logger, AvatarKitDownloadUtilityInterface $downloadUtility) {
    $this->avatarCacheStorage = $entityTypeManager->getStorage('avatars_avatar_cache');
    $this->logger = $logger;
    $this->downloadUtility = $downloadUtility;
  }

  /**
   * {@inheritdoc}
   */
  public function getLocalCache(string $service_id, EntityAvatarIdentifierInterface $identifier) : ?AvatarCacheInterface {
    $hashed = $identifier->getHashed();
    $ids = $this->avatarCacheStorage->getQuery()
      ->condition('avatar_service', $service_id)
      ->condition('identifier', $hashed)
      ->execute();
    if ($ids) {
      $id = reset($ids);
      return $this->avatarCacheStorage->load($id);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function localCache(string $service_id, string $uri, EntityAvatarIdentifierInterface $identifier) : ?AvatarCacheInterface {
    $entity = $identifier->getEntity();
    $identifier_hash = $identifier->getHashed();

    $file = NULL;
    try {
      // @todo determine if URI is already a local file + managed by a permanent
      // file entity.
      $response = $this->downloadUtility->get($uri);
    }
    catch (\Exception $e) {
      // Acceptable exceptions.
      $log_args = [
        '@service' => $service_id,
        '@entity_type' => $entity->getEntityTypeId(),
        '@entity_id' => $entity->id(),
        '@message' => $e->getMessage(),
      ];
      $this->logger
        ->debug('Failed to download @service avatar for @entity_type #@entity_id. This failure is probably acceptable. Message is: @message', $log_args);
    }

    if (isset($response)) {
      // Different try block since we want to log these exceptions.
      try {
        $filepath = $this->avatarFileName($service_id, $identifier);
        $file = $this->downloadUtility->createFile($response, $filepath);
      }
      catch (\Exception $e) {
        $this->logger
          ->error('Failed to create avatar file: @exception', ['@exception' => $e->getMessage()]);
      }
    }

    /** @var \Drupal\avatars\Entity\AvatarCacheInterface $avatar_cache */
    $avatar_cache = $this->avatarCacheStorage->create([
      'avatar_service' => $service_id,
      'identifier' => $identifier_hash,
      'avatar' => $file,
    ]);
    $avatar_cache->save();

    // @todo ensure usage entry is created.

    return $avatar_cache;
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateCaches(EntityInterface $entity): void {
    // Get all caches in storage, don't need to worry about preferences or
    // progressively loading each service.
    // @todo
  }

  /**
   * Generate the filesystem destination for an avatar.
   *
   * @param string $service_id
   *   An avatar service ID.
   * @param \Drupal\avatars\EntityAvatarIdentifierInterface $identifier
   *   An entity avatar identifier.
   *
   * @return string
   *   A filesystem URI.
   */
  protected function avatarFileName(string $service_id, EntityAvatarIdentifierInterface $identifier): string {
    return 'public://avatar_kit/' . $service_id . '/identifier/' . $identifier->getHashed();
  }

}
