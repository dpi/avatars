<?php

namespace Drupal\avatars\Event;

use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Used to determine the preference order of avatar services for an entity.
 *
 * @see \Drupal\avatars\Event\AvatarKitEvents::ENTITY_SERVICE_PREFERENCE
 */
class EntityServicePreferenceEvent extends Event {

  /**
   * An array of weights keyed by avatar service entity ID's.
   *
   * Lower value weights are higher priority.
   *
   * @var int[]
   */
  protected $services;

  /**
   * The entity to get preferences.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * Get weights for avatar services.
   *
   * @return string[]
   *   An array of weights keyed by avatar service entity ID's.
   */
  public function getServices(): array {
    return $this->services;
  }

  /**
   * Sets weights for avatar services.
   *
   * @param string[] $services
   *   An array of weights keyed by avatar service entity ID's.
   *
   * @return $this
   *   Returns this event for chaining.
   */
  public function setServices(array $services) : self {
    $this->services = $services;
    return $this;
  }

  /**
   * Get the entity preferences are being set for.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity preferences are being set for.
   */
  public function getEntity(): EntityInterface {
    return $this->entity;
  }

  /**
   * Set the entity preferences are being set for.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity preferences are being set for.
   *
   * @return $this
   *   Returns this event for chaining.
   */
  public function setEntity(EntityInterface $entity) : self {
    $this->entity = $entity;
    return $this;
  }

}
