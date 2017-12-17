<?php

namespace Drupal\avatars\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\file\FileInterface;

/**
 * Defines the Avatar cache entity.
 *
 * @ContentEntityType(
 *   id = "avatars_avatar_cache",
 *   label = @Translation("Avatar cache"),
 *   base_table = "avatars_avatar_cache",
 *   entity_keys = {
 *     "id" = "acid",
 *     "bundle" = "avatar_service",
 *     "uuid" = "uuid"
 *   },
 *   bundle_entity_type = "avatars_service"
 * )
 */
class AvatarCache extends ContentEntityBase implements AvatarCacheInterface {

  /**
   * {@inheritdoc}
   */
  public function getAvatarService(): ?AvatarKitServiceInterface {
    return AvatarKitService::load($this->bundle());
  }

  /**
   * {@inheritdoc}
   */
  public function getAvatarServiceId(): string {
    return $this->bundle();
  }

  /**
   * {@inheritdoc}
   */
  public function getAvatar(): ?FileInterface {
    return $this->get('avatar')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getIdentifier(): string {
    return $this->get('identifier')->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // @see \Drupal\Core\Field\Plugin\Field\FieldType\StringItem
    $fields['identifier'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Identifier'))
      ->setDescription(t('The identifier used to generate the avatar.'))
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    // @see \Drupal\dynamic_entity_reference\Plugin\Field\FieldType\DynamicEntityReferenceItem
    $fields['entity'] = BaseFieldDefinition::create('dynamic_entity_reference')
      ->setLabel(t('Entity'))
      ->setDescription(t('The entity the avatar is based on.'))
      ->setRequired(FALSE);

    // @see \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem
    $fields['avatar'] = BaseFieldDefinition::create('file')
      ->setLabel(t('Reference to a file entity containing an image.'))
      ->setCardinality(1)
      ->setRequired(FALSE);

    // @see \Drupal\Core\Field\Plugin\Field\FieldType\CreatedItem
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Date of creation'))
      ->setDescription(t('The date the avatar cache was created.'))
      ->setRequired(TRUE)
      ->setReadOnly(TRUE);

    // @see \Drupal\Core\Field\Plugin\Field\FieldType\TimestampItem
    $fields['last_check'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Date of last check.'))
      ->setDescription(t('Date the avatar was last checked.'))
      ->setRequired(TRUE);

    return $fields;
  }

}
