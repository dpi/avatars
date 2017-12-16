<?php

namespace Drupal\avatars\Entity;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;

/**
 * Defines an interface for role entity storage classes.
 */
interface AvatarKitServiceStorageInterface extends ConfigEntityStorageInterface {

  /**
   * Loads services on demand as a generator is iterated.
   *
   * If an invalid ID is passed, then it is ignored.
   *
   * @param string[] $service_ids
   *   An array of service plugin ID's.
   *
   * @return \Generator|\Drupal\avatars\Plugin\Avatars\Service\AvatarKitServiceInterface[]
   *   Creates service plugins on demand.
   */
  public function loadMultipleGenerator(array $service_ids);

}
