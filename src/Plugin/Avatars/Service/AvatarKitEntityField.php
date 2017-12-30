<?php

namespace Drupal\avatars\Plugin\Avatars\Service;

use dpi\ak\AvatarIdentifier;
use dpi\ak\AvatarIdentifierInterface;
use Drupal\avatars\EntityAvatarIdentifierInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field\Entity\FieldConfig;

/**
 * Gets avatar from a field in the same entity.
 *
 * @AvatarKitService(
 *   id = "avatars_entity_field",
 *   label = @Translation("Copy uploaded image"),
 *   description = @Translation("Copies an image uploaded to the entity."),
 *   files = TRUE,
 * )
 */
class AvatarKitEntityField extends AvatarKitServiceBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getAvatar(AvatarIdentifierInterface $identifier) : ?string {
    if (!$identifier instanceof EntityAvatarIdentifierInterface) {
      // Identifier must have an entity.
      return NULL;
    }

    $entity = $identifier->getEntity();

    $field = $this->configuration['field'];
    if ($field) {
      [$entity_type, $bundle, $fieldName] = explode('.', $field);
      if (($entity_type == $entity->getEntityTypeId()) && ($bundle == $entity->bundle())) {
        /** @var \Drupal\file\Plugin\Field\FieldType\FileFieldItemList $field_list */
        $field_list = $entity->{$fieldName};
        /** @var \Drupal\file\Plugin\Field\FieldType\FileItem $item */
        $item = $field_list->first();
        /** @var \Drupal\file\FileInterface $file */
        $file = $field_list->entity;
        if ($file) {
          $uri = $file->getFileUri();
          return $uri;
        }
        return NULL;
      }
    }

    // Entity does not map to this plugin.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies(): array {
    $dependencies = [];

    $field = $this->configuration['field'] ?? NULL;
    if ($field) {
      $dependencies['config'][] = 'field.field.' . $field;
    }

    return $dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    $configuration = parent::defaultConfiguration();
    $configuration['field'] = NULL;
    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);

    $avatar_field_types = ['file', 'image'];

    $fieldsOptions = [];
    foreach ($avatar_field_types as $fieldType) {
      $fieldMap = $this->entityFieldManager()->getFieldMapByFieldType($fieldType);
      unset($fieldMap['avatars_avatar_cache']);
      foreach ($fieldMap as $entity_type => $fields) {
        $entityTypeInfo = $this->entityTypeManager()->getDefinition($entity_type);
        $bundleInfo = $this->entityTypeBundleInfo()->getBundleInfo($entity_type);

        foreach ($fields as $fieldName => $fieldInfo) {
          ['bundles' => $bundles] = $fieldInfo;
          foreach ($bundles as $bundle) {
            $entity_type_label = $entityTypeInfo->getLabel();
            $key = $entity_type . ':' . $bundle . ':' . $fieldName;
            $fieldsOptions[(string) $entity_type_label][$key] = $this->t('@bundle: @field_name', [
              '@bundle' => $bundleInfo[$bundle]['label'],
              '@field_name' => $fieldName,
            ]);
          }
        }
      }
    }

    $form['help'] = [
      '#plain_text' => $this->t('This service will run if the requested entity type and bundle is the same as specified by this field.'),
    ];

    $form['field'] = [
      '#type' => 'select',
      '#title' => $this->t('Field'),
      '#description' => $this->t('Copy field values from this field.'),
      '#options' => $fieldsOptions,
      '#empty_option' => $this->t('- Select -'),
      '#required' => TRUE,
    ];

    $field = $this->configuration['field'];
    if ($field) {
      [$entity_type, $bundle, $fieldName] = explode('.', $field);
      $form['field']['#default_value'] = $entity_type . ':' . $bundle . ':' . $fieldName;
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::submitConfigurationForm($form, $form_state);

    $field = $form_state->getValue('field');
    [$entity_type, $bundle, $fieldName] = explode(':', $field);

    $field_config_id = $entity_type . '.' . $bundle . '.' . $fieldName;
    $fieldConfig = FieldConfig::load($field_config_id);
    if ($fieldConfig) {
      // @todo currently does not work with base fields.
      $this->configuration['field'] = $field_config_id;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createIdentifier() : AvatarIdentifierInterface {
    return (new AvatarIdentifier())
      ->setHasher(function ($string) {
        return $string;
      });
  }

  /**
   * Get the entity field manager.
   *
   * @return \Drupal\Core\Entity\EntityFieldManagerInterface
   *   The entity field manager.
   */
  protected function entityFieldManager(): EntityFieldManagerInterface {
    return \Drupal::service('entity_field.manager');
  }

  /**
   * Get the entity type bundle info service.
   *
   * @return \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   *   The entity type bundle info service.
   */
  protected function entityTypeBundleInfo(): EntityTypeBundleInfoInterface {
    return \Drupal::service('entity_type.bundle.info');
  }

  /**
   * Get the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  protected function entityTypeManager(): EntityTypeManagerInterface {
    return \Drupal::service('entity_type.manager');
  }

}
