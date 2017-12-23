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
      // getEmail API is incorrect. Should be string|null. Returns null in the
      // case of anonymous user.
      // See https://www.drupal.org/project/drupal/issues/2932774
      $email = $entity->getEmail() ?? 'anonymous@example.com';
      $this->setRaw($email);
    }
    else {
      $this->setRaw($entity->id());
    }

    return $this;
  }

}
