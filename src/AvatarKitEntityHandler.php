<?php

namespace Drupal\avatars;

use Drupal\avatars\Entity\AvatarCacheInterface;
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
      $identifier = $this->createEntityIdentifier($service_plugin, $entity);

      // Check if the avatar for this entity service already exists.
      $cache = $this->entityLocalCache->getLocalCache($service_id, $identifier);
      if ($cache) {
        if ($cache->getAvatar()) {
          yield $service_id => $cache;
        }
        continue;
      }

      // Download the avatar if it isn't already local.
      $uri = $service_plugin->getAvatar($identifier);
      $cache = $this->entityLocalCache
        ->localCache($service_id, $uri, $identifier);

      // If the avatar was downloaded and saved to a cache successfully.
      if ($cache) {
        if ($cache->getAvatar()) {
          yield $service_id => $cache;
        }
        continue;
      }
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
   */
  public static function createEntityIdentifier(AvatarKitServiceInterface $service, EntityInterface $entity) : EntityAvatarIdentifierInterface {
    $identifier = $service->createIdentifier();
    $new_identifier = new EntityAvatarIdentifierProxy($identifier);
    $new_identifier->setEntity($entity);
    return $new_identifier;
  }

}
