<?php

namespace Drupal\avatars\Form\Entity;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form to delete Avatar Kit services.
 */
class AvatarKitServiceDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  protected function getRedirectUrl() : Url {
    return Url::fromRoute('avatars.config.services');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) : void {
    parent::submitForm($form, $form_state);
    $form_state->setRedirectUrl($this->getRedirectUrl());
  }

}
