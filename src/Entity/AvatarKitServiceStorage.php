<?php

namespace Drupal\avatars\Entity;

use Drupal\Core\Config\Entity\ConfigEntityStorage;

/**
 * Controller class for user roles.
 */
class AvatarKitServiceStorage extends ConfigEntityStorage implements AvatarKitServiceStorageInterface {

  /**
   * {@inheritdoc}
   */
  public function loadMultipleGenerator(array $service_ids) {
    foreach ($service_ids as $service_id) {
      /** @var \Drupal\avatars\Entity\AvatarKitServiceInterface $service */
      $service = $this->load($service_id);
      if ($service) {
        yield $service_id => $service->getPlugin();
      }
    }
  }

}
