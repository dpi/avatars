<?php

/**
 * @file
 * Contains \Drupal\avatars\Entity\AvatarPreview.
 */

namespace Drupal\avatars\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\avatars\AvatarPreviewInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\file\FileInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\user\UserInterface;

/**
 * Defines the avatar preview entity.
 *
 * @ContentEntityType(
 *   id = "avatars_preview",
 *   label = @Translation("Avatar preview"),
 *   base_table = "avatars_preview",
 *   entity_keys = {
 *     "id" = "id"
 *   }
 * )
 */
class AvatarPreview extends ContentEntityBase implements AvatarPreviewInterface {

  /**
   * {@inheritdoc}
   */
  public function getAvatarGenerator() {
    return $this->get('avatar_generator')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setAvatarGenerator($avatar_generator) {
    $this->set('avatar_generator', $avatar_generator);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUser() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setUser(UserInterface $user) {
    $this->set('uid', ['entity' => $user]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getAvatar() {
    return $this->get('avatar')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setAvatar(FileInterface $file = NULL) {
    $this->set('avatar', ['entity' => $file]);
    // @todo: change usage on save.
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getScope() {
    return $this->get('scope')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setScope($scope) {
    $this->set('scope', $scope);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function getAvatarPreview($avatar_generator, UserInterface $user) {
    $entities = \Drupal::entityManager()->getStorage('avatars_preview')
      ->loadByProperties([
        'avatar_generator' => $avatar_generator,
        'uid' => $user->id(),
      ]);
    return reset($entities);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Preview ID'))
      ->setDescription(t('The preview ID.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['avatar_generator'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Avatar generator'))
      ->setDescription(t('The avatar generator for the associated avatar.'))
      ->setReadOnly(TRUE)
      ->setRequired(TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('The user who owns this avatar.'))
      ->setSetting('target_type', 'user')
      ->setCardinality(1)
      ->setReadOnly(TRUE)
      ->setRequired(TRUE);

    $fields['avatar'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Reference to a file entity containing an avatar.'))
      ->setSetting('target_type', 'file')
      ->setCardinality(1)
      ->setReadOnly(TRUE)
      ->setRequired(FALSE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Date of creation'))
      ->setDescription(t('The date the avatar preview was created.'))
      ->setRequired(TRUE);

    $fields['scope'] = BaseFieldDefinition::create('integer')
      ->setSetting('unsigned', FALSE)
      ->setSetting('size', 'tiny')
      ->setLabel(t('Preference scope'))
      ->setDescription(t('Which preference caused this avatar to be generated.'))
      ->setDefaultValue(static::SCOPE_TEMPORARY)
      ->setRequired(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    if (!$update && $this->getAvatar()) {
      /** @var \Drupal\file\FileUsage\FileUsageInterface $file_usage */
      $file_usage = \Drupal::service('file.usage')
        ->add($this->getAvatar(), 'avatars', $this->getEntityTypeId(), $this->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    /** @var \Drupal\file\FileUsage\FileUsageInterface $file_usage */
    $file_usage = \Drupal::service('file.usage');

    /** @var static[] $entities */
    foreach ($entities as $avatar_preview) {
      // When the last usage record is gone, the file will be made temporary.
      // After a the file has been temporary for a $few_hours. The temporary,
      // file will be deleted by file_cron(), and then its cache tags will be
      // invalidated. Instead, force the cache invalidation here.
      if ($avatar_preview->getAvatar()) {
        \Drupal::service('cache_tags.invalidator')->invalidateTags(
          $avatar_preview->getAvatar()->getCacheTags()
        );
        $file_usage->delete($avatar_preview->getAvatar(), 'avatars', $avatar_preview->getEntityTypeId(), $avatar_preview->id(), 0);
      }
    }
  }

}
