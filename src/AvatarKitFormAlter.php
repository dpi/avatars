<?php

namespace Drupal\avatars;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field\FieldConfigInterface;

/**
 * Drupal form alters.
 */
class AvatarKitFormAlter implements AvatarKitFormAlterInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|null
   * @see \Drupal\avatars\AvatarKitFormAlter::entityFieldHandler
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function fieldConfigEditForm(array &$form, FormStateInterface $form_state): void {
    $field_config = $this->getFieldConfig($form_state);

    $avatar_field_types = ['file', 'image'];
    if (!in_array($field_config->getType(), $avatar_field_types)) {
      // Only add our form alters if this is a valid avatar field target.
      return;
    }

    $third_party = $field_config->getThirdPartySettings('avatars');

    $form['avatars'] = [
      '#type' => 'details',
      '#title' => $this->t('Avatars'),
      '#tree' => TRUE,
      '#open' => TRUE,
      '#weight' => 50,
    ];

    $target_entity_type_id = $field_config->getTargetEntityTypeId();
    $form['avatars']['hash'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Identifier string'),
      '#description' => $this->t('Generate avatar hashes for @entity_type_plural based on this string.', [
        '@entity_type_plural' => $this->entityTypeManager()->getDefinition($target_entity_type_id)->getPluralLabel(),
      ]),
      '#default_value' => $third_party['hash']['contents'] ?? NULL,
    ];

    if ($this->moduleHandler()->moduleExists('token')) {
      // Add the token tree UI.
      $form['avatars']['token_tree'] = [
        '#theme' => 'token_tree_link',
        '#token_types' => [$target_entity_type_id],
        '#show_nested' => FALSE,
        '#global_types' => FALSE,
      ];
    }

    // Our submission function needs to be before
    // \Drupal\field_ui\Form\FieldConfigEditForm::save.
    array_unshift(
      $form['actions']['submit']['#submit'],
      [$this, 'fieldConfigEditFormSubmit']
    );
  }

  /**
   * {@inheritdoc}
   */
  public function fieldConfigEditFormSubmit(array $form, FormStateInterface $form_state): void {
    $field_config = $this->getFieldConfig($form_state);
    $hash['contents'] = $form_state->getValue(['avatars', 'hash']);
    $field_config->setThirdPartySetting('avatars', 'hash', $hash);
  }

  /**
   * Get the field config entity.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\field\FieldConfigInterface
   *   The field config entity.
   */
  public function getFieldConfig(FormStateInterface $form_state): FieldConfigInterface {
    /** @var \Drupal\field_ui\Form\FieldConfigEditForm $form_object */
    $form_object = $form_state->getFormObject();
    return $form_object->getEntity();
  }

  /**
   * Get the entity type manager.
   *
   * @return \Drupal\Core\Entity\EntityTypeManagerInterface
   *   The entity type manager.
   */
  protected function entityTypeManager(): EntityTypeManagerInterface {
    if (!$this->entityTypeManager) {
      $this->entityTypeManager = \Drupal::entityTypeManager();
    }
    return $this->entityTypeManager;
  }

  /**
   * Get the module handler.
   *
   * @return \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler.
   */
  protected function moduleHandler(): ModuleHandlerInterface {
    return \Drupal::moduleHandler();
  }

}