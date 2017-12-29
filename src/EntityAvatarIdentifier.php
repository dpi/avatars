<?php

namespace Drupal\avatars;

use dpi\ak\AvatarIdentifier;
use Drupal\avatars\Exception\AvatarKitEntityAvatarIdentifierException;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;
use Drupal\field\Entity\FieldConfig;

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

    $raw = $this->tokenReplace($entity);
    if (empty($raw)) {
      throw new AvatarKitEntityAvatarIdentifierException('Pre hashed string is empty after token replacement.');
    }

    $this->setRaw($raw);

    return $this;
  }

  /**
   * Generate a pre-hashed string for an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Tokens will be replaced given this entity context.
   *
   * @return string
   *   A pre-hashed string for an entity.
   *
   * @throws \Drupal\avatars\Exception\AvatarKitEntityAvatarIdentifierException
   */
  protected function tokenReplace(EntityInterface $entity): string {
    $field_config = $this->entityFieldHandler()->getAvatarFieldConfig($entity);
    if (!$field_config) {
      throw new AvatarKitEntityAvatarIdentifierException('No field mapping/field config for this entity.');
    }

    $hash_settings = $field_config->getThirdPartySetting('avatars', 'hash');
    $token_text = $hash_settings['contents'] ?? '';
    if (empty($token_text)) {
      throw new AvatarKitEntityAvatarIdentifierException('No token text defined for this entity.');
    }

    $data = [];
    $data[$entity->getEntityTypeId()] = $entity;
    $options = [];
    $options['clear'] = TRUE;
    return $this->token()->replace($token_text, $data, $options);
  }

  /**
   * Get the entity field handler service.
   *
   * @return \Drupal\avatars\AvatarKitEntityFieldHandlerInterface
   *   The entity field handler service.
   */
  protected function entityFieldHandler(): AvatarKitEntityFieldHandlerInterface {
    return \Drupal::service('avatars.entity.field_handler');
  }

  /**
   * Get Drupal placeholder/token replacement system.
   *
   * @return \Drupal\Core\Utility\Token
   *   The Drupal placeholder/token replacement system.
   */
  protected function token(): Token {
    return \Drupal::token();
  }

}
