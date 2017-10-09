<?php

namespace Drupal\avatars\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\avatars\Plugin\Avatars\Service\AvatarKitServiceInterface as AvatarKitServicePluginInterface;

/**
 * Defines interface for avatar service entities.
 */
interface AvatarKitServiceInterface extends ConfigEntityInterface, EntityWithPluginCollectionInterface {

  /**
   * Returns the weight.
   *
   * @return int
   *   The weight of this avatar service.
   */
  public function getWeight() : int;

  /**
   * Sets the weight to the given value.
   *
   * @param int $weight
   *   The desired weight.
   *
   * @return $this
   *   This avatar service for chaining.
   */
  public function setWeight(int $weight) : self;

  /**
   * Get the ID of avatar service plugin.
   *
   * @return string
   *   The ID of avatar service plugin
   */
  public function getPluginId() : string;

  /**
   * Returns the plugin instance.
   *
   * @return \Drupal\avatars\Plugin\Avatars\Service\AvatarKitServiceInterface
   *   A Avatar Kit service instance.
   */
  public function getPlugin() : ?AvatarKitServicePluginInterface;

}
