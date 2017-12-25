<?php

namespace Drupal\avatars;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

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
    /** @var \Drupal\field_ui\Form\FieldConfigEditForm $obj */
    $form_object = $form_state->getFormObject();
    /** @var \Drupal\field\FieldConfigInterface $field_config */
    $field_config = $form_object->getEntity();

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

    // Add the token tree UI.
    $form['avatars']['token_tree'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => [$target_entity_type_id],
      '#show_nested' => FALSE,
      '#global_types' => FALSE,
    ];

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
    /** @var \Drupal\field_ui\Form\FieldConfigEditForm $obj */
    $form_object = $form_state->getFormObject();
    /** @var \Drupal\field\FieldConfigInterface $field_config */
    $field_config = $form_object->getEntity();
    $hash['contents'] = $form_state->getValue(['avatars', 'hash']);
    $field_config->setThirdPartySetting('avatars', 'hash', $hash);
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

}
