<?php

namespace Drupal\avatars\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Used to alter avatar service plugin definitions.
 *
 * @see \Drupal\avatars\Event\AvatarKitEvents::PLUGIN_SERVICE_ALTER
 */
class AvatarKitServiceDefinitionAlterEvent extends Event {

  /**
   * Alter definitions of avatar service plugins.
   *
   * @var array
   */
  protected $definitions;

  /**
   * Get definitions.
   *
   * @return array
   *   An array of definitions.
   */
  public function getDefinitions(): array {
    return $this->definitions;
  }

  /**
   * Set definitions.
   *
   * @param array $definitions
   *   Set definitions.
   *
   * @return $this
   *   Returns this event for chaining.
   */
  public function setDefinitions(array $definitions): self {
    $this->definitions = $definitions;
    return $this;
  }

}
