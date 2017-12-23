<?php

namespace Drupal\avatars\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides interface for Avatar Kit field mapping entity.
 */
interface AvatarKitEntityMapInterface extends ConfigEntityInterface {

  /**
   * Get field name.
   *
   * @return string|null
   *   The field name.
   */
  public function getFieldName(): ?string;

  /**
   * Set the field name.
   *
   * @param string|null $field_name
   *   The field name.
   *
   * @return $this
   *   This entity mapping for chaining.
   */
  public function setFieldName(?string $field_name);

}
