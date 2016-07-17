<?php

/**
 * @file
 * Contains \Drupal\avatars\Plugin\AvatarGenerator\AvatarGeneratorBase.
 */

namespace Drupal\avatars\Plugin\AvatarGenerator;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * AvatarGenerator plugin base class.
 */
abstract class AvatarGeneratorBase extends PluginBase implements AvatarGeneratorPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configuration = array_merge($this->defaultConfiguration(), $this->configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Create a site-unique identifier for a user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   A user account.
   *
   * @return string
   *   A unique string based on the user.
   */
  protected function getIdentifier(AccountInterface $account) {
    // There is no ID for new users (registration page).
    if ($account->isNew()) {
      return '0';
    }
    else {
      return !empty($account->getEmail()) ? $account->getEmail() : (string) $account->id();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getFile(AccountInterface $account) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function generateUri(AccountInterface $account) {
    return NULL;
  }

}
