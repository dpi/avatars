<?php

namespace Drupal\avatars;

use Drupal\avatars\Entity\AvatarCacheInterface;
use Drupal\avatars\Exception\AvatarKitEntityAvatarIdentifierException;
use Drupal\avatars\Plugin\Avatars\Service\AvatarKitServiceInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LoggerInterface;

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
   * @var \Drupal\avatars\AvatarKitLocalCacheInterface
   */
  protected $entityLocalCache;

  /**
   * Avatar Kit preference manager.
   *
   * @var AvatarKitEntityPreferenceManagerInterface
   */
  protected $preferenceManager;

  /**
   * Whether this handler should be in read only mode.
   *
   * If set to true, then no avatar cache entities will be saved or updated.
   *
   * @var bool
   */
  protected $readOnly = FALSE;

  /**
   * The logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * AvatarKitManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger instance.
   * @param \Drupal\avatars\AvatarKitLocalCacheInterface $entityLocalCache
   *   Avatar Kit local cache.
   * @param \Drupal\avatars\AvatarKitEntityPreferenceManagerInterface $preferenceManager
   *   Avatar Kit preference manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, LoggerInterface $logger, AvatarKitLocalCacheInterface $entityLocalCache, AvatarKitEntityPreferenceManagerInterface $preferenceManager) {
    $this->serviceStorage = $entityTypeManager->getStorage('avatars_service');
    $this->entityLocalCache = $entityLocalCache;
    $this->preferenceManager = $preferenceManager;
    $this->logger = $logger;
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
        $this->logger->debug('No identifier for entity %entity_type:%id', [
          '%entity_type' => $entity->getEntityTypeId(),
          '%id' => $entity->id(),
        ]);
        continue;
      }

      // Check if the avatar for this entity service already exists.
      $cache_existing = $this->entityLocalCache->getLocalCache($service_id, $identifier);
      if ($cache_existing) {
        $needsUpdate = $this->entityLocalCache->cacheNeedsUpdate($cache_existing);
        if ($this->isReadOnly() || !$needsUpdate) {
          // Yield if there is a file.
          if ($cache_existing->getAvatar()) {
            yield $service_id => $cache_existing;
          }
          continue;
        }
      }

      if ($this->isReadOnly()) {
        continue;
      }

      // A new cache needs to be created:
      $uri = $service_plugin->getAvatar($identifier);
      $args = [$service_id, $uri, $identifier];

      if ($uri) {
        // Try local.
        $plugin_supports_local = $service_plugin->getPluginDefinition()['files'] ?? FALSE;
        if ($plugin_supports_local) {
          $cache_local = $this->entityLocalCache->cacheLocalFileEntity(...$args);
          if ($cache_local) {
            yield $service_id => $cache_local;
            continue;
          }
        }

        // Try remote.
        $cache_remote = $this->entityLocalCache->cacheRemote(...$args);
        if ($cache_remote) {
          yield $service_id => $cache_remote;
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

  /**
   * Determine if an avatar cache needs to be checked.
   *
   * @param \Drupal\avatars\Plugin\Avatars\Service\AvatarKitServiceInterface $service_plugin
   *   An avatar service plugin.
   * @param \Drupal\avatars\Entity\AvatarCacheInterface $avatar_cache
   *   An avatar cache entity.
   *
   * @return bool
   *   Whether the avatar cache needs to be re-checked.
   */
  protected function cacheNeedsUpdate(AvatarKitServiceInterface $service_plugin, AvatarCacheInterface $avatar_cache): bool {
    $plugin_is_dynamic = $service_plugin->getPluginDefinition()['dynamic'] ?? FALSE;
    if (!$plugin_is_dynamic) {
      // Static avatars never need updates.
      return FALSE;
    }

    $checkTime = $avatar_cache->getLastCheckTime() ?? 0;
    $now = $this->time->getCurrentTime();

    $pluginConfiguration = $service_plugin->getConfiguration();
    $lifetime = $pluginConfiguration['lifetime'] ?? NULL;
    return $lifetime ? $checkTime < ($now - $lifetime) : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function isReadOnly(): bool {
    return $this->readOnly;
  }

  /**
   * {@inheritdoc}
   */
  public function setReadOnly(bool $readOnly): void {
    $this->readOnly = $readOnly;
  }

}
