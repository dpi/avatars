<?php

namespace Drupal\avatars;

use Drupal\avatars\Entity\AvatarCacheInterface;
use Drupal\avatars\Entity\AvatarKitEntityMap;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\file\FileInterface;

/**
 * Handles pushing avatar caches into entities.
 */
class AvatarKitEntityFieldHandler implements AvatarKitEntityFieldHandlerInterface {

  /**
   * The avatar entity handler.
   *
   * @var \Drupal\avatars\AvatarKitEntityHandlerInterface
   */
  protected $entityHandler;

  /**
   * Constructs a new AvatarKitEntityFieldHandler instance.
   *
   * @param \Drupal\avatars\AvatarKitEntityHandlerInterface $entityHandler
   *   The avatar entity handler.
   */
  public function __construct(AvatarKitEntityHandlerInterface $entityHandler) {
    $this->entityHandler = $entityHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function copyCacheToEntity(FieldableEntityInterface $entity, AvatarCacheInterface $avatar_cache): void {
    $field_name = $this->getAvatarFieldName($entity);
    if (!$field_name) {
      throw new \Exception('Entity does not accept avatars.');
    }

    $file = $avatar_cache->getAvatar();
    $this->pushFileIntoEntity($entity, $field_name, $file);
  }

  /**
   * {@inheritdoc}
   */
  public function checkUpdates(FieldableEntityInterface $entity): void {
    // Don't try to find the first avatar when it doesnt have a target field.
    $field_name = $this->getAvatarFieldName($entity);
    if (!$field_name) {
      return;
    }

    $first = $this->entityHandler->findFirst($entity);
    if ($first) {
      $this->copyCacheToEntity($entity, $first);
    }
  }

  /**
   * Places a reference to a file in the field of a given entity.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The destination entity.
   * @param string $field_name
   *   Name of a file field.
   * @param \Drupal\file\FileInterface $file
   *   The new file.
   */
  protected function pushFileIntoEntity(FieldableEntityInterface $entity, string $field_name, FileInterface $file) {
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $field_item_list */
    $field_item_list = $entity->get($field_name);

    // Determine if file is already in the entity.
    $files = $field_item_list->referencedEntities();
    $current_file = reset($files);
    if ($current_file instanceof FileInterface && $current_file->id() == $file->id()) {
      return;
    }

    // Replace existing values, if any, with new file.
    $field_item_list->setValue([$file]);
    $entity->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getAvatarFieldName(EntityInterface $entity): ?string {
    $entity_type = $entity->getEntityTypeId();
    $bundle = $entity->bundle();
    $entity_map = AvatarKitEntityMap::load($entity_type . '.' . $bundle . '.' . 'default');
    return $entity_map ? $entity_map->getFieldName() : NULL;
  }

}
