<?php

namespace Drupal\avatars;

use Drupal\avatars\Entity\AvatarCacheInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileInterface;
use Psr\Http\Message\ResponseInterface;
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
   * Storage for 'file' entity.
   *
   * @var \Drupal\Core\Entity\ContentEntityStorageInterface
   */
  protected $fileStorage;

  /**
   * The logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The download utility.
   *
   * @var \Drupal\avatars\AvatarKitDownloadUtilityInterface
   */
  protected $downloadUtility;

  /**
   * Creates a new AvatarKitLocalCache instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   * @param \Drupal\avatars\AvatarKitDownloadUtilityInterface $downloadUtility
   *   The download utility.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerInterface $logger, AvatarKitDownloadUtilityInterface $downloadUtility) {
    $this->avatarCacheStorage = $entityTypeManager->getStorage('avatars_avatar_cache');
    $this->fileStorage = $entityTypeManager->getStorage('file');
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
  public function cacheLocalFileEntity(string $service_id, string $uri, EntityAvatarIdentifierInterface $identifier) : ?AvatarCacheInterface {
    $identifier_hash = $identifier->getHashed();

    $files = $this->fileStorage->loadByProperties(['uri' => $uri]);
    if (!$files) {
      return NULL;
    }

    /** @var \Drupal\file\FileInterface $file */
    $file = reset($files);

    $avatar_cache = $this->getLocalCache($service_id, $identifier);
    if ($avatar_cache) {
      $existing_file = $avatar_cache->getAvatar();
      if ($this->fileEntityIsDifferent($file, $existing_file)) {
        $avatar_cache->setAvatar($file);
      }
    }
    else {
      /** @var \Drupal\avatars\Entity\AvatarCacheInterface $avatar_cache */
      $avatar_cache = $this->avatarCacheStorage->create([
        'avatar_service' => $service_id,
        'identifier' => $identifier_hash,
        'avatar' => $file,
      ]);
    }

    $avatar_cache
      ->markChecked()
      ->save();

    return $avatar_cache;
  }

  /**
   * {@inheritdoc}
   */
  public function cacheRemote(string $service_id, string $uri, EntityAvatarIdentifierInterface $identifier) : ?AvatarCacheInterface {
    $entity = $identifier->getEntity();
    $identifier_hash = $identifier->getHashed();

    $file = NULL;
    try {
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

    $avatar_cache_existing = $this->getLocalCache($service_id, $identifier);
    if (isset($response)) {
      if ($avatar_cache_existing) {
        $contents_are_different = $this->contentsIsDifferent($avatar_cache_existing, $response);
        if (!$contents_are_different) {
          $avatar_cache_existing
            ->markChecked()
            ->save();
          return $avatar_cache_existing;
        }
      }

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

    if (!$file) {
      return NULL;
    }

    if ($avatar_cache_existing) {
      $avatar_cache = $avatar_cache_existing;
    }
    else {
      /** @var \Drupal\avatars\Entity\AvatarCacheInterface $avatar_cache */
      $avatar_cache = $this->avatarCacheStorage->create([
        'avatar_service' => $service_id,
        'identifier' => $identifier_hash,
      ]);
    }

    $avatar_cache
      ->markChecked()
      ->setAvatar($file)
      ->save();

    return $avatar_cache;
  }

  /**
   * Compare whether two entities are different.
   *
   * @param \Drupal\file\FileInterface|null $a
   *   A file entity, or NULL.
   * @param \Drupal\file\FileInterface|null $b
   *   A file entity, or NULL.
   *
   * @return bool
   *   Whether two entities are different.
   */
  protected function fileEntityIsDifferent(?FileInterface $a, ?FileInterface $b) {
    $a_id = $a ? $a->id() : NULL;
    $b_id = $b ? $b->id() : NULL;
    return $a_id != $b_id;
  }

  /**
   * Checks whether avatar cache file is different to a downloaded file.
   *
   * @param \Drupal\avatars\Entity\AvatarCacheInterface $avatar_cache
   *   An avatar cache entity.
   * @param \Psr\Http\Message\ResponseInterface $response
   *   A HTTP response.
   *
   * @return bool
   *   Whether the files are different.
   */
  protected function contentsIsDifferent(AvatarCacheInterface $avatar_cache, ResponseInterface $response): bool {
    $existing_file = $avatar_cache->getAvatar();
    if (!$existing_file) {
      return TRUE;
    }

    $existing_file_uri = $existing_file->getFileUri();
    if (!$existing_file_uri) {
      return TRUE;
    }

    // Returns false if file does not exist.
    $existing_file_contents = file_get_contents($existing_file_uri);
    if (!$existing_file_contents) {
      return TRUE;
    }

    $contents = (string) $response->getBody();
    return $contents != $existing_file_contents;
  }

  /**
   * {@inheritdoc}
   */
  public function cacheEmpty(string $service_id, ?string $uri, EntityAvatarIdentifierInterface $identifier) : AvatarCacheInterface {
    $identifier_hash = $identifier->getHashed();

    $avatar_cache = $this->getLocalCache($service_id, $identifier);
    if (!$avatar_cache) {
      /** @var \Drupal\avatars\Entity\AvatarCacheInterface $avatar_cache */
      $avatar_cache = $this->avatarCacheStorage->create([
        'avatar_service' => $service_id,
        'identifier' => $identifier_hash,
      ]);
    }

    $avatar_cache
      ->markChecked()
      ->setAvatar(NULL)
      ->save();

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
    $name = $identifier->getEntity()->id();
    return \file_create_filename($name, 'public://avatar_kit/' . $service_id . '/identifier/');
  }

}
