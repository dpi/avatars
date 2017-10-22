<?php

namespace Drupal\avatars;

use Drupal\avatars\Event\AvatarKitEvents;
use Drupal\avatars\Event\EntityServicePreferenceEvent;
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
   * Creates a new AvatarKitEntityPreferenceManager object.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EventDispatcherInterface $eventDispatcher, EntityTypeManagerInterface $entityTypeManager) {
    $this->eventDispatcher = $eventDispatcher;
    $this->serviceStorage = $entityTypeManager->getStorage('avatars_service');
  }

  /**
   * {@inheritdoc}
   */
  public function getPreferences(EntityInterface $entity) : array {
    // @todo preferences should be cached per entity.
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

    return $service_ids;
  }

}
