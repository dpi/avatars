<?php

namespace Drupal\avatars;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface for entity preference manager.
 */
interface AvatarKitEntityPreferenceManagerInterface {

  /**
   * Get the avatar service preferences for an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to get preferences for.
   *
   * @return string[]
   *   An array of avatar service plugin ID's sorted in order of priority. Keys
   *   have no importance.
   */
  public function getPreferences(EntityInterface $entity) : array;

  /**
   * Reset preferences for a entity bundle.
   *
   * @param string $entityType
   *   An entity type ID.
   * @param string $bundle
   *   A bundle.
   */
  public function invalidatePreferences(string $entityType, string $bundle): void;

}
