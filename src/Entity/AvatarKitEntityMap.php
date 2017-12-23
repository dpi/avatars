<?php

namespace Drupal\avatars\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Avatar Kit field mapping entity.
 *
 * @ConfigEntityType(
 *   id = "avatars_entity_mapping",
 *   label = @Translation("Avatar Kit entity map"),
 *   config_prefix = "entity_mapping",
 *   entity_keys = {
 *     "id" = "id",
 *   },
 * ),
 */
class AvatarKitEntityMap extends ConfigEntityBase implements AvatarKitEntityMapInterface {

  /**
   * The field name.
   *
   * @var string
   */
  protected $field_name;

  /**
   * The name of the entity type the field is attached to.
   *
   * @var string
   */
  protected $entity_type;

  /**
   * The name of the bundle the field is attached to.
   *
   * @var string
   */
  protected $bundle;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->entity_type . '.' . $this->bundle . '.default';
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();
    $this->addDependency('config', 'field.field.' . $this->entity_type . '.' . $this->bundle . '.' . $this->field_name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldName(): ?string {
    return $this->field_name;
  }

  /**
   * {@inheritdoc}
   */
  public function setFieldName(?string $field_name): self {
    $this->field_name = $field_name;
    return $this;
  }

}
