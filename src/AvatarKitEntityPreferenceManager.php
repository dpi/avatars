<?php

namespace Drupal\avatars;

use Drupal\avatars\Event\AvatarKitEvents;
use Drupal\avatars\Event\EntityServicePreferenceEvent;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Manages order in which services should be fetched for entities.
 */
class AvatarKitEntityPreferenceManager implements AvatarKitEntityPreferenceManagerInterface {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Storage for avatar service entities.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected $serviceStorage;

  /**
   * The avatar service preference cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $preferenceCacheBackend;

  /**
   * Creates a new AvatarKitEntityPreferenceManager object.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $preferenceCacheBackend
   *   The avatar service preference cache backend.
   */
  public function __construct(EventDispatcherInterface $eventDispatcher, EntityTypeManagerInterface $entityTypeManager, CacheBackendInterface $preferenceCacheBackend) {
    $this->eventDispatcher = $eventDispatcher;
    $this->serviceStorage = $entityTypeManager->getStorage('avatars_service');
    $this->preferenceCacheBackend = $preferenceCacheBackend;
  }

  /**
   * {@inheritdoc}
   */
  public function getPreferences(EntityInterface $entity) : array {
    $preference_cache_id = $entity->getEntityTypeId() . ':' . $entity->id();
    $cache_item = $this->preferenceCacheBackend
      ->get($preference_cache_id);
    if ($cache_item !== FALSE) {
      return $cache_item->data;
    }

    $services = $this->serviceStorage
      ->loadMultiple();

    $services_weights = array_flip(array_keys($services));
    $event = (new EntityServicePreferenceEvent())
      ->setServices($services_weights)
      ->setEntity($entity);

    $this->eventDispatcher
      ->dispatch(AvatarKitEvents::ENTITY_SERVICE_PREFERENCE, $event);

    $services = $event->getServices();

    // Sort the array by weight (values), maintaining keys.
    asort($services);

    // Strip out the weights, leaving only service ID's in sorted order.
    $service_ids = array_keys($services);

    $this->preferenceCacheBackend
      ->set($preference_cache_id, $service_ids);

    return $service_ids;
  }

  /**
   * {@inheritdoc}
   */
  public function invalidatePreferences(string $entityType, string $bundle): void {
    // There is currently no way to differentiate entity type / bundles. So
    // reset everything.
    $this->preferenceCacheBackend->invalidateAll();
  }

}
