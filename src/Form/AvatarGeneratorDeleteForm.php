<?php

/**
 * @file
 * Contains \Drupal\avatars\Form\AvatarGeneratorDeleteForm.
 */

namespace Drupal\avatars\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller to delete an avatar generator instance.
 */
class AvatarGeneratorDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete avatar generator %label?', [
      '%label' => $this->entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('avatars.config');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    drupal_set_message(t('Avatar generator %label was deleted.', array(
      '%label' => $this->entity->label(),
    )));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
