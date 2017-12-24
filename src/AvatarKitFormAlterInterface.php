<?php

namespace Drupal\avatars;

use Drupal\Core\Form\FormStateInterface;

/**
 * Interface for Drupal form alters.
 */
interface AvatarKitFormAlterInterface {

  /**
   * Implements hook_form_FORM_ID_alter().
   *
   * FORM_ID: 'field_config_edit_form'.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @see \avatars_form_field_config_edit_form_alter()
   */
  public function fieldConfigEditForm(array &$form, FormStateInterface $form_state): void;

  /**
   * Submission handler for field config edit form alter.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @see \Drupal\avatars\AvatarKitFormAlterInterface::fieldConfigEditForm
   */
  public function fieldConfigEditFormSubmit(array $form, FormStateInterface $form_state): void;

}
