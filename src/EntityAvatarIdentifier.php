<?php

namespace Drupal\avatars;

use dpi\ak\AvatarIdentifier;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * An entity identifier.
 */
class EntityAvatarIdentifier extends AvatarIdentifier implements EntityAvatarIdentifierInterface {

  /**
   * An entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function getEntity(): EntityInterface {
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntity(EntityInterface $entity): EntityAvatarIdentifierInterface {
    $this->entity = $entity;

    // @fixme assumes user entity.
    // @todo replace with tokening system.
    if ($entity instanceof AccountInterface) {
      $this->setRaw($entity->getEmail());
    }
    else {
      $this->setRaw($entity->id());
    }

    return $this;
  }

}
