<?php

namespace Drupal\avatars;

use Drupal\avatars\Entity\AvatarCacheInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\file\FileInterface;

/**
 * Handles pushing avatar caches into entities.
 */
class AvatarKitEntityFieldHandler implements AvatarKitEntityFieldHandlerInterface {

  /**
   * {@inheritdoc}
   */
  public function copyCacheToEntity(FieldableEntityInterface $entity, AvatarCacheInterface $avatar_cache): void {
    // @todo Allow site builder to set target field.
    $field_name = 'user_picture';
    $file = $avatar_cache->getAvatar();
    $this->pushFileIntoEntity($entity, $field_name, $file);
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

}
