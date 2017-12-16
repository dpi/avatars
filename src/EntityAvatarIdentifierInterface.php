<?php


namespace Drupal\avatars;

use dpi\ak\AvatarIdentifierInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * An entity identifier.
 */
interface EntityAvatarIdentifierInterface extends AvatarIdentifierInterface {

  /**
   * Get the entity for this identifier.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   An entity.
   */
  public function getEntity(): EntityInterface;

  /**
   * Set the entity for this identifier.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   An entity.
   *
   * @return $this
   *   This object.
   */
  public function setEntity(EntityInterface $entity): EntityAvatarIdentifierInterface;

}
