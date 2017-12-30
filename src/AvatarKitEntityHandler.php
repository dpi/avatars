<?php

namespace Drupal\avatars;

use Drupal\avatars\Entity\AvatarCacheInterface;
use Drupal\avatars\Exception\AvatarKitEntityAvatarIdentifierException;
use Drupal\avatars\Plugin\Avatars\Service\AvatarKitServiceInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Downloads and caches avatars into entities.
 */
class AvatarKitEntityHandler implements AvatarKitEntityHandlerInterface {

  /**
   * Storage for Avatar services.
   *
   * @var \Drupal\avatars\Entity\AvatarKitServiceStorageInterface
   */
  protected $serviceStorage;

  /**
   * Avatar Kit local cache.
   *
   * @var \Drupal\avatars\AvatarKitLocalCache
   */
  protected $entityLocalCache;

  /**
   * Avatar Kit preference manager.
   *
   * @var AvatarKitEntityPreferenceManagerInterface
   */
  protected $preferenceManager;

  /**
   * AvatarKitManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\avatars\AvatarKitLocalCache $entityLocalCache
   *   Avatar Kit local cache.
   * @param \Drupal\avatars\AvatarKitEntityPreferenceManagerInterface $preferenceManager
   *   Avatar Kit preference manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, AvatarKitLocalCache $entityLocalCache, AvatarKitEntityPreferenceManagerInterface $preferenceManager) {
    $this->serviceStorage = $entityTypeManager->getStorage('avatars_service');
    $this->entityLocalCache = $entityLocalCache;
    $this->preferenceManager = $preferenceManager;
  }

  /**
   * {@inheritdoc}
   */
  public function findFirst(EntityInterface $entity): ?AvatarCacheInterface {
    $all = $this->findAll($entity);
    $current = $all->current();
    return $current;
  }

  /**
   * {@inheritdoc}
   */
  public function findAll(EntityInterface $entity): \Generator {
    $service_ids = $this->preferenceManager->getPreferences($entity);
    foreach ($this->serviceStorage->loadMultipleGenerator($service_ids) as $service_id => $service_plugin) {
      try {
        $identifier = $this->createEntityIdentifier($service_plugin, $entity);
      }
      catch (AvatarKitEntityAvatarIdentifierException $e) {
        continue;
      }

      // Check if the avatar for this entity service already exists.
      $cache = $this->entityLocalCache->getLocalCache($service_id, $identifier);
      if ($cache) {
        // Yield if there is a file.
        if ($cache->getAvatar()) {
          yield $service_id => $cache;
        }
        continue;
      }

      // A new cache needs to be created:
      $uri = $service_plugin->getAvatar($identifier);
      $args = [$service_id, $uri, $identifier];

      if ($uri) {
        // Try local.
        $plugin_supports_local = $service_plugin->getPluginDefinition()['files'] ?? FALSE;
        if ($plugin_supports_local) {
          $cache = $this->entityLocalCache->cacheLocalFileEntity(...$args);
          if ($cache) {
            yield $service_id => $cache;
            continue;
          }
        }

        // Try remote.
        $cache = $cache ?? $this->entityLocalCache->cacheRemote(...$args);
        if ($cache) {
          yield $service_id => $cache;
          continue;
        }
      }

      // Else empty.
      // Don't yield the cache because it isn't considered *successful*.
      $this->entityLocalCache->cacheEmpty(...$args);
    }
  }

  /**
   * Creates a Entity avatar identifier object for a service.
   *
   * @param \Drupal\avatars\Plugin\Avatars\Service\AvatarKitServiceInterface $service
   *   An avatar service.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to create an identifier.
   *
   * @return \Drupal\avatars\EntityAvatarIdentifierInterface
   *   An identifier object.
   *
   * @throws \Drupal\avatars\Exception\AvatarKitEntityAvatarIdentifierException
   */
  public static function createEntityIdentifier(AvatarKitServiceInterface $service, EntityInterface $entity) : EntityAvatarIdentifierInterface {
    $identifier = $service->createIdentifier();
    $new_identifier = new EntityAvatarIdentifierProxy($identifier);
    $new_identifier->setEntity($entity);
    return $new_identifier;
  }

}
