<?php

/**
 * @file
 * Contains \Drupal\avatars\AvatarGeneratorStorageInterface.
 */

namespace Drupal\avatars;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;

/**
 * Defines an interface for avatar generator storage.
 */
interface AvatarGeneratorStorageInterface extends ConfigEntityStorageInterface {

  /**
   * Get all enabled avatar generator plugin instances.
   *
   * @return \Drupal\avatars\AvatarGeneratorInterface[]
   *   An array of avatar generator plugin instances.
   */
  public function getEnabledAvatarGenerators();

}
