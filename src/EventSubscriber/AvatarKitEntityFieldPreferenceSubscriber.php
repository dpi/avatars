<?php

namespace Drupal\avatars\EventSubscriber;

use Drupal\avatars\AvatarKitEntityFieldHandlerInterface;
use Drupal\avatars\Entity\AvatarKitService;
use Drupal\avatars\Event\AvatarKitEvents;
use Drupal\avatars\Event\EntityServicePreferenceEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber for preference events.
 */
class AvatarKitEntityFieldPreferenceSubscriber implements EventSubscriberInterface {

  /**
   * The avatar entity field handler.
   *
   * @var \Drupal\avatars\AvatarKitEntityFieldHandlerInterface
   */
  private $entityFieldHandler;

  /**
   * Constructs a new AvatarKitEntityFieldPreferenceSubscriber.
   *
   * @param \Drupal\avatars\AvatarKitEntityFieldHandlerInterface $entityFieldHandler
   *   The avatar entity field handler.
   */
  public function __construct(AvatarKitEntityFieldHandlerInterface $entityFieldHandler) {
    $this->entityFieldHandler = $entityFieldHandler;
  }

  /**
   * Sorts avatar services by services as they appear in field configuration.
   *
   * @param \Drupal\avatars\Event\EntityServicePreferenceEvent $event
   *   Entity service preference event.
   */
  public function avatarServiceFieldWeights(EntityServicePreferenceEvent $event) {
    $entity = $event->getEntity();
    $field_config = $this->entityFieldHandler->getAvatarFieldConfig($entity);
    if ($field_config) {
      $existingServices = $event->getServices();
      $existingServices = array_flip($existingServices);
      $configServices = $field_config->getThirdPartySetting('avatars', 'services', []);

      // Existing services is considered the pool of allowed/enabled services.
      // Filter these services down to what is also present in configuration,
      // omitting any service ID's that no longer exist.
      $service_ids = array_intersect($configServices, $existingServices);

      // Reset the ID's, then flip.
      $service_ids = array_flip(array_values($service_ids));
      $event->setServices($service_ids);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AvatarKitEvents::ENTITY_SERVICE_PREFERENCE][] = ['avatarServiceFieldWeights', -512];
    return $events;
  }

}
