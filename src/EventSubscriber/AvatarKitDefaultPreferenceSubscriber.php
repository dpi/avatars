<?php

namespace Drupal\avatars\EventSubscriber;

use Drupal\avatars\Entity\AvatarKitService;
use Drupal\avatars\Event\AvatarKitEvents;
use Drupal\avatars\Event\EntityServicePreferenceEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for preference events.
 */
class AvatarKitDefaultPreferenceSubscriber implements EventSubscriberInterface {

  /**
   * Sorts avatar services by config entity weight value.
   *
   * @param \Drupal\avatars\Event\EntityServicePreferenceEvent $event
   *   Entity service preference event.
   */
  public function avatarServiceWeights(EntityServicePreferenceEvent $event) {
    $service_ids = array_keys($event->getServices());
    $services = AvatarKitService::loadMultiple($service_ids);

    // Sort by weight.
    uasort($services, [AvatarKitService::class, 'sort']);

    $service_weights = array_flip(array_keys($services));
    $event->setServices($service_weights);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AvatarKitEvents::ENTITY_SERVICE_PREFERENCE][] = ['avatarServiceWeights', 0];
    return $events;
  }

}
