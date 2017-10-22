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
   * An array of avatar service entity ID's keyed by weight integer.
   *
   * Lower value keys are higher priority.
   *
   * @var string[]
   */
  protected $services;

  /**
   * The entity to get preferences.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * Get avatar service entity ID's.
   *
   * @return string[]
   *   An array of avatar service entity ID's keyed by weight integer.
   */
  public function getServices(): array {
    return $this->services;
  }

  /**
   * Set array of avatar service entity ID's.
   *
   * @param string[] $services
   *   An array of avatar service entity ID's keyed by weight integer.
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
