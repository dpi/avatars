<?php

/**
 * @file
 * Contains \Drupal\avatars\AvatarGeneratorInterface.
 */

namespace Drupal\avatars;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Interface for avatar generator configuration.
 */
interface AvatarGeneratorInterface extends ConfigEntityInterface {

  /**
   * Returns the plugin instance.
   *
   * @return \Drupal\avatars\Plugin\AvatarGenerator\AvatarGeneratorPluginInterface
   *   The plugin instance for this avatar generator.
   */
  public function getPlugin();

  /**
   * Get weight for the avatar generator.
   *
   * @return mixed
   */
  public function getWeight();

  /**
   * Set weight for the avatar generator.
   *
   * @param int $weight
   *   Weight for the avatar generator.
   *
   * @return $this
   */
  public function setWeight($weight);

}
